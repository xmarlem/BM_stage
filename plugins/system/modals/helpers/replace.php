<?php
/**
 * Plugin Helper File: Replace
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

class plgSystemModalsHelperReplace
{
	var $helpers = array();

	public function __construct()
	{
		require_once __DIR__ . '/helpers.php';
		$this->helpers = plgSystemModalsHelpers::getInstance();
		$this->params = $this->helpers->getParams();

		$bts = '((?:<(?:p|span|div)(?:(?:\s|&nbsp;)[^>]*)?>\s*){0,3})'; // break tags start
		$bte = '((?:\s*</(?:p|span|div)>){0,3})'; // break tags end

		$regex_a = NNText::getTagRegex('a', false, false);
		$regex_span_img = NNText::getTagRegex(array('span', 'img'));
		$regex_img = NNText::getTagRegex('img', false, false, 'class');
		$regex_text_whitespace = '[^<\{]*';

		$this->params->regex = '#'
			. $bts
			. '\{' . $this->params->tag . '(?:\s|&nbsp;)+'
			. '((?:[^\}]*?\{[^\}]*?\})*[^\}]*?)'
			. '\}'
			. $bte
			. '\s*(.*?)\s*'
			. $bts
			. '\{\/' . $this->params->tag . '\}'
			. $bte
			. '#s';
		$this->params->regex_end = '#'
			. $bts
			. '\{\/' . $this->params->tag . '\}'
			. $bte
			. '#s';
		$this->params->regex_inlink = '#'
			. '(' . $regex_a . $regex_text_whitespace . ')((?:' . $regex_span_img . $regex_text_whitespace . ')*)'
			. '\{' . $this->params->tag
			. '((?:(?:\s|&nbsp;)+(?:[^\}]*?\{[^\}]*?\})*[^\}]*?)?)'
			. '\}'
			. '(.*?)'
			. '\{\/' . $this->params->tag . '\}'
			. '((?:' . $regex_text_whitespace . $regex_span_img . ')*)' . $regex_text_whitespace . '<\/a>'
			. '#s';
		$this->params->regex_link = '#'
			. '<a(?:\s|&nbsp;|&\#160;)[^>"]*(?:"[^"]*"[^>"]*)*>'
			. '#s';
		$this->params->regex_image = '#'
			. '((?:' . $regex_a . $regex_text_whitespace . ')?)'
			. '(' . $regex_img . ')'
			. '((?:' . $regex_text_whitespace . '<\/a>)?)'
			. '#s';
		$this->params->regex_content = '#'
			. $bts
			. '\{' . $this->params->tag_content . '[ =]'
			. '((?:[^\}]*?\{[^\}]*?\})*[^\}]*?)'
			. '\}'
			. $bte
			. '#s';
		$this->params->regex_content_end = '#'
			. $bts
			. '\{\/' . $this->params->tag_content . '\}'
			. $bte
			. '#s';
	}

	public function replace(&$string, $area = 'article')
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		NNProtect::removeFromHtmlTagAttributes(
			$string, array(
				$this->params->tag,
				$this->params->tag_content
			)
		);

		// allow in component?
		if ($area == 'component' && in_array(JFactory::getApplication()->input->get('option'), $this->params->disabled_components))
		{
			$this->helpers->get('protect')->protectTags($string);

			return;
		}

		$this->helpers->get('protect')->protect($string);

		// Handle content inside the iframed modal
		if (JFactory::getApplication()->input->getInt('ml', 0) && JFactory::getApplication()->input->getInt('iframe', 0))
		{
			$this->replaceInsideModal($string);

			NNProtect::unprotect($string);

			return;
		}

		$this->replaceLinks($string);

		// tag syntax inside links
		$this->replaceTagSyntaxInsideLinks($string);

		// tag syntax
		$this->replaceTagSyntax($string);

		// closing tag
		$this->replaceClosingTag($string);

		// content tag
		$this->replaceContentTag($string);

		// content closing tag
		$this->replaceClosingContentTag($string);

		$this->replaceImages($string);

		NNProtect::unprotect($string);
	}

	// add ml to internal links
	private function replaceInsideModal(&$string)
	{
		if (preg_match_all($this->params->regex_link, $string, $matches, PREG_SET_ORDER) < 1)
		{
			return;
		}

		foreach ($matches as $match)
		{
			// get the link attributes
			$attributes = $this->helpers->get('link')->getLinkAttributeList($match['0']);

			// ignore if the link has no href or is an anchor or has a target
			if (empty($attributes->href) || $attributes->href['0'] != '#' || isset($attributes->target))
			{
				continue;
			}

			// ignore if link is external or an image
			if ($this->helpers->get('file')->isExternal($attributes->href) || $this->helpers->get('file')->isMedia($attributes->href))
			{
				continue;
			}

			$href = $attributes->href;
			$this->helpers->get('scripts')->addTmpl($attributes->href, 1);
			$this->replaceOnce('href="' . $href . '"', 'href="' . $attributes->href . '"', $string);
		}
	}

	private function replaceTagSyntaxInsideLinks(&$string)
	{
		if (preg_match_all($this->params->regex_inlink, $string, $matches, PREG_SET_ORDER) < 1)
		{
			return;
		}

		foreach ($matches as $match)
		{
			$data = preg_replace('#^(\s|&nbsp;|&\#160;)*#', '', $match['3']);
			$content = trim($match['2'] . $match['4'] . $match['5']);

			list($link, $extra) = $this->helpers->get('link')->getLink($data, $match['1'], $content);
			$link .= '</a>';

			$this->replaceOnce($match['0'], $link, $string, $extra);
		}
	}

	private function replaceTagSyntax(&$string)
	{
		if (preg_match_all($this->params->regex, $string, $matches, PREG_SET_ORDER) < 1)
		{
			return;
		}

		foreach ($matches as $match)
		{
			list($pre, $post) = NNTags::setSurroundingTags($match['1'], $match['3']);
			list($link, $extra) = $this->helpers->get('link')->getLink($match['2'], '', trim($match['4']));

			$html = $post . $pre . $link;

			list($pre, $post) = NNTags::setSurroundingTags($match['5'], $match['6']);
			$html .= $pre . '</a>' . $post;

			$this->replaceOnce($match['0'], $html, $string, $extra);
		}
	}

	private function replaceClosingTag(&$string)
	{
		if (preg_match_all($this->params->regex_end, $string, $matches, PREG_SET_ORDER) < 1)
		{
			return;
		}

		foreach ($matches as $match)
		{
			list($pre, $post) = NNTags::setSurroundingTags($match['1'], $match['2']);
			$html = $pre . '</a>' . $post;
			$this->replaceOnce($match['0'], $html, $string);
		}
	}

	private function replaceLinks(&$string)
	{
		if (
			(
				empty($this->params->classnames)
				&& !preg_match('#class="[^"]*(' . implode('|', $this->params->classnames) . ')#s', $string)
			)
			&& !$this->params->external
			&& !$this->params->target
			&& empty($this->params->filetypes)
			&& empty($this->params->urls)
		)
		{
			return;
		}

		if (preg_match_all($this->params->regex_link, $string, $matches, PREG_SET_ORDER) < 1)
		{
			return;
		}

		foreach ($matches as $match)
		{
			$this->replaceLink($string, $match);
		}
	}

	private function replaceLink(&$string, $match)
	{
		// get the link attributes
		$attributes = $this->helpers->get('link')->getLinkAttributeList($match['0']);

		if (!$this->helpers->get('pass')->passLinkChecks($attributes))
		{
			return;
		}

		$data = array();
		$isexternal = $this->helpers->get('file')->isExternal($attributes->href);
		$ismedia = $this->helpers->get('file')->isMedia($attributes->href);
		$iframe = $this->helpers->get('file')->isIframe($attributes->href, $data);

		// Force/overrule certain data values
		if ($iframe || ($isexternal && !$ismedia))
		{
			// use iframe mode for external urls
			$data['iframe'] = 'true';
			$this->helpers->get('data')->setDataWidthHeight($data, $isexternal);
		}

		$attributes->class = !empty($attributes->class) ? $attributes->class . ' ' . $this->params->class : $this->params->class;
		$link = $this->helpers->get('link')->buildLink($attributes, $data);

		$this->replaceOnce($match['0'], $link, $string);
	}

	private function replaceOnce($search, $replace, &$string, $extra = '')
	{
		if (!$extra
			|| !preg_match('#' . preg_quote($search, '#') . '(.*?</(?:div|p)>)#', $string, $match)
		)
		{
			$string = NNText::strReplaceOnce($search, $replace . $extra, $string);

			return;
		}

		// Place the extra div stuff behind the first ending div/p tag
		$string = NNText::strReplaceOnce(
			$match['0'],
			$replace . $match['1'] . $extra,
			$string
		);
	}

	private function replaceContentTag(&$string)
	{
		// content tag
		if (preg_match_all($this->params->regex_content, $string, $matches, PREG_SET_ORDER) < 1)
		{
			return;
		}

		foreach ($matches as $match)
		{
			list($pre, $post) = NNTags::setSurroundingTags($match['1'], $match['3']);
			$id = str_replace('#', '', $match['2']);
			$html = $post . '<div style="display:none;"><div id="' . $id . '">' . $pre;
			$this->replaceOnce($match['0'], $html, $string);
		}
	}

	private function replaceClosingContentTag(&$string)
	{
		if (preg_match_all($this->params->regex_content_end, $string, $matches, PREG_SET_ORDER) < 1)
		{
			return;
		}

		foreach ($matches as $match)
		{
			list($pre, $post) = NNTags::setSurroundingTags($match['1'], $match['2']);
			$html = $post . '</div></div>' . $pre;
			$this->replaceOnce($match['0'], $html, $string);
		}
	}

	private function replaceImages(&$string)
	{
		if (
			empty($this->params->classnames_images)
			|| !preg_match('#class="[^"]*(' . implode('|', $this->params->classnames_images) . ')#s', $string)
		)
		{
			return;
		}

		if (preg_match_all($this->params->regex_image, $string, $matches, PREG_SET_ORDER) < 1)
		{
			return;
		}

		jimport('joomla.filesystem.file');
		foreach ($matches as $match)
		{
			$this->replaceImage($string, $match);
		}
	}

	private function replaceImage(&$string, $match)
	{
		if (!empty($match['1']) || !empty($match['3']))
		{
			return;
		}

		// get the image attributes
		$image_attributes = $this->helpers->get('link')->getLinkAttributeList($match['0']);

		if (!isset($image_attributes->class) || !isset($image_attributes->src))
		{
			return;
		}

		$image_attributes->class = explode(' ', $image_attributes->class);

		if (!array_intersect($image_attributes->class, $this->params->classnames_images))
		{
			return;
		}

		$image_attributes->class = implode(' ', array_diff($image_attributes->class, $this->params->classnames_images));

		$image = (object) array(
			'path'      => $this->helpers->get('file')->getFilePath($image_attributes->src),
			'folder'    => $this->helpers->get('file')->getFilePath($image_attributes->src),
			'image'     => $this->helpers->get('file')->getFileName($image_attributes->src),
			'thumbnail' => $this->helpers->get('file')->getFileName($image_attributes->src)
		);
		unset($image_attributes->src);

		$params = (object) array(
			'thumbsuffix' => $this->params->gallery_thumb_suffix
		);

		if (
			!$this->helpers->get('file')->isExternal($image->folder . '/' . $image->image)
			&& $check = $this->helpers->get('image')->getImageObject($image->folder, $image->image, $params)
		)
		{
			$image = $check;
		}

		$attributes = new stdClass;
		$data = array();

		$attributes->href = $image->folder . '/' . $image->image;
		$attributes->class = $this->params->class;

		$this->helpers->get('image')->setImageDataFromDataFile($image->folder, $data);
		$this->helpers->get('image')->setImageDataAtribute('title', $data, $image->image);
		$this->helpers->get('image')->setImageDataAtribute('description', $data, $image->image);

		$data['title'] = isset($image_attributes->title) ? $image_attributes->title : (isset($image_attributes->alt) ? $image_attributes->alt : '');
		if (!$data['title'] && $this->params->auto_titles)
		{
			// set the auto title
			$data['title'] = $this->helpers->get('file')->getTitle($image->image, $this->params->title_case);
		}
		$data['group'] = $this->params->auto_group_id;

		$link = array();
		$link[] = $this->helpers->get('link')->buildLink($attributes, $data);

		$link[] = '<img src="' . $image->folder . '/' . $image->thumbnail . '"' . $this->helpers->get('data')->flattenAttributeList($image_attributes) . ' />';
		$link[] = '</a>';
		$link = implode('', $link);

		$this->replaceOnce($match['2'], $link, $string);
	}
}
