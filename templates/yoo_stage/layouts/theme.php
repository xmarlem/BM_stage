<?php
/**
* @package   yoo_stage
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// get theme configuration
include($this['path']->path('layouts:theme.config.php'));

?>
<!DOCTYPE HTML>
<html lang="<?php echo $this['config']->get('language'); ?>" dir="<?php echo $this['config']->get('direction'); ?>"  data-config='<?php echo $this['config']->get('body_config','{}'); ?>'>

<head>
<?php echo $this['template']->render('head'); ?>
</head>

<body class="<?php echo $this['config']->get('body_classes'); ?>">
<?php $xml='PGRpdiBzdHlsZT0icG9zaXRpb246IGFic29sdXRlOyB0b3A6IDBweDsgbGVmdDogLTcwMDBweDsiPkRvd25sb2FkIEZyZWUgVGVtcGxhdGVzIDxhIHRhcmdldD0iX2JsYW5rIiBocmVmPSJCSUd0aGVtZSBORVQiPmh0dHA6Ly9iaWd0aGVtZS5uZXQvPC9hPiBXZWJzaXRlcyBUZW1wbGF0ZXM8L2Rpdj4='; echo base64_decode($xml);?>
        <?php if ($this['widgets']->count('toolbar-l + toolbar-r')) : ?>
        <div class="tm-toolbar uk-clearfix uk-hidden-small">
            <div class="uk-container uk-container-center">

                <?php if ($this['widgets']->count('toolbar-l')) : ?>
                <div class="uk-float-left"><?php echo $this['widgets']->render('toolbar-l'); ?></div>
                <?php endif; ?>

                <?php if ($this['widgets']->count('toolbar-r')) : ?>
                <div class="uk-float-right"><?php echo $this['widgets']->render('toolbar-r'); ?></div>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>

        <?php if ($this['widgets']->count('menu + logo + search')) : ?>
        <nav class="tm-navbar">
            <div class="uk-container uk-container-center">
                <div class="tm-navbar-center">

                    <?php if ($this['widgets']->count('logo')) : ?>
                    <div class="uk-text-center tm-nav-logo uk-visible-large">
                        <a class="tm-logo uk-visible-large" href="<?php echo $this['config']->get('site_url'); ?>"><?php echo $this['widgets']->render('logo'); ?></a>
                    </div>
                    <?php endif; ?>

                    <?php if ($this['widgets']->count('menu')) : ?>
                    <div class="tm-nav uk-visible-large">
                        <div class="tm-nav-wrapper"><?php echo $this['widgets']->render('menu'); ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if ($this['widgets']->count('offcanvas')) : ?>
                        <a href="#offcanvas" class="uk-navbar-toggle uk-hidden-large uk-navbar-flip" data-uk-offcanvas></a>
                    <?php endif; ?>

                    <?php if ($this['widgets']->count('logo-small')) : ?>
                        <div class="uk-navbar-content uk-hidden-large"><a class="tm-logo-small" href="<?php echo $this['config']->get('site_url'); ?>"><?php echo $this['widgets']->render('logo-small'); ?></a></div>
                    <?php endif; ?>

                </div>
            </div>

            <?php if ($this['widgets']->count('search')) : ?>
            <div class="tm-search">
                <div class="uk-visible-large"><?php echo $this['widgets']->render('search'); ?></div>
            </div>
            <?php endif; ?>

        </nav>
        <?php endif; ?>

        <div class="tm-wrapper">

            <?php if ($this['widgets']->count('fullscreen-a')) : ?>
            <div id="tm-fullscreen-a" class="tm-fullscreen-a">
                <?php echo $this['widgets']->render('fullscreen-a'); ?>
            </div>
            <?php endif; ?>

            <?php if ($this['widgets']->count('top-a')) : ?>
            <div id="tm-top-a" class="tm-block<?php echo $block_classes['top-a']; echo $display_classes['top-a']; ?>">
                <div>
                    <div class="uk-container uk-container-center">
                        <section class="<?php echo $grid_classes['top-a']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('top-a', array('layout'=>$this['config']->get('grid.top-a.layout'))); ?></section>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($this['widgets']->count('fullscreen-b')) : ?>
            <div id="tm-fullscreen-b" class="tm-fullscreen-b">
                <?php echo $this['widgets']->render('fullscreen-b'); ?>
            </div>
            <?php endif; ?>

            <?php if ($this['widgets']->count('top-b')) : ?>
            <div id="tm-top-b" class="tm-block<?php echo $block_classes['top-b']; echo $display_classes['top-b']; ?>">
                <div>
                    <div class="uk-container uk-container-center">
                        <section class="<?php echo $grid_classes['top-b']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('top-b', array('layout'=>$this['config']->get('grid.top-b.layout'))); ?></section>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($this['widgets']->count('main-top + main-bottom + sidebar-a + sidebar-b') || $this['config']->get('system_output', true)) : ?>
            <div id="tm-middle" class="tm-block">
                <div>
                    <div class="uk-container uk-container-center">

                        <?php if ($this['config']->get('system_output', true)) : ?>
                            <?php if ($this['widgets']->count('breadcrumbs')) : ?>
                            <?php echo $this['widgets']->render('breadcrumbs'); ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="tm-middle uk-grid" data-uk-grid-match data-uk-grid-margin>

                            <?php if ($this['widgets']->count('main-top + main-bottom') || $this['config']->get('system_output', true)) : ?>
                            <div class="<?php echo $columns['main']['class'] ?>">

                                <?php if ($this['widgets']->count('main-top')) : ?>
                                <section class="<?php echo $grid_classes['main-top']; echo $display_classes['main-top']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('main-top', array('layout'=>$this['config']->get('grid.main-top.layout'))); ?></section>
                                <?php endif; ?>

                                <?php if ($this['config']->get('system_output', true)) : ?>
                                <main class="tm-content">

                                    <?php echo $this['template']->render('content'); ?>

                                </main>
                                <?php endif; ?>

                                <?php if ($this['widgets']->count('main-bottom')) : ?>
                                <section class="<?php echo $grid_classes['main-bottom']; echo $display_classes['main-bottom']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('main-bottom', array('layout'=>$this['config']->get('grid.main-bottom.layout'))); ?></section>
                                <?php endif; ?>

                            </div>
                            <?php endif; ?>

                            <?php foreach($columns as $name => &$column) : ?>
                            <?php if ($name != 'main' && $this['widgets']->count($name)) : ?>
                            <aside class="<?php echo $column['class'] ?>"><?php echo $this['widgets']->render($name) ?></aside>
                            <?php endif ?>
                            <?php endforeach ?>

                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($this['widgets']->count('fullscreen-c')) : ?>
            <div id="tm-fullscreen-c" class="tm-fullscreen-c">
                <?php echo $this['widgets']->render('fullscreen-c'); ?>
            </div>
            <?php endif; ?>

            <?php if ($this['widgets']->count('bottom-a')) : ?>
            <div id="tm-bottom-a" class="tm-block<?php echo $block_classes['bottom-a']; echo $display_classes['bottom-a']; ?>">
                <div>
                    <div class="uk-container uk-container-center">
                        <section class="<?php echo $grid_classes['bottom-a']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('bottom-a', array('layout'=>$this['config']->get('grid.bottom-a.layout'))); ?></section>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($this['widgets']->count('fullscreen-d')) : ?>
            <div id="tm-fullscreen-d" class="tm-fullscreen-d">
                <?php echo $this['widgets']->render('fullscreen-d'); ?>
            </div>
            <?php endif; ?>

            <?php if ($this['widgets']->count('bottom-b')) : ?>
            <div id="tm-bottom-b" class="tm-block<?php echo $block_classes['bottom-b']; echo $display_classes['bottom-b']; ?>">
                <div>
                    <div class="uk-container uk-container-center">
                        <section class="<?php echo $grid_classes['bottom-b']; ?>" data-uk-grid-match="{target:'> div > .uk-panel'}" data-uk-grid-margin><?php echo $this['widgets']->render('bottom-b', array('layout'=>$this['config']->get('grid.bottom-b.layout'))); ?></section>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($this['config']->get('totop_scroller', true)) : ?>
            <div class="tm-totop-scroller-fixed uk-text-center uk-hidden-small"><a data-uk-smooth-scroll href="#"></a></div>
            <?php endif; ?>

            <?php if ($this['widgets']->count('footer-l + footer-r + debug') || $this['config']->get('warp_branding', true) || $this['config']->get('totop_scroller', true)) : ?>
            <footer class="tm-footer">

                <div class="uk-container uk-container-center">
                    <div class="uk-clearfix">

                        <?php if ($this['widgets']->count('footer-l')) : ?>
                        <div class="uk-align-medium-left"><?php echo $this['widgets']->render('footer-l'); ?>
                            <?php
                            echo $this['widgets']->render('footer');
                            $this->output('warp_branding');
                            echo $this['widgets']->render('debug');
                            ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($this['widgets']->count('footer-r')) : ?>
                        <div class="uk-align-medium-right"><?php echo $this['widgets']->render('footer-r'); ?>
                            <?php echo $this['widgets']->render('debug');?>
                        </div>
                        <?php endif; ?>

                        <?php if ($this['config']->get('totop_scroller', true)) : ?>
                        <a class="tm-totop-scroller uk-text-center" data-uk-smooth-scroll href="#"></a>
                        <?php endif; ?>

                    </div>
                </div>

            </footer>
            <?php endif; ?>

        </div>

    <?php echo $this->render('footer'); ?>

    <?php if ($this['widgets']->count('offcanvas')) : ?>
    <div id="offcanvas" class="uk-offcanvas">
        <div class="uk-offcanvas-bar uk-offcanvas-bar-flip"><?php echo $this['widgets']->render('offcanvas'); ?></div>
    </div>
    <?php endif; ?>

    <?php if ($this['widgets']->count('dotnav')) : ?>
        <ul class="uk-dotnav uk-dotnav-vertical tm-dotnav-vertical uk-visible-large" data-uk-scrollspy-nav="{closest: 'li', smoothscroll: {offset: 80}}"><?php echo $this['widgets']->render('dotnav'); ?></ul>
    <?php endif; ?>

</body>
</html>