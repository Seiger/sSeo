<?php
/**
 * Plugin for Seiger SEO Tools to Evolution CMS.
 */

use Illuminate\Support\Facades\Event;
use Seiger\sSeo\Facades\sSeo;

/**
 * Add Menu item
 */
Event::listen('evolution.OnManagerMenuPrerender', function($params) {
    $menu['sseo'] = [
        'sseo',
        'tools',
        '<i class="'.__('sSeo::global.icon').'"></i><span class="menu-item-text">'.__('sSeo::global.title').'</span>',
        sSeo::route('sSeo.index'),
        __('sSeo::global.title'),
        "",
        "",
        "main",
        0,
        6,
    ];

    return serialize(array_merge($params['menu'], $menu));
});
