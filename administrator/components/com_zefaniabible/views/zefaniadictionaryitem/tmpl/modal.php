<?php
/**
 * @author		Andrei Chernyshev
 * @copyright	
 * @license		GNU General Public License version 2 or later
 */

defined("_JEXEC") or die("Restricted access");

// Include the HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;
$assoc = JLanguageAssociations::isEnabled();
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'zefaniadictionaryitem.cancel' || document.formvalidator.isValid(document.id('zefaniadictionaryitem-form')))
		{
			if (window.opener && (task == 'zefaniadictionaryitem.save' || task == 'zefaniadictionaryitem.cancel'))
			{
				window.opener.document.closeEditWindow = self;
				window.opener.setTimeout('window.document.closeEditWindow.close()', 1000);
			}

			Joomla.submitform(task, document.getElementById('zefaniadictionaryitem-form'));
		}
	}
</script>
<div class="container-popup">

<div class="pull-right">
	<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('zefaniadictionaryitem.apply');"><?php echo JText::_('JTOOLBAR_APPLY') ?></button>
	<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('zefaniadictionaryitem.save');"><?php echo JText::_('JTOOLBAR_SAVE') ?></button>
	<button class="btn" type="button" onclick="Joomla.submitbutton('zefaniadictionaryitem.cancel');"><?php echo JText::_('JCANCEL') ?></button>
</div>

<div class="clearfix"> </div>
<hr class="hr-condensed" />

<form action="<?php echo JRoute::_('index.php?option=com_zefaniabible&layout=modal&tmpl=component&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="zefaniadictionaryitem-form" class="form-validate form-horizontal">
	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', empty($this->item->id) ? JText::_('COM_ZEFANIABIBLE_NEW_NEWSFEED', true) : JText::_('COM_ZEFANIABIBLE_EDIT_NEWSFEED', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<div class="form-vertical">
					<?php echo $this->form->getControlGroup('name'); ?>
				</div>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'attrib-jbasic', JText::_('JGLOBAL_FIELDSET_DISPLAY_OPTIONS', true)); ?>
			<?php $this->fieldset = 'jbasic'; ?>
			<?php echo JLayoutHelper::render('joomla.edit.fieldset', $this); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php $this->set('ignore_fieldsets', array('jbasic')); ?>
		<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
