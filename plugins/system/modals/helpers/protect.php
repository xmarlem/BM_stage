<?php
/**
 * Plugin Helper File: Protect
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

class plgSystemModalsHelperProtect
{
	var $helpers = array();

	public function __construct()
	{
		require_once __DIR__ . '/helpers.php';
		$this->helpers = plgSystemModalsHelpers::getInstance();
		$this->params = $this->helpers->getParams();

		$this->params->protected_tags = array(
			$this->params->tag,
			$this->params->tag_content
		);
	}

	public function protect(&$string)
	{
		NNProtect::protectFields($string);
		NNProtect::protectSourcerer($string);
	}

	public function protectTags(&$string)
	{
		NNProtect::protectTags($string, $this->params->protected_tags);
	}

	public function unprotectTags(&$string)
	{
		NNProtect::unprotectTags($string, $this->params->protected_tags);
	}
}
