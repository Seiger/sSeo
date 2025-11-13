<?php
/**
 * Plugin for Seiger SEO Tools to Evolution CMS.
 */

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Seiger\sCommerce\Facades\sCommerce;
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
        if ($requestUri != '/' && evo()->getConfig('friendly_urls', false) && trim(evo()->getConfig('friendly_url_suffix', ''))) {
            $requestUriArr = explode('?', $requestUri);
            $langIndex = '';

            if (evo()->getConfig('check_sLang', false)) {
                $langIndex = '/' . evo()->getLocale() . '/';
            }

            if ($requestUriArr[0] != $langIndex && !str_ends_with($requestUriArr[0], evo()->getConfig('friendly_url_suffix', ''))) {
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
 * Page Not Found logics
 */
Event::listen('evolution.OnPageNotFound', function () {
    if (config('seiger.settings.sSeo.redirects_enabled', 0) == 1) {
        $requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $siteKey = evo()->getConfig('check_sMultisite', false) ? evo()->getConfig('site_key', 'default') : 'all';
        $redirect = sRedirect::whereIn('site_key', [$siteKey, 'all'])->where('old_url', $requestUri)->first();

        if ($redirect) {
            header('Location: ' . $redirect->new_url, true, $redirect->type);
            exit;
        }
    }

    if (evo()->getConfig('check_sMultisite', false)) {
        if (request()->is('robots.txt')) {
            $file = null;
            if (file_exists(EVO_STORAGE_PATH . evo()->getConfig('site_key', 'default') . DIRECTORY_SEPARATOR . 'robots.txt')) {
                $file = EVO_STORAGE_PATH . evo()->getConfig('site_key', 'default') . DIRECTORY_SEPARATOR . 'robots.txt';
            } elseif (file_exists(EVO_BASE_PATH . 'robots.txt')) {
                $file = EVO_BASE_PATH . 'robots.txt';
            }

            if ($file) {
                header('Content-Type: text/plain');
                echo file_get_contents($file);
                exit;
            }
        }

        if (request()->is('sitemap.xml')) {
            $file = null;
            if (file_exists(EVO_STORAGE_PATH . evo()->getConfig('site_key', 'default') . DIRECTORY_SEPARATOR . 'sitemap.xml')) {
                $file = EVO_STORAGE_PATH . evo()->getConfig('site_key', 'default') . DIRECTORY_SEPARATOR . 'sitemap.xml';
            } elseif (file_exists(EVO_BASE_PATH . 'sitemap.xml')) {
                $file = EVO_BASE_PATH . 'sitemap.xml';
            }

            if ($file) {
                header('Content-Type: text/xml');
                echo file_get_contents($file);
                exit;
            }
        }
    }
});

/**
 * Render SEO tags
 */
Event::listen('evolution.OnHeadWebDocumentRender', function() {return '';});
Event::listen('evolution.OnWebPagePrerender', function() {
    sSeo::headInjection();
});

/**
 * Render SEO Fields
 */
Event::listen('evolution.OnRenderSeoFields', function($params) {
    $id = intval($params['id'] ?? 0);
    $type = $params['type'] ?? 'document';
    $lang = $params['lang'] ?? 'base';
    evo()->setConfig('lang', $lang);

    if ($id > 0) {
        $fields = sSeoModel::where('resource_id', $id)
            ->where('resource_type', $type)
            ->where('lang', $lang)
            ->first()?->toArray() ?? [];

        $fields['lang'] = $lang;
        return view('sSeo::partials.fieldsBlock', $fields)->render();
    }
});

/**
 * Render SEO Tab
 */
Event::listen('evolution.OnDocFormRender', function($params) {
    if (isset($params['id']) && !empty($params['id']) && !evo()->getConfig('check_sLang', false)) {
        $lang = $params['lang'] ?? 'base';
        $fields = sSeoModel::where('resource_id', $params['id'])
            ->where('resource_type', 'document')
            ->where('domain_key', evo()->getConfig('site_key', 'default'))
            ->where('lang', $lang)
            ->first()?->toArray() ?? [];
        $fields['lang'] = $lang;
        return view('sSeo::resourceTab', $fields)->render();
    }
});
Event::listen('evolution.sCommerceManagerAddTabEvent', function($params) {
    if (!evo()->getConfig('check_sLang', false)) {
        $reflector = new \ReflectionClass('sSeo');
        $result['handler'] = str_replace('Facades/sSeo.php', 'Controllers/modulesSeoTabHandler.php', $reflector->getFileName());
        $result['view'] = '';

        if (isset($params['currentTab']) && $params['currentTab'] == 'content') {
            $result['view'] = sCommerce::tabRender('sseoproduct', 'sSeo::moduleTab', $params['dataInput'] ?? [], __('sSeo::global.title'), __('sSeo::global.icon'), ' ');
        }

        return $result;
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
    sSeo::generateSitemap(intval($params['id'] ?? 0));
});
Event::listen('evolution.sCommerceAfterProductContentSave', function($params) {
    if (isset($params['product']) && $params['product']->id) {
        $lang = $params['content']?->lang ?? 'base';
        $data = array_merge(['resource_id' => $params['product']->id, 'resource_type' => 'product'], request()->input('sseo', []));
        $data['domain_key'] = $data[$lang]['domain_key'] = 'default';
        //$scopes = DB::table('s_product_category')->where('product', $params['product']->id)->whereLike('scope', 'primary_%')->get()?->pluck('scope')?->toArray();

        //if ($scopes && is_array($scopes)&& count($scopes)) {
        //    foreach ($scopes as $scope) {
        //        $data['domain_key'] = $data[$lang]['domain_key'] = str_replace('primary_', '', $scope);
        //        sSeo::updateSeoFields($data);
        //    }
        //} else {
            sSeo::updateSeoFields($data);
        //}
    }
});
Event::listen('evolution.sArticlesAfterContentSave', function($params) {
    $article = $params['article'] ?? null;
    $content = $params['content'] ?? null;
    $articleId = is_object($article) ? ($article->id ?? 0) : (int)$article;

    if ($articleId > 0) {
        $lang = $content?->lang ?? 'base';
        $data = array_merge(['resource_id' => $articleId, 'resource_type' => 'article'], request()->input('sseo', []));
        $data['domain_key'] = $data[$lang]['domain_key'] = 'default';
        sSeo::updateSeoFields($data);
    }
});

/**
 * Add Menu item
 */
Event::listen('evolution.OnManagerMenuPrerender', function($params) {
    $menu['sseo'] = [
        'sseo',
        'tools',
        '<i class="'.__('sSeo::global.icon').'"></i>'.__('sSeo::global.title'),
        sSeo::route('sSeo.dashboard'),
        __('sSeo::global.title'),
        "",
        "",
        "main",
        0,
        7,
    ];

    return serialize(array_merge($params['menu'], $menu));
});
