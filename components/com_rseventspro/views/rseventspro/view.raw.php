<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class rseventsproViewRseventspro extends JViewLegacy
{
	public function display($tpl = null) {
		$db				= JFactory::getDbo();
		$query			= $db->getQuery(true);
		$app			= JFactory::getApplication();
		$layout			= $this->getLayout();
		$jinput			= $app->input;
		$params			= rseventsproHelper::getParams();
		$this->user		= $this->get('User');
		$this->admin	= rseventsproHelper::admin();
		$this->config	= rseventsproHelper::getConfig();
		
		if ($jinput->getCmd('type','') == 'ical') {
			if ($params->get('ical',1) == 0) {
				$app->redirect(rseventsproHelper::route('index.php?option=com_rseventspro&layout=default',false));
			}
			
			if ($rows = $this->get('events')) {
				require_once JPATH_SITE.'/components/com_rseventspro/helpers/ical/iCalcreator.class.php';
				
				$config = array("unique_id" => JURI::root(), 'filename' => 'Events.ics');
				$v = new vcalendar( $config );
				$v->setProperty( "method", "PUBLISH" );
				
				foreach ($rows as $row) {
					if (!rseventsproHelper::canview($row->id)) {
						continue;
					}
					
					$event = $this->getEvent($row->id);
					
					$description = strip_tags($event->description);
					$description = str_replace("\n",'',$description);
					
					$start	= rseventsproHelper::date($event->start,null,false,true);
					$end	= rseventsproHelper::date($event->end,null,false,true);
					
					$start->setTZByID($start->getTZID());
					$start->convertTZ(new RSDate_Timezone('GMT'));
					$end->setTZByID($end->getTZID());
					$end->convertTZ(new RSDate_Timezone('GMT'));
					
					$vevent = & $v->newComponent( "vevent" );
					$vevent->setProperty( "dtstart", array($start->getYear(), $start->getMonth(), $start->getDay(), $start->getHour(), $start->getMinute(), $start->getSecond(), 'tz' => 'Z'));
					if (!$event->allday) $vevent->setProperty( "dtend", array($end->getYear(), $end->getMonth(), $end->getDay(), $end->getHour(), $end->getMinute(), $end->getSecond(), 'tz' => 'Z'));
					$vevent->setProperty( "LOCATION", $event->locationname. ' (' .$event->address . ')' );
					$vevent->setProperty( "summary", $event->name ); 
					$vevent->setProperty( "description", $description);
				}
				$v->returnCalendar();
				$app->close();
			}
		} else {
			if ($layout == 'ticket') {
				if ($jinput->get('from') == 'subscriber') {
					$query->clear()
						->select($db->qn('e.owner'))
						->from($db->qn('#__rseventspro_events','e'))
						->join('left', $db->qn('#__rseventspro_users','u').' ON '.$db->qn('u.ide').' = '.$db->qn('e.id'))
						->where($db->qn('u.id').' = '.$jinput->getInt('id'));
					
					$db->setQuery($query);
					$userid = (int) $db->loadResult();
				} else {
					$query->clear()
						->select($db->qn('idu'))
						->from($db->qn('#__rseventspro_users'))
						->where($db->qn('id').' = '.$jinput->getInt('id'));
					
					$db->setQuery($query);
					$userid = (int) $db->loadResult();
				}
				
				if ($this->admin || $userid == $this->user) {
					if (file_exists(JPATH_SITE.'/components/com_rseventspro/helpers/pdf.php')) {
						require_once JPATH_SITE.'/components/com_rseventspro/helpers/pdf.php';
						JFactory::getDocument()->setMimeEncoding('application/pdf');
						$pdf = RSEventsProPDF::getInstance();
						
						if ($id = $jinput->getInt('id'))
							$this->buffer 		= $pdf->ticket($id);
					} else {
						JFactory::getApplication()->close();
					}
				} else {
					JFactory::getApplication()->close();
				}
			} else {
				$jinput->set('limitstart',$jinput->getInt('limitstart',0));
				$tmpl = $jinput->get('tpl');
				
				if ($tmpl == 'events') {
					$this->events = $this->get('Events');
				} elseif ($tmpl == 'search') {
					$this->events = $this->get('Results');
				} elseif ($tmpl == 'locations') {
					$this->locations = $this->get('Locations');
				} elseif ($tmpl == 'categories') {
					$this->categories = $this->get('Categories');
				} elseif ($tmpl == 'subscribers') {
					$this->event = $this->get('Event');
					
					if ($this->admin || $this->event->owner == $this->user) {
						$this->subscribers  = $this->get('subscribers');
					} else {
						JFactory::getApplication()->close();
					}
				}
				
				$this->tmpl			= $tmpl;
				$this->params		= rseventsproHelper::getParams();
				$this->permissions	= rseventsproHelper::permissions();
			}
		}
		
		parent::display($tpl);
	}
	
	public function getStatus($state) {
		if ($state == 0) {
			return '<font color="blue">'.JText::_('COM_RSEVENTSPRO_GLOBAL_STATUS_INCOMPLETE').'</font>';
		} elseif ($state == 1) {
			return '<font color="green">'.JText::_('COM_RSEVENTSPRO_GLOBAL_STATUS_COMPLETED').'</font>';
		} elseif ($state == 2) {
			return '<font color="red">'.JText::_('COM_RSEVENTSPRO_GLOBAL_STATUS_DENIED').'</font>';
		}
	}
	
	public function getUser($id) {
		if ($id > 0) {
			return JFactory::getUser($id)->get('username');
		} else return JText::_('COM_RSEVENTSPRO_GLOBAL_GUEST');
	}
	
	public function getNumberEvents($id, $type) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$events	= 0;
		
		if ($type == 'categories') {
			$query->clear()
				->select($db->qn('e.id'))
				->from($db->qn('#__rseventspro_events','e'))
				->join('left', $db->qn('#__rseventspro_taxonomy','t').' ON '.$db->qn('e.id').' = '.$db->qn('t.ide'))
				->where($db->qn('t.type').' = '.$db->q('category'))
				->where($db->qn('t.id').' = '.(int) $id);
			
			
			$db->setQuery($query);
			$eventids = $db->loadColumn();
			
			if (!empty($eventids)) {
				foreach ($eventids as $eid) {
					if (!rseventsproHelper::canview($eid)) 
						continue;
					$events++;
				}
			}
		} else if ($type == 'locations') {
			$query->clear()
				->select($db->qn('id'))
				->from($db->qn('#__rseventspro_events'))
				->where($db->qn('location').' = '.(int) $id);
			
			
			$db->setQuery($query);
			$eventids = $db->loadColumn();
			
			if (!empty($eventids)) {
				foreach ($eventids as $eid) {
					if (!rseventsproHelper::canview($eid)) 
						continue;
					$events++;
				}
			}
		}
		
		if (!$events) return;
		return $events.' '.JText::plural('COM_RSEVENTSPRO_CALENDAR_EVENTS',$events);
	}
	
	protected function getEvent($id) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->select($db->qn('e.name'))->select($db->qn('e.start'))->select($db->qn('e.end'))->select($db->qn('e.description'))
			->select($db->qn('l.name','locationname'))->select($db->qn('l.address'))->select($db->qn('e.allday'))
			->from($db->qn('#__rseventspro_events','e'))
			->join('left', $db->qn('#__rseventspro_locations','l').' ON '.$db->qn('l.id').' = '.$db->qn('e.location'))
			->where($db->qn('e.id').' = '.(int) $id);
		
		$db->setQuery($query);
		return $db->loadObject();
	}
}