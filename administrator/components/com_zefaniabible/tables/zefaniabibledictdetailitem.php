<?php
/**
 * @author		Andrei Chernyshev
 * @copyright	
 * @license		GNU General Public License version 2 or later
 */

defined("_JEXEC") or die("Restricted access");

/**
 * ZefaniabibleDictDetailItem table class.
 *
 * @package     Zefaniabible
 * @subpackage  Tables
 */
class ZefaniabibleTableZefaniabibleDictDetailItem extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__zefaniabible_dictionary_detail', 'id', $db);
	}

	/**
     * Overloaded check function
     */
    public function check()
	{
		
		return parent::store($updateNulls);
	}
}
?>