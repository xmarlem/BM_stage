<?php
/**
 * @author		Andrei Chernyshev
 * @copyright	
 * @license		GNU General Public License version 2 or later
 */

defined("_JEXEC") or die("Restricted access");

/**
 * Zefaniadictionaryitem item controller class.
 *
 * @package     Zefaniabible
 * @subpackage  Controllers
 */
class ZefaniabibleControllerZefaniadictionaryitem extends JControllerForm
{
	/**
	 * The URL view item variable.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $view_item = 'Zefaniadictionaryitem';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $view_list = 'Zefaniadictionary';
	
	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   2.5
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Zefaniadictionaryitem', 'ZefaniabibleModel', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_zefaniabible&view=zefaniadictionary' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
}
?>