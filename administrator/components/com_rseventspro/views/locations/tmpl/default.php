<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction')); ?>

<script type="text/javascript">
<?php if ($this->total > count($this->items)) { ?>
	window.addEvent('domready', function(){ 
	$('rsepro_loadmore').addEvent('click', function(el) {
		var lstart = $('rseprocontainer').getElements('tr');
		rspagination('locations',lstart.length);
	});
});
<?php } ?>

function saveorder(n,task) 
{
	$('rscheckbox').click();
	Joomla.submitform(task);
}
</script>

<form method="post" action="<?php echo JRoute::_('index.php?option=com_rseventspro&view=locations'); ?>" name="adminForm" id="adminForm">
<div class="row-fluid">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
		<?php echo $this->filterbar->show(); ?>
		<table class="table table-striped adminlist" id="locationsList">
			<thead>
				<?php echo $this->filterbar->orderingHead($this->items,'locations'); ?>
				<th width="1%" align="center" class="small hidden-phone"><input type="checkbox" name="checkall-toggle" id="rscheckbox" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this);"/></th>
				<th><?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'name', $listDirn, $listOrder); ?></th>
				<th width="1%" style="min-width:55px" class="nowrap center"><?php echo JHtml::_('grid.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?></th>
				<th width="1%" class="nowrap hidden-phone"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?></th>
			</thead>
			<tbody id="rseprocontainer">
				<?php foreach ($this->items as $i => $item) { ?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="1">
						<?php echo $this->filterbar->orderingBody($item->ordering, 'ordering', $this->pagination, $i, $this->total, 'locations'); ?>
						<td class="center hidden-phone">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="nowrap has-context">
							<a href="<?php echo JRoute::_('index.php?option=com_rseventspro&task=location.edit&id='.$item->id); ?>" class="rsepro_name"><?php echo $item->name; ?></a>
							<?php if (!empty($item->address)) { ?>
							<br /> <?php echo $item->address; ?>
							<?php } ?>
						</td>
						<td class="center">
							<?php echo JHtml::_('jgrid.published', $item->published, $i, 'locations.'); ?>
						</td>
						<td class="center hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="5">
					<?php if ($this->total > count($this->items)) { ?>
					<button type="button" class="rsepromore_inactive" id="rsepro_loadmore"><?php echo JText::_('COM_RSEVENTSPRO_GLOBAL_MORE_RESULTS'); ?></button>
					<?php } ?>
				</td>
			</tr>
		</tfoot>
		</table>
	</div>
</div>
	
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="total" id="total" value="<?php echo $this->total; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
</form>