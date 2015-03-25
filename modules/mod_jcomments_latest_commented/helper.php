<?php
// no direct access
defined('_JEXEC') or die;

class modJCommentsLatestCommentedHelper
{
	static function getList( &$params )
	{
		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		$source = $params->get('source', 'com_content');
		if (!is_array($source)) {
			$source = explode(',', $source);
		}
		
		$date = JFactory::getDate();

		if (version_compare(JVERSION,'1.6.0','ge')) {
			$now = $date->toSql();
			$access = array_unique(JAccess::getAuthorisedViewLevels($user->get('id')));
			$access[] = 0; // for backward compability
		} else {
			$now = $date->toMySQL();
			$access = $user->get('aid', 0);
		}

		$where = array();

		$where[] = 'c.published = 1';
		$where[] = 'c.deleted = 0';
		$where[] = "o.link <> ''";
		$where[] = (is_array($access) ? "o.access IN (" . implode(',', $access) . ")" : " o.access <= " . (int) $access);

		if (JCommentsMultilingual::isEnabled()) {
			$where[] = 'o.lang = ' . $db->Quote(JCommentsMultilingual::getLanguage());
		}

		$joins = array();

		if (count($source) == 1 && $source[0] == 'com_content') {
			$joins[] = 'JOIN #__content AS cc ON cc.id = o.object_id';
			$joins[] = 'LEFT JOIN #__categories AS ct ON ct.id = cc.catid';

			$where[] = "c.object_group = " . $db->Quote($source[0]);
			$where[] = "(cc.publish_up = '0000-00-00 00:00:00' OR cc.publish_up <= '$now')";
			$where[] = "(cc.publish_down = '0000-00-00 00:00:00' OR cc.publish_down >= '$now')";

			$categories = $params->get('catid', array());
			if (!is_array($categories)) {
				$categories = explode(',', $categories);
			}

			JArrayHelper::toInteger($categories);

			$categories = implode(',', $categories);
			if (!empty($categories)) {
				$where[] = "cc.catid IN (" . $categories . ")";
			}
		} else if (count($source)) {
			$where[] = "c.object_group in ('" . implode("','", $source) . "')";
		}

		$query = "SELECT o.id, o.title, o.link"
			. ", COUNT(c.id) AS commentsCount, MAX(c.date) AS commentdate"
			. " FROM #__jcomments_objects AS o"
			. " JOIN #__jcomments AS c ON c.object_id = o.object_id AND c.object_group = o.object_group AND c.lang = o.lang"
			. (count($joins) ? ' ' . implode(' ', $joins) : '')
			. (count($where) ? ' WHERE  ' . implode(' AND ', $where) : '')
			. " GROUP BY o.id, o.title, o.link"
			. " ORDER BY commentdate DESC"
			;

		$db->setQuery($query, 0, $params->get('count'));
		$list = $db->loadObjectList();

		return $list;
	}
}
