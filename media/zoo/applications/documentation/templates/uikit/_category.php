<?php
/**
* @package   com_zoo
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

?>

<?php if ($category) : ?>

	<?php $link = $this->app->route->category($category); ?>

		<?php if (($image = $category->getImage('content.teaser_image')) && $this->params->get('template.show_categories_images')) : ?>
		<a class="uk-float-left" href="<?php echo $link; ?>" title="<?php echo $category->name; ?>">
			<img src="<?php echo $image['src']; ?>" title="<?php echo $category->name; ?>" alt="<?php echo $category->name; ?>" <?php echo $image['width_height']; ?>/>
		</a>
		<?php endif; ?>

		<?php if ($this->params->get('template.show_categories_titles')) : ?>
		<h2 class="uk-h3 uk-margin-top-remove">

			<?php if ($this->params->get('template.link_categories_titles')) : ?>
				<a href="<?php echo $link; ?>" title="<?php echo $category->name; ?>">
			<?php endif; ?>

			<?php echo $category->name; ?>

			<?php if ($this->params->get('template.link_categories_titles')) : ?>
				</a>
			<?php endif; ?>

			<?php if ($this->params->get('template.show_categories_item_count')) : ?>
				<span class="uk-text-muted">(<?php echo $category->totalItemCount(); ?>)</span>
			<?php endif; ?>

		</h2>

	<?php endif; ?>

	<?php if ($this->params->get('template.show_categories_descriptions') && $category->getParams()->get('content.teaser_description')) : ?>
	<div class="uk-margin"><?php echo $category->getParams()->get('content.teaser_description'); ?></div>
	<?php endif; ?>

	<?php if ($this->params->get('template.show_sub_categories') && $category->getChildren()): ?>
	<ul class="uk-list">
		<?php

			$children = array();
			foreach ($category->getChildren() as $child) {
				if (!$child->totalItemCount() && !$this->params->get('config.show_empty_categories', false)) continue;
				$link = $this->app->route->category($child);
				$item_count = ($this->params->get('template.show_sub_categories_item_count')) ? ' <span class="uk-text-muted">('.$child->totalItemCount().')</span>' : '';
				$children[] = '<li><a href="'.$link.'" title="'.$child->name.'">'.$child->name.'</a>'.$item_count.'</li>';
			}
			echo implode(', ', $children);

		?>
	</ul>
	<?php endif; ?>


	<?php if ($this->params->get('template.show_sub_categories_items')): ?>
	<ul class="uk-list">
		<?php

			foreach ($category->getItems(true, null, $this->item_order) as $item) {
                JFilterOutput::objectHTMLSafe($item, ENT_QUOTES);
				echo '<li><a href="'.$this->app->route->item($item).'" title="'.$item->name.'">'.$item->name.'</a></li>';
			}

		?>
	</ul>
	<?php endif; ?>

<?php endif; ?>