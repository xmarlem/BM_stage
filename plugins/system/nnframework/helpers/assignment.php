<?php
/**
 * NoNumber Framework Helper File: Assignments
 *
 * @package         NoNumber Framework
 * @version         15.3.10
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/cache.php';

class nnFrameworkAssignment
{
	public $request = null;
	public $date = null;
	public $db = null;
	public $selection = null;
	public $params = null;
	public $assignment = null;
	public $article = null;

	public function __construct($request, $date)
	{
		$this->request = $request;
		$this->date = $date;
		$this->db = JFactory::getDBO();
	}

	public function init()
	{
	}

	public function initAssignment($assignment, $article = 0)
	{
		$this->selection = $assignment->selection;
		$this->params = $assignment->params;
		$this->assignment = $assignment->assignment;
		$this->article = $article;
	}

	public function pass($pass = true, $assignment = null)
	{
		$assignment = $assignment ?: $this->assignment;

		return $pass ? ($assignment == 'include') : ($assignment == 'exclude');
	}

	public function passSimple($values = '', $caseinsensitive = false, $assignment = null, $selection = null)
	{
		$values = $this->makeArray($values, true);
		$assignment = $assignment ?: $this->assignment;
		$selection = $selection ?: $this->selection;

		$pass = false;
		foreach ($values as $value)
		{
			if ($caseinsensitive)
			{
				if (in_array(strtolower($value), array_map('strtolower', $selection)))
				{
					$pass = true;
					break;
				}

				continue;
			}

			if (in_array($value, $selection))
			{
				$pass = true;
				break;
			}
		}

		return $this->pass($pass, $assignment);
	}

	public function passItemByType(&$pass, $type = '', $data = null)
	{
		$pass_type = !empty($data) ? $this->{'pass' . $type}($data) : $this->{'pass' . $type}();

		if ($pass_type == null)
		{
			return true;
		}

		$pass = $pass_type;

		return $pass;
	}

	public function passByPageTypes($option, $selection = array(), $assignment = 'all', $add_view = false)
	{
		if ($this->request->option != $option)
		{
			return $this->pass(false, $assignment);
		}

		$pagetype = $this->request->view;

		if ($this->request->layout && $this->request->layout != 'default')
		{
			$pagetype = ($add_view ? $pagetype . '_' : '') . $this->request->layout;
		}

		return $this->passSimple($pagetype, $selection, $assignment);
	}

	function getMenuItemParams($id = 0)
	{
		$hash = md5('getMenuItemParams_' . $id);

		if (nnCache::has($hash))
		{
			return nnCache::get($hash);
		}

		$query = $this->db->getQuery(true)
			->select('m.params')
			->from('#__menu AS m')
			->where('m.id = ' . (int) $id);
		$this->db->setQuery($query);
		$params = $this->db->loadResult();

		$parameters = nnParameters::getInstance();

		return nnCache::set($hash,
			$parameters->getParams($params)
		);
	}

	function getParentIds($id = 0, $table = 'menu', $parent = 'parent_id', $child = 'id')
	{
		if (!$id)
		{
			return array();
		}

		$hash = md5('getParentIds_' . $id . '_' . $table . '_' . $parent . '_' . $child);

		if (nnCache::has($hash))
		{
			return nnCache::get($hash);
		}

		$parent_ids = array();

		while ($id)
		{
			$query = $this->db->getQuery(true)
				->select('t.' . $parent)
				->from('#__' . $table . ' as t')
				->where('t.' . $child . ' = ' . (int) $id);
			$this->db->setQuery($query);
			$id = $this->db->loadResult();

			if (!$id)
			{
				continue;
			}

			$parent_ids[] = $id;
		}

		return nnCache::set($hash,
			$parent_ids
		);
	}

	public function makeArray($array = '', $onlycommas = false, $trim = true)
	{
		if (empty($array))
		{
			return array();
		}

		$hash = md5('makeArray_' . json_encode($array) . '_' . $onlycommas . '_' . $trim);

		if (nnCache::has($hash))
		{
			return nnCache::get($hash);
		}

		$array = $this->mixedDataToArray($array, $onlycommas);

		if (empty($array))
		{
			return $array;
		}

		if (!$trim)
		{
			return $array;
		}

		foreach ($array as $k => $v)
		{
			if (!is_string($v))
			{
				continue;
			}

			$array[$k] = trim($v);
		}

		return nnCache::set($hash,
			$array
		);
	}

	private function mixedDataToArray($array = '', $onlycommas = 0)
	{
		if (!is_array($array))
		{
			$delimiter = ($onlycommas || strpos($array, '|') === false) ? ',' : '|';

			return explode($delimiter, $array);
		}

		if (empty($array))
		{
			return $array;
		}

		if (isset($array['0']) && is_array($array['0']))
		{
			return $array['0'];
		}

		if (count($array) === 1 && strpos($array['0'], ',') !== false)
		{
			return explode(',', $array['0']);
		}

		return $array;
	}

	public function passContentIds()
	{
		if (empty($this->selection))
		{
			return null;
		}

		return in_array($this->request->id, $this->selection);
	}

	public function passContentKeywords($fields = array('title', 'introtext', 'fulltext'), $text = '')
	{
		if (empty($this->params->content_keywords))
		{
			return null;
		}

		if (!$text)
		{
			$item = $this->getItem($fields);

			foreach ($fields as $field)
			{
				if (!isset($item->{$field}))
				{
					return false;
				}

				$text = trim($text . ' ' . $item->{$field});

			}
		}

		if (empty($text))
		{
			return false;
		}

		$this->params->content_keywords = $this->makeArray($this->params->content_keywords);

		foreach ($this->params->content_keywords as $keyword)
		{
			if (!preg_match('#\b' . preg_quote($keyword, '#') . '\b#si', $text))
			{
				continue;
			}

			return true;
		}
	}

	public function passMetaKeywords($field = 'metakey', $keywords = '')
	{
		if (empty($this->params->meta_keywords))
		{
			return null;
		}

		if (!$keywords)
		{
			$item = $this->getItem($field);

			if (!isset($item->metakey) || empty($item->metakey))
			{
				return false;
			}

			$keywords = $item->metakey;
		}

		if (empty($keywords))
		{
			return false;
		}

		if (is_string($keywords))
		{
			$keywords = str_replace(' ', ',', $keywords);
		}

		$keywords = $this->makeArray($keywords);

		$this->params->meta_keywords = $this->makeArray($this->params->meta_keywords);

		foreach ($this->params->meta_keywords as $keyword)
		{
			if (!$keyword || !in_array(trim($keyword), $keywords))
			{
				continue;
			}

			return true;
		}
	}

	public function passAuthors($field = 'created_by', $author = '')
	{
		if (empty($this->params->authors))
		{
			return null;
		}

		if (!$author)
		{
			$item = $this->getItem($field);

			if (!isset($item->{$field}))
			{
				return false;
			}

			$author = $item->{$field};
		}

		if (empty($author))
		{
			return false;
		}

		$this->params->authors = $this->makeArray($this->params->authors);

		return in_array($author, $this->params->authors);
	}

	public function getItem($fields = array())
	{
		return null;
	}
}
