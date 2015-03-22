<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class rseventsproTableEvent extends JTable
{	
	/**
	 * @param	JDatabase	A database connector object
	 */
	public function __construct($db) {
		parent::__construct('#__rseventspro_events', 'id', $db);
	}
	
	/**
	 * Overloaded bind function
	 *
	 * @param	array		Named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @since	1.6
	 */
	public function bind($array, $ignore = '') {
		return parent::bind($array, $ignore);
	}
	
	
	/**
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    http://docs.joomla.org/JTable/check
	 * @since   11.1
	 */
	public function check() {
		$db = $this->getDbo();
		$config = JFactory::getConfig();
		$tzoffset = $config->get('offset');
		
		if ($this->URL == 'http://')
			$this->URL = '';
		
		// Manipulate dates
		if (empty($this->id)) {
			$user	= JFactory::getUser();
			$start	= JFactory::getDate();
			$end	= JFactory::getDate();
			$endunix = $end->toUnix() + 7200;
			$end	= JFactory::getDate($endunix);
			
			if (JFactory::getApplication()->isAdmin()) $this->published = 1;
			$this->name = empty($this->name) ? JText::_('COM_RSEVENTSPRO_NEW_EVENT') : $this->name;
			$this->start = (empty($this->start) || $this->start == $db->getNullDate()) ? $start->toSql() : $this->start;
			
			if (!isset($this->from)) {
				$this->end = (empty($this->end) || $this->end == $db->getNullDate()) ? $end->toSql() : $this->end;
			} else {
				unset($this->from);
			}
			
			$this->owner = $user->get('id');
			$this->options = rseventsproHelper::getDefaultOptions();
			
			if ($user->get('guest')) 
				$this->sid = JFactory::getSession()->getId();
			
			$created	= new RSDate();
			$created->convertTZByID('GMT');
			$this->created = $created->formatLikeDate('Y-m-d H:i:s');
		} else {
			if ($this->allday) {
				$this->start = $this->start.' 00:00:00';
				$start_date	= rseventsproHelper::date($this->start,null,false,true);
				$start_date->setTZByID($start_date->getTZID());
				$start_date->convertTZ(new RSDate_Timezone('GMT'));
				
				$new_start = new RSDate($start_date->formatLikeDate('Y-m-d H:i:s'));
				$new_start->setTZByID(rseventsproHelper::getTimezone());
				$new_start->convertTZ(new RSDate_Timezone('GMT'));
				
				$start = rseventsproHelper::date($new_start->formatLikeDate('Y-m-d H:i:s'),null,false,true);
				$start->setTZByID($start->getTZID());
				$start->convertTZ(new RSDate_Timezone('GMT'));
				
				$this->start = $start->formatLikeDate('Y-m-d H:i:s');
			} else {
				$start_date	= rseventsproHelper::date($this->start,null,false,true);
				$start_date->setTZByID($start_date->getTZID());
				$start_date->convertTZ(new RSDate_Timezone('GMT'));
				
				$new_start = new RSDate($start_date->formatLikeDate('Y-m-d H:i:s'));
				$new_start->setTZByID(rseventsproHelper::getTimezone());
				$new_start->convertTZ(new RSDate_Timezone('GMT'));
				
				$start = rseventsproHelper::date($new_start->formatLikeDate('Y-m-d H:i:s'),null,false,true);
				$start->setTZByID($start->getTZID());
				$start->convertTZ(new RSDate_Timezone('GMT'));
				
				$end_date = rseventsproHelper::date($this->end,null,false,true);
				$end_date->setTZByID($end_date->getTZID());
				$end_date->convertTZ(new RSDate_Timezone('GMT'));
				
				if ($start_date > $end_date)
					$end_date->addSeconds(7200);
				
				$new_end = new RSDate($end_date->formatLikeDate('Y-m-d H:i:s'));
				$new_end->setTZByID(rseventsproHelper::getTimezone());
				$new_end->convertTZ(new RSDate_Timezone('GMT'));
				
				
				$end = rseventsproHelper::date($new_end->formatLikeDate('Y-m-d H:i:s'),null,false,true);
				$end->setTZByID($end->getTZID());
				$end->convertTZ(new RSDate_Timezone('GMT'));
			}
			
			$this->start	= $this->allday ? $this->start : $start->formatLikeDate('Y-m-d H:i:s');
			$this->end		= $this->allday ? $db->getNullDate() : $end->formatLikeDate('Y-m-d H:i:s');
		}
		
		// Start registration
		if (!empty($this->start_registration) && $this->start_registration != $db->getNullDate()) {
			$start_registration  = JFactory::getDate($this->start_registration, $tzoffset);
			$this->start_registration = $start_registration->toSql();
		}
		
		// End registration
		if (!empty($this->end_registration) && $this->end_registration != $db->getNullDate()) {
			$end_registration  = JFactory::getDate($this->end_registration, $tzoffset);
			$this->end_registration = $end_registration->toSql();
		}
		
		// Unsubscribe date
		if (!empty($this->unsubscribe_date) && $this->unsubscribe_date != $db->getNullDate()) {
			$this->unsubscribe_date = JFactory::getDate($this->unsubscribe_date, $tzoffset)->toSql();
		}
		
		// Discounts
		if ($this->discounts) {
			if ($this->early_fee_end && $this->early_fee_end != $db->getNullDate()) {
				$this->early_fee_end = JFactory::getDate($this->early_fee_end, $tzoffset)->toSql();
			}

			if ($this->late_fee_start && $this->late_fee_start != $db->getNullDate()) {
				$this->late_fee_start = JFactory::getDate($this->late_fee_start, $tzoffset)->toSql();
			}
		}
		
		// Repeat dates
		if (isset($this->repeat_also) && is_array($this->repeat_also)) {
			$dates = array_unique($this->repeat_also);
			$dates = array_merge($dates,array());
			
			$startdate	= rseventsproHelper::date($this->start,null,false,true);
			$seconds	= ($startdate->formatLikeDate('H') * 60 + $startdate->formatLikeDate('i')) * 60 + $startdate->formatLikeDate('s');
			
			foreach ($dates as $i => $date) {
				$newdate = rseventsproHelper::date($date,null,false,true);
				$newdate->setTZByID($newdate->getTZID());
				$newdate->convertTZ(new RSDate_Timezone('GMT'));
				$newdate->addSeconds($seconds);
				$dates[$i] = $newdate->formatLikeDate('Y-m-d H:i:s');
			}
			
			$registry = new JRegistry();
			$registry->loadArray($dates);
			$this->repeat_also = (string) $registry;
		} else $this->repeat_also = '';
		
		if (isset($this->payments) && is_array($this->payments)) {
			$registry = new JRegistry();
			$registry->loadArray($this->payments);
			$this->payments = (string) $registry;
		} else $this->payments = '';
		
		if (isset($this->gallery_tags) && is_array($this->gallery_tags)) {
			$registry = new JRegistry;
			$registry->loadArray($this->gallery_tags);
			$this->gallery_tags = (string) $registry;
		} else {
			$this->gallery_tags = '';
		}
		
		if (isset($this->options) && is_array($this->options)) {
			$registry = new JRegistry();
			$registry->loadArray($this->options);
			$this->options = (string) $registry;
		} else $this->options = '';
		
		if (!empty($this->metakeywords)) {
			$this->metakeywords = rtrim($this->metakeywords,',');
		}

		return true;
	}
	
	
	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/publish
	 * @since   11.1
	 */
	public function publish($pks = null, $value = 1, $userid = 0) {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$task	= JFactory::getApplication()->input->getCmd('task');
		
		if (count($pks) == 1 && $task == 'unpublish') {
			$query->clear()
				->select($db->qn('published'))
				->from($db->qn('#__rseventspro_events'))
				->where($db->qn('id').' = '.(int) @$pks[0]);
			$db->setQuery($query);
			$state = (int) $db->loadResult();
			if ($state == 2) {
				JFactory::getApplication()->enqueueMessage(JText::_('COM_RSEVENTSPRO_ARCHIVE_INFO'));
			}
		}
		
		if ($task == 'archive') {
			$query->clear()
				->update($db->qn('#__rseventspro_events'))
				->set($db->qn('archived'). ' = '.$db->q(1))
				->where($db->qn('id'). ' IN ('.implode(',',$pks).')');
			
			$db->setQuery($query);
			$db->execute();
		} else {
			if ($value == 1) {
				$query->clear()
					->update($db->qn('#__rseventspro_events'))
					->set($db->qn('approved'). ' = '.$db->q(0))
					->where($db->qn('id'). ' IN ('.implode(',',$pks).')');
				
				$db->setQuery($query);
				$db->execute();
			}
		}
		
		return parent::publish($pks, $value, $userid);
	}
	
	/**
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     http://docs.joomla.org/JTable/delete
	 * @since   2.5
	 */
	public function delete($pk = null, $children = false) {
		return rseventsproHelper::remove($pk);
	}
	
	
	public function verify(&$array) {
		if (!isset($array['recurring']))
			$array['recurring'] = 0;
		
		if (!isset($array['allday']))
			$array['allday'] = 0;
		
		if (!isset($array['discounts']))
			$array['discounts'] = 0;
		
		if (!isset($array['ticketsconfig']))
			$array['ticketsconfig'] = 0;
		
		if (!isset($array['registration']))
			$array['registration'] = 0;
		
		if (!isset($array['comments']))
			$array['comments'] = 0;
		
		if (!isset($array['notify_me']))
			$array['notify_me'] = 0;
		
		if (!isset($array['notify_me_unsubscribe']))
			$array['notify_me_unsubscribe'] = 0;
		
		if (!isset($array['overbooking']))
			$array['overbooking'] = 0;
		
		if (!isset($array['max_tickets']))
			$array['max_tickets'] = 0;
		
		if (!isset($array['show_registered']))
			$array['show_registered']= 0;
		
		if (!isset($array['automatically_approve']))
			$array['automatically_approve'] = 0;
		
		if (isset($array['options'])) {
			$defaults = rseventsproHelper::getDefaultOptions();
			$registry = new JRegistry;
			$registry->loadString($defaults);
			$defaults = $registry->toArray();
			
			foreach ($defaults as $name => $value) {
				if (!isset($array['options'][$name]))
					$array['options'][$name] = 0;
			}
		}
		
		return true;
	}
}