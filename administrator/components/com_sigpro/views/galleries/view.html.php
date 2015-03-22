<?php
/**
 * @version		$Id: view.html.php 2725 2013-04-06 17:05:49Z joomlaworks $
 * @package		Simple Image Gallery Pro
 * @author		JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

class SigProViewGalleries extends SigProView
{

	public function display($tpl = null)
	{
		$model = SigProModel::getInstance('Galleries', 'SigProModel');
		$model->setState('limit', $this->limit);
		$model->setState('limitstart', $this->limitstart);
		$model->setState('sorting', $this->sorting);
		$model->setState('type', $this->type);
		$galleries = $model->getData();
		$this->assignRef('rows', $galleries);

		jimport('joomla.html.pagination');
		$total = $model->getState('total');
		$this->assignRef('total', $total);
		$pagination = new JPagination($total, $this->limitstart, $this->limit);
		$this->assignRef('pagination', $pagination);

		if ($this->type == 'k2')
		{
			$frameSrc = 'index.php?option=com_k2&amp;view=items&amp;task=element&amp;tmpl=component&amp;limit=10';
			$frameHeight = 450;
			$frameClass = 'sigProModalK2ItemsFrame';
		}
		else
		{
			$frameSrc = 'index.php?option=com_sigpro&amp;view=galleries&amp;task=add&amp;tmpl=component&amp;editorName='.$this->editorName.'&amp;type='.$this->type.'&amp;parentTmpl='.$this->tmpl;
			$frameHeight = 50;
			$frameClass = 'sigProModalAddGalleryFrame';
		}
		$this->assignRef('frameSrc', $frameSrc);
		$this->assignRef('frameHeight', $frameHeight);
		$this->assignRef('frameClass', $frameClass);

		$options = array();
		$options[] = JHTML::_('select.option', 'folder ASC', JText::_('COM_SIGPRO_FOLDER_NAME_ASC'));
		$options[] = JHTML::_('select.option', 'folder DESC', JText::_('COM_SIGPRO_FOLDER_NAME_DESC'));
		$options[] = JHTML::_('select.option', 'modified ASC', JText::_('COM_SIGPRO_MODIFIED_DATE_ASC'));
		$options[] = JHTML::_('select.option', 'modified DESC', JText::_('COM_SIGPRO_MODIFIED_DATE_DESC'));
		$lists = array();
		$lists['sorting'] = JHTML::_('select.genericlist', $options, 'sorting', 'onchange="this.form.submit();"', 'value', 'text', $this->sorting);
		$this->assignRef('lists', $lists);

		parent::display($tpl);
	}

}
