<?php
/**
 * Plugin for Seiger SEO Tools to Evolution CMS.
 */

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Seiger\sSeo\Facades\sSeo;
use Seiger\sSeo\Models\sRedirect;
use Seiger\sSeo\Models\sSeoModel;

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
                    $redirect = true;
                }
            } else {
                if (config('seiger.settings.sSeo.manage_www', 0) == 2) {
                    $domen = 'www.' . $domen;
                    $redirect = true;
                }
            }
        }

        // Check domen
        $url .= $domen;

        // Check request slashes count
        $requestUri = $_SERVER['REQUEST_URI'];
        if (preg_match("/(\/){2,}/", $requestUri)) {
            $requestUriArr = explode('/', $requestUri);
            $requestUriArr = array_diff($requestUriArr, ['']);
            $requestUri = implode('/', $requestUriArr);
            if (strpos($requestUri,"/")) {
                $requestUri = '/'.$requestUri;
            }
            $redirect = true;
        }

        // Check request path uppercase letters
        if (preg_match_all("/[A-Z]/", $requestUri)) {
            $requestUriArr = explode('?', $requestUri);
            if (preg_match_all("/[A-Z]/", $requestUriArr[0])) {
                $requestUriArr[0] = Str::lower($requestUriArr[0]);
                $requestUri = implode('?', $requestUriArr);
                $redirect = true;
            }
        }

        // Check request end
        if (evo()->getConfig('friendly_urls', false) && trim(evo()->getConfig('friendly_url_suffix', ''))) {
            $requestUriArr = explode('?', $requestUri);
            if (!str_ends_with($requestUriArr[0], evo()->getConfig('friendly_url_suffix', ''))) {
                $requestUriArr[0] = $requestUriArr[0] . evo()->getConfig('friendly_url_suffix', '');
                $requestUri = implode('?', $requestUriArr);
                $redirect = true;
            }
        }

        $url .= $requestUri;

        // Remove index.php
        if (evo()->getConfig('friendly_urls', false) && Str::contains($url, 'index.php')) {
            $redirect = true;
            $url = str_replace('index.php', '', $url);
        }

        // Check redirect
        if ($redirect) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $url);
            die;
        }
    }
});

/**
 * Redirects
 */
Event::listen('evolution.OnPageNotFound', function ($params) {
    $requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $siteKey = evo()->getConfig('check_sMultisite', false) ? evo()->getConfig('site_key', 'default') : 'default';
    $redirect = sRedirect::where('site_key', $siteKey)->where('old_url', $requestUri)->first();

    if ($redirect) {
        evo()->sendRedirect($redirect->new_url, 0, '', $redirect->type);
        exit;
    }
});

Event::listen('evolution.OnHeadWebDocumentRender', function($params) {
    // Meta Canonical
    $canonical = sSeo::checkCanonical();

    // Meta Title
    $title = sSeo::checkMetaTitle();

    // Meta Description
    $description = sSeo::checkMetaDescription();

    // SEO robots
    $robots = sSeo::checkRobots();

    return view('sSeo::partials.headWebDocument', compact('canonical', 'robots', 'title', 'description'))->render();
});

Event::listen('evolution.OnDocFormSave', function($params) {
    sSeo::generateSitemap();
});

/**
 * Add Menu item
 */
Event::listen('evolution.OnManagerMenuPrerender', function($params) {
    $menu['sseo'] = [
        'sseo',
        'tools',
        '<i class="'.__('sSeo::global.icon').'"></i><span class="menu-item-text">'.__('sSeo::global.title').'</span>',
        sSeo::route('sSeo.redirects'),
        __('sSeo::global.title'),
        "",
        "",
        "main",
        0,
        7,
    ];

    return serialize(array_merge($params['menu'], $menu));
});

/**
 * Add SEO Tab or Block
 */
Event::listen('evolution.OnDocFormRender', function($params) {
    if (isset($params['id']) && !empty($params['id'])) {
        $fields = sSeoModel::where('resource_id', $params['id'])
            ->where('resource_type', 'document')
            ->first()?->toArray();
        return view('sSeo::resourceTab', $fields ?? [])->render();
    }
});
Event::listen('evolution.sCommerceFormFieldRender', function($params) {
    if (isset($params['field']) && !empty($params['field']) && $params['field'] == 'seo' && $params['dataInput']['product'] && $params['dataInput']['product']->id) {
        $fields = sSeoModel::where('resource_id', $params['dataInput']['product']->id)
            ->where('resource_type', 'product')
            ->first()?->toArray();
        return view('sSeo::productSection', $fields ?? [])->render();
    }
});

/**
 * Save SEO fields
 */
Event::listen('evolution.OnDocFormSave', function($params) {
    if (isset($params['id']) && !empty($params['id'])) {
        $data = array_merge(['resource_id' => $params['id'], 'resource_type' => 'document'], request()->input('sseo', []));
        sSeo::updateSeoFields($data);
    }
});
Event::listen('evolution.sCommerceAfterProductContentSave', function($params) {
    if (isset($params['product']) && $params['product']->id) {
        $data = array_merge(['resource_id' => $params['product']->id, 'resource_type' => 'product'], request()->input('sseo', []));
        sSeo::updateSeoFields($data);
    }
});
