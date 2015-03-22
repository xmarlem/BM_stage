<?php
/**
 * @version		$Id$
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

class SigProControllerSettings extends SigProController
{

	public function apply()
	{
		$response = $this->saveSettings();
		$this->setRedirect('index.php?option=com_sigpro&view=settings', $response->message, $response->type);
	}

	public function save()
	{
		$response = $this->saveSettings();
		$this->setRedirect('index.php?option=com_sigpro', $response->message, $response->type);
	}

	protected function saveSettings()
	{
		if (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
			$params = JRequest::getVar('jform', array(), 'post', 'array');
			$id = JRequest::getInt('id');
			$option = JRequest::getCmd('component');
			$data = array('params' => $params, 'id' => $id, 'option' => $option);
		}
		else
		{
			JRequest::checkToken() or jexit('Invalid Token');
			$data = JRequest::get('post');
		}
		$this->checkPermissions();
		$model = SigProModel::getInstance('Settings', 'SigProModel');
		$model->setState('option', 'com_sigpro');
		$model->setState('data', $data);
		$response = new stdClass;
		if ($model->save())
		{
			$response->message = JText::_('COM_SIGPRO_SETTINGS_SAVED');
			$response->type = 'message';
		}
		else
		{
			$response->message = $model->getError();
			$response->type = 'error';
		}
		return $response;
	}

	public function cancel()
	{
		$this->setRedirect('index.php?option=com_sigpro');
	}

	protected function checkPermissions()
	{
		if (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			if (!JFactory::getUser()->authorise('core.admin', 'com_sigpro'))
			{
				JFactory::getApplication()->redirect('index.php?option=com_sigpro', JText::_('JERROR_ALERTNOAUTHOR'));
				return;
			}
		}
	}

}
