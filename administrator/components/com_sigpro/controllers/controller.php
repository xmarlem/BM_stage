<?php
/**
 * @version		$Id: controller.php 2725 2013-04-06 17:05:49Z joomlaworks $
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.controller');

if (version_compare(JVERSION, '3.0', 'ge'))
{
	class SigProController extends JControllerLegacy
	{
		public function display($cachable = false, $urlparams = array())
		{
			parent::display($cachable, $urlparams);
		}

		public function setRedirect($url, $msg = null, $type = null)
		{
			$galleryType = JRequest::getCmd('type', 'site');
			$tmpl = JRequest::getCmd('tmpl', 'index');
			$url .= '&type='.$galleryType.'&tmpl='.$tmpl;
			$editorName = JRequest::getCmd('editorName');
			if ($editorName)
			{
				$url .= '&editorName='.$editorName;
			}
			parent::setRedirect($url, $msg, $type);
		}

	}

}
elseif (version_compare(JVERSION, '2.5', 'ge'))
{
	class SigProController extends JController
	{
		public function display($cachable = false, $urlparams = false)
		{
			parent::display($cachable, $urlparams);
		}

		public function setRedirect($url, $msg = null, $type = null)
		{
			$galleryType = JRequest::getCmd('type', 'site');
			$tmpl = JRequest::getCmd('tmpl', 'index');
			$url .= '&type='.$galleryType.'&tmpl='.$tmpl;
			$editorName = JRequest::getCmd('editorName');
			if ($editorName)
			{
				$url .= '&editorName='.$editorName;
			}
			parent::setRedirect($url, $msg, $type);
		}

	}

}
else
{
	class SigProController extends JController
	{
		public function display($cachable = false)
		{
			parent::display($cachable);
		}

		public function setRedirect($url, $msg = null, $type = null)
		{
			$galleryType = JRequest::getCmd('type', 'site');
			$tmpl = JRequest::getCmd('tmpl', 'index');
			$url .= '&type='.$galleryType.'&tmpl='.$tmpl;
			$editorName = JRequest::getCmd('editorName');
			if ($editorName)
			{
				$url .= '&editorName='.$editorName;
			}
			parent::setRedirect($url, $msg, $type);
		}

	}

}
