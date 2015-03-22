<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license     GNU General Public License version 2 or later; see LICENSE
*/

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Implements a combo box field.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldRSCalendar extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'RSCalendar';
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput() {		
		require_once JPATH_SITE.'/components/com_rseventspro/helpers/rseventspro.php';
		require_once JPATH_SITE.'/components/com_rseventspro/helpers/html.php';

		$this->format    = (string) $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';
		
		if (!rseventsproHelper::isJ3()) {
			JFactory::getDocument()->addStyleDeclaration(".rs_calendar_icon > img, .rs_calendar_icon_clear > img { margin: 0; }");
		}
		
		// Handle the special case for "now".
		if (strtoupper($this->value) == 'NOW') {
			$this->value = strftime($this->format);
		}

		return JHtml::_('rseventspro.calendar', $this->value, $this->name, $this->id, $this->format, false, false, true, 0); 
	}
}