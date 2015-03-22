<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' ); 
$event = $this->details['event']; 
$categories = (isset($this->details['categories']) && !empty($this->details['categories'])) ? JText::_('COM_RSEVENTSPRO_GLOBAL_CATEGORIES').': '.$this->details['categories'] : '';
$tags = (isset($this->details['tags']) && !empty($this->details['tags'])) ? JText::_('COM_RSEVENTSPRO_GLOBAL_TAGS').': '.$this->details['tags'] : ''; ?>

<div class="rsepro_plugin_container">
	
	<?php if (!empty($event->options['show_icon_list'])) { ?>
	<div class="rsepro_plugin_image">
		<a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($event->id,$event->name).$this->itemid); ?>" class="rs_event_link">
			<?php if (!empty($event->icon)) { ?>
				<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/events/thumbs/s_<?php echo $event->icon.'?nocache='.uniqid(''); ?>" alt="" width="<?php echo $this->config->icon_small_width; ?>" />
			<?php } else { ?>
				<img src="<?php echo JURI::root(); ?>components/com_rseventspro/assets/images/blank.png" alt="" width="70" />
			<?php }  ?>
		</a>
	</div>
	<?php } ?>
	
	<div class="rsepro_plugin_content">
		<span>
			<a href="<?php echo rseventsproHelper::route('index.php?option=com_rseventspro&layout=show&id='.rseventsproHelper::sef($event->id,$event->name).$this->itemid); ?>" class="rsepro_plugin_link">
				<?php echo $event->name; ?>
			</a>
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
</div>