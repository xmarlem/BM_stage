<?php
/**
 * Helper class: com_content.article
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

class helperBetterPreviewPreviewContentArticle extends helperBetterPreviewPreview
{

	function renderPreview(&$article, $context)
	{
		if ($context != 'com_content.article' || !isset($article->id) || $article->id != JFactory::getApplication()->input->get('id'))
		{
			return;
		}

		parent::renderPreview($article, $context);
	}

	function states()
	{
		parent::initStates(
			'content',
			array(
				'published' => 'state',
				'publish_up' => 'publish_up',
				'publish_down' => 'publish_down',
				'parent' => 'catid',
				'hits' => 'hits'
			),
			'categories',
			array(
				'parent' => 'parent_id'
			)
		);
	}
}
