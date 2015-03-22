<?php
/**
 * @version		$Id: jw_sigpro.php 2725 2013-04-06 17:05:49Z joomlaworks $
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.plugin.plugin');

class plgButtonJw_SigPro extends JPlugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	function onDisplay($name)
	{
		$mainframe = JFactory::getApplication();
		if ($mainframe->isSite())
		{
			return false;
		}
		$document = JFactory::getDocument();
		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js');
		}
		else
		{
			JHtml::_('jquery.framework');
		}
		$document->addScript(JURI::root(true).'/administrator/components/com_sigpro/js/jquery.noconflict.js');
		$document->addScript(JURI::root(true).'/administrator/components/com_sigpro/js/fancybox/jquery.fancybox.pack.js');
		$document->addStyleSheet(JURI::root(true).'/administrator/components/com_sigpro/js/fancybox/jquery.fancybox.css');
		$document->addStyleDeclaration("
			.sigProEditorButton { background:url(../administrator/components/com_sigpro/images/xtd-sig-icon.png) 100% 0 no-repeat }
			.icon-sigProEditorButton{background:url(../administrator/components/com_sigpro/images/sigpro-icon.png) 0 0 no-repeat;height:16px!important;width:16px!important;line-height:16px!important;position:relative;top:3px;}
		");
		$document->addScriptDeclaration("
			function SigProModal() {
				\$sig.fancybox({
					type: 'iframe',
					href: 'index.php?option=com_sigpro&tmpl=component&type=site&editorName=".$name."',
					padding: 0, 
					margin: 40, 
					title: null, 
					width: 1225, 
					height: 800,
					helpers: {
						css: { 'html':'overflow:hidden' },
						locked: true
					}
				});
			}");
		$button = new JObject();
		$button->set('link', 'index.php?option=com_sigpro&amp;tmpl=component&amp;type=site&amp;editorName='.$name);
		$button->set('text', JText::_('PLG_EDITORS-XTD_JW_SIGPRO_IMAGE_GALLERIES'));
		$button->set('name', 'sigProEditorButton');
		$button->set('onclick', 'SigProModal(); return false;');
		return $button;
	}

}
