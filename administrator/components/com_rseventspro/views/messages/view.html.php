<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class rseventsproViewMessages extends JViewLegacy
{
	protected $form;
	protected $type;
	protected $types;
	
	public function display($tpl = null) {
		$this->form			= $this->get('Form');
		$this->type			= $this->get('Type');
		$this->types		= $this->get('Types');
		
		if (!in_array($this->type,$this->types)) {
			echo rseventsproHelper::modalClose();
			JFactory::getApplication()->close();
		}
		
		parent::display($tpl);
	}
}