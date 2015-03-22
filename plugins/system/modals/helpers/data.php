<?php
/**
 * Plugin Helper File: Data
 *
 * @package         Modals
 * @version         5.0.5
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

class plgSystemModalsHelperData
{
	var $helpers = array();

	public function __construct()
	{
		require_once __DIR__ . '/helpers.php';
		$this->helpers = plgSystemModalsHelpers::getInstance();
		$this->params = $this->helpers->getParams();

		$this->params->open_count = $this->getOpenCount();
	}

	public function setDataWidthHeight(&$data, $isexternal)
	{
		$this->setDataAxis($data, $isexternal, 'width');
		$this->setDataAxis($data, $isexternal, 'height');
	}

	public function setDataAxis(&$data, $isexternal, $axis = 'width')
	{
		if (!empty($data[$axis]))
		{
			return;
		}

		if ($isexternal)
		{
			$data[$axis] = $this->params->{'external' . $axis} ?: $this->params->{$axis} ?: '95%';

			return;
		}

		$data[$axis] = $this->params->{$axis} ?: $this->params->{'external' . $axis} ?: '95%';
	}

	public function setDataOpen(&$data)
	{
		if (empty($data['openonce']) && empty($data['openmin']) && empty($data['openmax']))
		{
			unset($data['openonce']);
			unset($data['openmin']);
			unset($data['openmax']);

			return;
		}

		unset($data['open']);

		$min = !empty($data['openonce']) ? 0 : (!empty($data['openmin']) ? (int) $data['openmin'] : 0);
		$max = !empty($data['openonce']) ? 1 : (!empty($data['openmax']) ? (int) $data['openmax'] : 0);

		if (($max && $this->params->open_count <= $max) && $this->params->open_count >= $min)
		{
			$data['open'] = 'true';
		}

		unset($data['openonce']);
		unset($data['openmin']);
		unset($data['openmax']);
	}

	public function flattenAttributeList($attributes)
	{
		$string = '';
		foreach ($attributes as $key => $val)
		{
			$key = trim($key);
			$val = trim($val);

			if ($key == '' || $val == '')
			{
				continue;
			}

			$string .= ' ' . $key . '="' . $val . '"';
		}

		return $string;
	}

	public function flattenDataAttributeList(&$dat)
	{
		if (isset($dat['width']))
		{
			unset($dat['externalWidth']);
		}

		if (isset($dat['height']))
		{
			unset($dat['externalHeight']);
		}

		$data = array();
		foreach ($dat as $key => $val)
		{
			if (!$str = $this->flattenDataAttribute($key, $val))
			{
				continue;
			}
			$data[] = $str;
		}

		return empty($data) ? '' : ' ' . implode(' ', $data);
	}

	public function flattenDataAttribute($key, $val)
	{
		if ($key == '' || $val == '')
		{
			return false;
		}

		if (strpos($key, 'title_') !== false || strpos($key, 'description_') !== false)
		{
			return false;
		}

		$key = $key == 'externalWidth' ? 'width' : $key;
		$key = $key == 'externalHeight' ? 'height' : $key;


		$val = str_replace('"', '&quot;', $val);

		if ($key == 'group')
		{
			// map group value to rel
			return 'data-modal-rel="' . $val . '"';
		}

		if (($key == 'width' || $key == 'height') && strpos($val, '%') === false)
		{
			// set param to innerWidth/innerHeight if value of width/height is a percentage
			return 'data-modal-inner-' . $key . '="' . $val . '"';
		}

		if (in_array(strtolower($key), $this->params->paramNamesLowercase))
		{
			// fix use of lowercase params that should contain uppercase letters
			$key = $this->params->paramNamesCamelcase[array_search(strtolower($key), $this->params->paramNamesLowercase)];
			$key = strtolower(preg_replace('#([A-Z])#', '-\1', $key));
		}

		return 'data-modal-' . $key . '="' . $val . '"';
	}

	private function getOpenCount()
	{
		switch ($this->params->open_count_based_on)
		{
			case 'page':
				$cookie_name = 'nn_modals_' . md5(NNText::getURI());
				$count = JFactory::getApplication()->input->getString($cookie_name, 0, 'COOKIE') + 1;
				setcookie($cookie_name, $count);

				return $count;
			case 'website':
			default:
				return JFactory::getSession()->get('session.counter', 0);
		}
	}
}
