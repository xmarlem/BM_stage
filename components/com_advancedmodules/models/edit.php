<?php
/**
 * @package         Advanced Module Manager
 * @version         4.22.2
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

require_once JPATH_ADMINISTRATOR . '/components/com_advancedmodules/models/module.php';

class AdvancedModulesModelEdit extends AdvancedModulesModelModule
{
	public function __construct($config = array())
	{
		parent::__construct($config);
	}
}
