<?php
/**
 * Plugin Module Helper File
 *
 * @package         Advanced Module Manager
 * @version         4.22.2
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

/*
 * ModuleHelper methods
 */

class plgSystemAdvancedModuleHelper
{
	public function onRenderModule(&$module)
	{
		// Module already nulled
		if (is_null($module))
		{
			return;
		}

		// Do nothing if is not frontend
		if (!JFactory::getApplication()->isSite())
		{
			return;
		}

		// return true if module is empty (this will empty the content)
		if ($this->isEmpty($module))
		{
			$module = null;

			return;
		}

	}

	public function isEmpty(&$module)
	{
		if (!isset($module->content))
		{
			return true;
		}

		$params = isset($module->adv_param) ? $module->adv_param : (isset($module->advancedparams) ? $module->advancedparams : null);

		// return false if hideempty is off in module params
		if (empty($params) || !isset($params->hideempty) || !$params->hideempty)
		{
			return false;
		}

		$config = $this->getConfig();

		// return false if show_hideempty is off in main config
		if (!$config->show_hideempty)
		{
			return false;
		}

		$content = trim($module->content);

		// return true if module is empty
		if ($content == '')
		{
			return true;
		}

		// remove html and hidden whitespace
		$content = str_replace(chr(194) . chr(160), ' ', $content);
		$content = str_replace(array('&nbsp;', '&#160;'), ' ', $content);
		// remove comment tags
		$content = preg_replace('#<\!--.*?-->#si', '', $content);
		// remove all closing tags
		$content = preg_replace('#</[^>]+>#si', '', $content);
		// remove tags to be ignored
		$tags = 'p|div|span|strong|b|em|i|ul|font|br|h[0-9]|fieldset|label|ul|ol|li|table|thead|tbody|tfoot|tr|th|td|form';
		$s = '#<(' . $tags . ')([^a-z0-9>][^>]*)?>#si';

		if (@preg_match($s . 'u', $content))
		{
			$s .= 'u';
		}

		if (preg_match($s, $content))
		{
			$content = preg_replace($s, '', $content);
		}

		// return whether content is empty
		return (trim($content) == '');
	}


	public function onPrepareModuleList(&$modules)
	{
		// return if is not frontend
		if (!JFactory::getApplication()->isSite())
		{
			return;
		}

		jimport('joomla.filesystem.file');

		require_once JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
		$parameters = nnParameters::getInstance();

		require_once JPATH_PLUGINS . '/system/nnframework/helpers/assignments.php';
		$assignments_helper = new nnFrameworkAssignmentsHelper;

		require_once JPATH_ADMINISTRATOR . '/components/com_advancedmodules/models/module.php';
		$model = new AdvancedModulesModelModule;

		$xmlfile_assignments = JPATH_ADMINISTRATOR . '/components/com_advancedmodules/assignments.xml';

		$modules = is_null($modules) ? $this->getModuleList() : $modules;

		if(is_array($modules) && empty($modules))
		{
			return;
		}

		foreach ($modules as $i => $module)
		{
			if (!isset($module->advancedparams))
			{
				$module->advancedparams = $this->getAdvancedParamsById($module->id);
			}

			$module->advancedparams = json_decode($module->advancedparams);
			if (
				!isset($module->advancedparams->assignto_menuitems)
				|| isset($module->advancedparams->assignto_urls_selection_sef)
				|| (
					!is_array($module->advancedparams->assignto_menuitems)
					&& strpos($module->advancedparams->assignto_menuitems, '|') !== false
				)
			)
			{
				$module->advancedparams = (object) $model->initAssignments($module->id, $module);
			}

			$module->advancedparams = $parameters->getParams($module->advancedparams, $xmlfile_assignments);

			if ($module->advancedparams === 0)
			{
				if (isset($module->published) && !$module->published)
				{
					unset($modules[$i]);

					continue;
				}

				$modules[$i] = $module;

				continue;
			}


			$module->reverse = 0;

			$this->setMirrorParams($module, $xmlfile_assignments);
			$this->removeDisabledAssignments($module->advancedparams);

			$assignments = $assignments_helper->getAssignmentsFromParams($module->advancedparams);
			$module->published = $assignments_helper->passAll(
				$assignments,
				$module->advancedparams->match_method
			);

			if ($module->reverse)
			{
				$module->published = !$module->published;
			}

			if (isset($module->published) && !$module->published)
			{
				unset($modules[$i]);

				continue;
			}

			$modules[$i] = $module;
		}

		$modules = array_values($modules);
	}

	private function setMirrorParams(&$module, $xmlfile_assignments)
	{
		if (!isset($module->advancedparams->mirror_module)
			|| !$module->advancedparams->mirror_module
			|| !isset($module->advancedparams->mirror_moduleid)
			|| empty($module->advancedparams->mirror_moduleid)
		)
		{
			return;
		}

		$parameters = nnParameters::getInstance();

		// Check if module should mirror another modules assignment settings
		$count = 0;
		while ($count++ < 10
			&& isset($module->advancedparams->mirror_module)
			&& $module->advancedparams->mirror_module
			&& isset($module->advancedparams->mirror_moduleid)
			&& !empty($module->advancedparams->mirror_moduleid)
		)
		{
			if (is_array($module->advancedparams->mirror_moduleid))
			{
				$module->advancedparams->mirror_moduleid = (int) $module->advancedparams->mirror_moduleid['0'];
			}
			$mirror_moduleid = (int) $module->advancedparams->mirror_moduleid;
			$module->reverse = ($module->advancedparams->mirror_module == 2);

			if (!$mirror_moduleid)
			{
				continue;
			}

			if ($mirror_moduleid == $module->id)
			{
				$empty = new stdClass;
				$mirror_params = $parameters->getParams($empty, $xmlfile_assignments);
			}
			else
			{
				if (isset($modules[$mirror_moduleid]))
				{
					if (!isset($modules[$mirror_moduleid]->adv_param))
					{
						$modules[$mirror_moduleid]->adv_param = $this->getAdvancedParamsById($mirror_moduleid);
						$modules[$mirror_moduleid]->adv_param = $parameters->getParams($modules[$mirror_moduleid]->adv_param, $xmlfile_assignments);
					}
					$mirror_params = $modules[$mirror_moduleid]->advancedparams;
				}
				else
				{
					$mirror_params = $this->getAdvancedParamsById($mirror_moduleid);
					$mirror_params = $parameters->getParams($mirror_params, $xmlfile_assignments);
				}
			}

			// Keep the advanced settings that shouldn't be mirrored
			$settings_to_keep = array(
				'hideempty', 'color',
			);

			foreach ($settings_to_keep as $key)
			{
				if (!isset($module->advancedparams->$key))
				{
					continue;
				}

				$mirror_params->$key = $module->advancedparams->$key;
			}

			$module->advancedparams = $mirror_params;
		}
	}

	private function removeDisabledAssignments(&$params)
	{
		$config = $this->getConfig();

		if (!$config->show_assignto_homepage)
		{
			$params->assignto_homepage = 0;
		}
		if (!$config->show_assignto_usergrouplevels)
		{
			$params->assignto_usergrouplevels = 0;
		}
		if (!$config->show_assignto_date)
		{
			$params->assignto_date = 0;
		}
		if (!$config->show_assignto_languages)
		{
			$params->assignto_languages = 0;
		}
		if (!$config->show_assignto_templates)
		{
			$params->assignto_templates = 0;
		}
		if (!$config->show_assignto_urls)
		{
			$params->assignto_urls = 0;
		}
		if (!$config->show_assignto_os)
		{
			$params->assignto_os = 0;
		}
		if (!$config->show_assignto_browsers)
		{
			$params->assignto_browsers = 0;
		}
		if (!$config->show_assignto_components)
		{
			$params->assignto_components = 0;
		}
		if (!$config->show_assignto_tags)
		{
			$params->show_assignto_tags = 0;
		}
		if (!$config->show_assignto_content)
		{
			$params->assignto_contentpagetypes = 0;
			$params->assignto_cats = 0;
			$params->assignto_articles = 0;
		}
	}


	private function getModuleList()
	{
		$app = JFactory::getApplication();
		$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());
		$lang = JFactory::getLanguage()->getTag();
		$clientId = (int) $app->getClientId();

		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params')
			->select('am.params as advancedparams, 0 as menuid, m.publish_up, m.publish_down')
			->from('#__modules AS m')
			->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id')
			->join('LEFT', '#__advancedmodules as am ON am.moduleid = m.id')
			->where('m.published = 1')
			->where('e.enabled = 1')
			->where('m.access IN (' . $groups . ')')
			->where('m.client_id = ' . $clientId);

		// Filter by language
		if ($app->isSite() && $app->getLanguageFilter())
		{
			$query->where('m.language IN (' . $db->quote($lang) . ',' . $db->quote('*') . ')');
		}

		$query->order('m.position, m.ordering');

		// Set the query
		$db->setQuery($query);

		try
		{
			$modules = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JLog::add(JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()), JLog::WARNING, 'jerror');

			return array();
		}

		return $modules;
	}

	private function getConfig()
	{
		static $instance;
		if (!is_object($instance))
		{
			require_once JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
			$parameters = nnParameters::getInstance();
			$instance = $parameters->getComponentParams('advancedmodules');
		}

		return $instance;
	}

	private function getAdvancedParamsById($id)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('a.params')
			->from('#__advancedmodules AS a')
			->where('a.moduleid = ' . (int) $id);
		$db->setQuery($query);

		return $db->loadResult();
	}
}
