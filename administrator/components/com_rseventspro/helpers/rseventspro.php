<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' ); 

class RseventsproHelper {
	
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($vName) {
		if (!version_compare(JVERSION, '3.0', '>=')) {
			JSubMenuHelper::addEntry(
				JText::_('COM_RSEVENTSPRO_GO_TO_DASHBOARD'),
				'index.php?option=com_rseventspro',
				$vName == ''
			);
			
			JSubMenuHelper::addEntry(
				JText::_('COM_RSEVENTSPRO_DASHBOARD_CATEGORIES'),
				'index.php?option=com_categories&extension=com_rseventspro',
				$vName == 'categories'
			);
		} else {
			JHtmlSidebar::addEntry(
				JText::_('COM_RSEVENTSPRO_GO_TO_DASHBOARD'), 
				'index.php?option=com_rseventspro', 
				$vName == ''
			);
			
			JHtmlSidebar::addEntry(
				JText::_('COM_RSEVENTSPRO_DASHBOARD_CATEGORIES'), 
				'index.php?option=com_categories&extension=com_rseventspro', 
				$vName == 'categories'
			);
		}
	}
}