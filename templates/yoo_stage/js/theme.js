/* Copyright (C) YOOtheme GmbH, YOOtheme Proprietary Use License (http://www.yootheme.com/license) */

jQuery(function($) {

    var config = $('html').data('config') || {};

    // Social buttons
    $('article[data-permalink]').socialButtons(config);

    var nav      = $('.tm-nav-wrapper'),
        navitems = nav.find('.uk-navbar-nav > li'),
        logo     = $('a.tm-logo');

    if (navitems.length && logo.length) {
        navitems.eq(Math.floor(navitems.length/2) - 1).after('<li class="tm-nav-logo-centered" data-uk-dropdown>'+logo[0].outerHTML+'</li>');
        logo.parent().remove();
    }

});