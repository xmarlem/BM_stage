<?php
/**
 * Link Helper class: com_k2.category
 *
 * @package         Better Preview
 * @version         3.3.2
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

include_once __DIR__ . '/helper.php';

class helperBetterPreviewLinkK2Category extends helperBetterPreviewLink
{
	function getLinks()
	{
		$helper = new helperBetterPreviewHelperK2Category($this->params);

		if (!$item = $helper->getK2Category())
		{
			return;
		}

		$parents = $helper->getK2CategoryParents($item);

		return array_merge(array($item), $parents);
	}
}
