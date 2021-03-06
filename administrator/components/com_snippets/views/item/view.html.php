<?php
/**
 * Item View
 *
 * @package         Snippets
 * @version         3.5.2
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2014 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Item View
 */
class SnippetsViewItem extends JViewLegacy
{
	protected $item;
	protected $form;
	protected $state;
	protected $config;
	protected $parameters;

	/**
	 * Display the view
	 *
	 */
	public function display($tpl = null)
	{
		require_once JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
		$this->parameters = NNParameters::getInstance();

		$this->form = $this->get('Form');
		$this->item = $this->_models['item']->getItem(null, 1);
		$this->state = $this->get('State');
		$this->config = $this->parameters->getComponentParams('snippets', $this->state->params);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->id == 0);
		$canDo = SnippetsHelper::getActions();

		JHtml::stylesheet('nnframework/style.min.css', false, true);
		JHtml::stylesheet('snippets/style.min.css', false, true);

		JFactory::getApplication()->input->set('hidemainmenu', true);

		// Set document title
		JFactory::getDocument()->setTitle(JText::_('SNIPPETS') . ': ' . JText::_('NN_ITEM'));
		// Set ToolBar title
		JToolbarHelper::title(JText::_('SNIPPETS') . ': ' . JText::_('NN_ITEM'), 'snippets icon-nonumber');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::apply('item.apply');
			JToolbarHelper::save('item.save');
		}

		if ($canDo->get('core.edit') && $canDo->get('core.create'))
		{
			JToolbarHelper::save2new('item.save2new');
		}
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('item.save2copy');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('item.cancel');
		}
		else
		{
			JToolbarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
		}
	}

	protected function render(&$form, $name = '', $title = '')
	{
		$items = array();
		foreach ($form->getFieldset($name) as $field)
		{
			$items[] = '<div class="control-group"><div class="control-label">'
				. $field->label
				. '</div><div class="controls">'
				. $field->input
				. '</div></div>';
		}
		if (empty ($items))
		{
			return '';
		}

		return implode('', $items);
	}
}
