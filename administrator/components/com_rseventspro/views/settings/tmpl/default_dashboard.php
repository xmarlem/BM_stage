<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

$fieldsets = array('modules'); 
foreach ($fieldsets as $fieldset) {
	echo JHtml::_('rsfieldset.start', 'adminform', JText::_($this->fieldsets[$fieldset]->label));
	foreach ($this->form->getFieldset($fieldset) as $field) {
		echo JHtml::_('rsfieldset.element', $field->label, $field->input);
	}
	echo JHtml::_('rsfieldset.end');
}