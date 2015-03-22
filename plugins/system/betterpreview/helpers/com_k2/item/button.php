<?php
/**
 * Button Helper class: com_k2.item
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

class helperBetterPreviewButtonK2Item extends helperBetterPreviewButton
{
	function getExtraJavaScript($text)
	{
		return '
				isjform = 0;
				text = text.split(\'<hr id="system-readmore" />\');
				introtext = text[0];
				fulltext =  text[1] == undefined ? "" : text[1];
				text = (introtext + " " + fulltext).trim();
				overrides = {
						text: text,
						introtext: introtext,
						fulltext: fulltext,
					};
			';
	}

	function getURL($name)
	{
		$helper = new helperBetterPreviewHelperK2Item($this->params);

		if (!$item = $helper->getK2Item())
		{
			return;
		}

		return $item->url;
	}
}
