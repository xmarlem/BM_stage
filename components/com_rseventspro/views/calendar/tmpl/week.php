<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );
$count = count($this->events); ?>

<h1><?php echo JText::sprintf('COM_RSEVENTSPRO_EVENTS_FROM_TO',$this->from,$this->to); ?></h1>

<?php $rss = $this->params->get('rss',1); ?>
<?php $ical = $this->params->get('ical',1); ?>
<?php if ($rss || $ical) { ?>
<div class="rs_rss">
	<?php if ($rss) { ?>
	<a href="<?php echo $this->rss; ?>" class="<?php echo rseventsproHelper::tooltipClass(); ?>" title="<?php echo rseventsproHelper::tooltipText(JText::_('COM_RSEVENTSPRO_RSS')); ?>">
		<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/rss.png" />
	</a>
	<?php } ?>
	<?php if ($ical) { ?>
	<a href="<?php echo $this->ical; ?>" class="<?php echo rseventsproHelper::tooltipClass(); ?>" title="<?php echo rseventsproHelper::tooltipText(JText::_('COM_RSEVENTSPRO_ICS')); ?>">
		<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/calendar.png" />
	</a>
	<?php } ?>
</div>
<?php } ?>

<?php if (!empty($this->events)) { ?>
<ul class="rs_events_container" id="rs_events_container">
	<?php foreach($this->events as $eventid) { ?>
	<?php $details = rseventsproHelper::details($eventid->id); ?>
	<?php if (isset($details['event']) && !empty($details['event'])) $event = $details['event']; else continue; ?>
	<?php $full = rseventsproHelper::eventisfull($event->id); ?>
	<?php $ongoing = rseventsproHelper::ongoing($event->id); ?>
	<?php $categories = (isset($details['categories']) && !empty($details['categories'])) ? JText::_('COM_RSEVENTSPRO_GLOBAL_CATEGORIES').': '.$details['categories'] : '';  ?>
	<?php $tags = (isset($details['tags']) && !empty($details['tags'])) ? JText::_('COM_RSEVENTSPRO_GLOBAL_TAGS').': '.$details['tags'] : '';  ?>
	<?php $incomplete = !$event->completed ? ' rs_incomplete' : ''; ?>
	<?php $featured = $event->featured ? ' rs_featured' : ''; ?>

	<li class="rs_event_detail<?php echo $incomplete.$featured; ?>" id="rs_event<?php echo $event->id; ?>" itemscope itemtype="http://schema.org/Event">
		
		<div class="rs_options" style="display:none;">
			<?php if ((!empty($this->permissions['can_edit_events']) || $event->owner == $this->user || $event->sid == $this->user || $this->admin) && !empty($this->user)) { ?>
				<a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=edit&id='.rseventsproHelper::sef($event->id,$event->name)); ?>">
					<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/edit.png" alt="<?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_EDIT'); ?>" />
				</a>
			<?php } ?>
			<?php if ((!empty($this->permissions['can_delete_events']) || $event->owner == $this->user || $event->sid == $this->user || $this->admin) && !empty($this->user)) { ?>
				<a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&task=rseventspro.remove&id='.rseventsproHelper::sef($event->id,$event->name)); ?>" onclick="return confirm('<?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_DELETE_CONFIRMATION'); ?>');">
					<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/delete.png" alt="<?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_DELETE'); ?>" />
				</a>
			<?php } ?>
		</div>
		
		<?php if (!empty($event->options['show_icon_list'])) { ?>
		<div class="rs_event_image" itemprop="image">
			<a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($event->id,$event->name)); ?>" class="rs_event_link">
				<?php if (!empty($event->icon)) { ?>
					<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/events/thumbs/s_<?php echo $event->icon.'?nocache='.uniqid(''); ?>" alt="" width="<?php echo $this->config->icon_small_width; ?>" />
				<?php } else { ?>
					<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/blank.png" alt="" width="70" />
				<?php }  ?>
			</a>
		</div>
		<?php } ?>
	
		<div class="rs_event_details">
			<span itemprop="name">
				<a itemprop="url" href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($event->id,$event->name)); ?>" class="rs_event_link<?php echo $full ? ' rs_event_full' : ''; ?><?php echo $ongoing ? ' rs_event_ongoing' : ''; ?>"><?php echo $event->name; ?></a> <?php if (!$event->completed) echo JText::_('COM_RSEVENTSPRO_GLOBAL_INCOMPLETE_EVENT'); ?> <?php if (!$event->published) echo JText::_('COM_RSEVENTSPRO_GLOBAL_UNPUBLISHED_EVENT'); ?>
			</span>
			<span>
				<?php if ($event->allday) { ?>
				<?php if (!empty($event->options['start_date_list'])) { ?>
				<?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_ON'); ?> <b><?php echo rseventsproHelper::date($event->start,$this->config->global_date,true); ?></b>
				<?php } ?>
				<?php } else { ?>
				<?php if (!empty($event->options['start_date_list']) && !empty($event->options['end_date_list'])) { ?>
				<?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_FROM'); ?> <b><?php echo rseventsproHelper::date($event->start,rseventsproHelper::showMask('list_start',$event->options),true); ?></b> <?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_TO_LOWERCASE'); ?> <b><?php echo rseventsproHelper::date($event->end,rseventsproHelper::showMask('list_end',$event->options),true); ?></b>
				<?php } else if (!empty($event->options['start_date_list'])) { ?>
				<?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_FROM'); ?> <b><?php echo rseventsproHelper::date($event->start,rseventsproHelper::showMask('list_start',$event->options),true); ?></b>
				<?php } else if (!empty($event->options['end_date_list'])) { ?>
				<?php echo JText::_('COM_RSEVENTSPRO_EVENT_ENDING_ON'); ?> <b><?php echo rseventsproHelper::date($event->end,rseventsproHelper::showMask('list_end',$event->options),true); ?></b>
				<?php } ?>
				<?php } ?>
			</span>
			<?php if (!empty($event->options['show_location_list']) || !empty($event->options['show_categories_list']) || !empty($event->options['show_tags_list'])) { ?>
			<span>
				<?php if ($event->locationid && $event->lpublished && !empty($event->options['show_location_list'])) { echo JText::_('COM_RSEVENTSPRO_GLOBAL_AT'); ?> <a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=location&id='.rseventsproHelper::sef($event->locationid,$event->location)); ?>"><?php echo $event->location; ?></a> <?php } ?>
				<?php echo (!empty($event->options['show_categories_list']) ? $categories : '').' '.(!empty($event->options['show_tags_list']) ? $tags : ''); ?>
			</span>
			<?php } ?>
		</div>
		
		<div style="display:none"><span itemprop="startDate"><?php echo rseventsproHelper::date($event->start,'Y-m-d H:i:s'); ?></span></div>
		<div style="display:none"><span itemprop="endDate"><?php echo rseventsproHelper::date($event->end,'Y-m-d H:i:s'); ?></span></div>
	</li>
	<?php } ?>
</ul>

<div class="rs_loader" id="rs_loader" style="display:none;">
	<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/loader.gif" alt="" />
</div>

<?php if ($this->total > $count) { ?>
<a class="rs_read_more" id="rsepro_loadmore"><?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_LOAD_MORE'); ?></a>
<?php } ?>

<span id="total" class="rs_hidden"><?php echo $this->total; ?></span>
<span id="Itemid" class="rs_hidden"><?php echo JFactory::getApplication()->input->getInt('Itemid'); ?></span>
<span id="date" class="rs_hidden"><?php echo JFactory::getApplication()->input->getString('date'); ?></span>
<?php } else echo JText::_('COM_RSEVENTSPRO_GLOBAL_NO_EVENTS'); ?>

<script type="text/javascript">
	window.addEvent('domready', function(){
		<?php if ($this->total > $count) { ?>
		$('rsepro_loadmore').addEvent('click', function(el) {
			var lstart = $$('#rs_events_container > li');
			rspagination('week',lstart.length);
		});
		<?php } ?>
		
		<?php if (!empty($count)) { ?>
		$$('#rs_events_container li').addEvents({
			mouseenter: function(){ 
				if (isset($(this).getElement('div.rs_options')))
					$(this).getElement('div.rs_options').style.display = '';
			},
			mouseleave: function(){      
				if (isset($(this).getElement('div.rs_options')))
					$(this).getElement('div.rs_options').style.display = 'none';
			}
		});
		<?php } ?>
	});
</script>