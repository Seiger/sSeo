<?php
/**
 * Plugin for Seiger SEO Tools to Evolution CMS.
 */

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Seiger\sSeo\Facades\sSeo;

/**
 * Correct url formatting
 */
Event::listen('evolution.OnLoadSettings', function($params) {
    if (!IN_MANAGER_MODE) {
        $redirect = false;
        // Check protocol
        $url = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['REQUEST_SCHEME'];
        if ($url != evo()->getConfig('server_protocol')) {
            $redirect = true;
            $url = evo()->getConfig('server_protocol', 'http');
        }
        $url .= '://';

        // Check www
        $domen = $_SERVER['HTTP_HOST'];
        if (config('seiger.settings.sSeo.manage_www', 0) > 0) {
            if (str_starts_with($domen, 'www.')) {
                if (config('seiger.settings.sSeo.manage_www', 0) == 1) {
                    $domen = ltrim($domen, 'www.');
                }
            } else {
                if (config('seiger.settings.sSeo.manage_www', 0) == 2) {
                    $domen = 'www.' . $domen;
                }
            }
        }

        // Check domen
        $url .= $domen;

        // Check request
        $url .= $_SERVER['REQUEST_URI'];

        // Remove index.php
        if (evo()->getConfig('friendly_urls', false) && Str::contains($url, 'index.php')) {
            $redirect = true;
            $url = str_replace('index.php', '', $url);
        }

        if ($redirect) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $url);
            die;
        }
    }
});

Event::listen('evolution.OnHeadWebDocumentRender', function($params) {
    // SEO robots
    $robots = sSeo::checkRobots();

    return view('sSeo::partials.headWebDocument', compact('robots'))->render();
});

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
        7,
    ];

    return serialize(array_merge($params['menu'], $menu));
});
