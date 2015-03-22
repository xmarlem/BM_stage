<?php
/**
 * Helper class: com_content.category
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

class helperBetterPreviewPreviewContentCategory extends helperBetterPreviewPreview
{

	function renderPreview(&$article, $context)
	{
		if ($context != 'com_content.category' || isset($article->introtext))
		{
			return;
		}
		parent::renderPreview($article, $context);
	}

	function states()
	{
		parent::initStates(
			'categories',
			array('parent' => 'parent_id'),
			'categories',
			array('parent' => 'parent_id')
		);
	}
}
