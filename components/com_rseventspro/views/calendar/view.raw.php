<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class rseventsproViewCalendar extends JViewLegacy
{
	public function display($tpl = null) {
		$app			= JFactory::getApplication();
		$layout 		= $this->getLayout();
		$jinput			= $app->input;
		$this->config	= rseventsproHelper::getConfig();
		$params			= rseventsproHelper::getParams();
		
		if ($jinput->getCmd('type','') == 'ical') {
			if ($params->get('ical',1) == 0) {
				$app->redirect(rseventsproHelper::route('index.php?option=com_rseventspro&view=calendar',false));
			}
			
			// Get events
			$rows = $this->get('Events');
			
			// If the option to not show full events is enabled , then remove them from our events list
			if (!$params->get('full',1)) {
				foreach ($rows as $i => $event) {
					if (rseventsproHelper::eventisfull($event->id)) {
						unset($rows[$i]);
					}
				}
			}
			
			if ($rows) {
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
			if ($layout == 'module') {
				require_once JPATH_SITE.'/components/com_rseventspro/helpers/calendar.php';
				require_once JPATH_SITE.'/modules/mod_rseventspro_calendar/helper.php';
				
				$month	= $jinput->getInt('month');
				$year	= $jinput->getInt('year');
				$module	= $jinput->getInt('mid');
				$params = $this->get('ModuleParams');
				
				// Get events
				$events = modRseventsProCalendar::getEvents($params);
				
				if (!$params->get('full',1)) {
					foreach ($events as $i => $event)
						if (rseventsproHelper::eventisfull($event->id)) unset($events[$i]);
				}
				
				$calendar = new RSEPROCalendar($events,$params,true);
				$calendar->class_suffix = $params->get('moduleclass_sfx','');
				$calendar->setDate($month, $year);
				
				$itemid = $params->get('itemid');
				$itemid = !empty($itemid) ? $itemid : RseventsproHelperRoute::getCalendarItemid();
				
				$this->calendar	= $calendar;
				$this->itemid	= $itemid;
				$this->module	= $module;
			} else {
				$jinput->set('limitstart', $jinput->getInt('limitstart'));
				
				$this->user			= JFactory::getUser()->get('id');
				$this->admin		= rseventsproHelper::admin();
				$this->params		= rseventsproHelper::getParams();
				$this->permissions	= rseventsproHelper::permissions();
				
				// Get events
				$events = $this->get('Events');
				
				if (!$this->params->get('full',1)) {
					foreach ($events as $i => $event) {
						if (rseventsproHelper::eventisfull($event->id)) {
							unset($events[$i]);
						}
					}
				}
				
				$this->events = $events;
			}
		}
		
		parent::display($tpl);
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