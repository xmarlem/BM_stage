<?php
/**
 * @version		$Id: media.php 2772 2013-04-09 17:03:35Z lefteris.kavadas $
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

class SigProControllerMedia extends SigProController
{

	public function connector()
	{
		$mainframe = JFactory::getApplication();
		$path = SigProHelper::getPath('site');
		$url = SigProHelper::getHTTPPath($path);
		JPath::check($path);
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/elfinder/php/elFinderConnector.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/elfinder/php/elFinder.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/elfinder/php/elFinderVolumeDriver.class.php';
		include_once JPATH_COMPONENT_ADMINISTRATOR.'/js/elfinder/php/elFinderVolumeLocalFileSystem.class.php';
		function access($attr, $path, $data, $volume)
		{
			$mainframe = JFactory::getApplication();
			// Hide files and folders starting with .
			if (strpos(basename($path), '.') === 0 && $attr == 'hidden')
			{
				return true;
			}
			// Read only access for front-end. Full access for administration section.
			switch($attr)
			{
				case 'read' :
					return true;
					break;
				case 'write' :
					return ($mainframe->isSite()) ? false : true;
					break;
				case 'locked' :
					return ($mainframe->isSite()) ? true : false;
					break;
				case 'hidden' :
					return false;
					break;
			}

		}

		if ($mainframe->isAdmin())
		{
			$permissions = array('read' => true, 'write' => true);
		}
		else
		{
			$permissions = array('read' => true, 'write' => false);
		}

		$options = array('roots' => array( array('driver' => 'LocalFileSystem', 'path' => $path, 'URL' => $url, 'accessControl' => 'access', 'defaults' => $permissions)));
		$connector = new elFinderConnector(new elFinder($options));
		$connector->run();
	}

}
