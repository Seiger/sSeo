<?php
/**
 * Plugin for Seiger SEO Tools to Evolution CMS.
 */

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Seiger\sCommerce\Facades\sCommerce;
use Seiger\sSeo\Facades\sSeo;
use Seiger\sSeo\Models\sRedirect;
use Seiger\sSeo\Models\sSeoModel as SeoModel;

$sseoResourceDefaults = static fn (): array => [
    'robots' => '',
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'canonical_url' => '',
    'exclude_from_sitemap' => false,
    'priority' => '0.5',
    'changefreq' => 'weekly',
];

$sseoResourceDomainKey = static function (mixed $resource, ?string $modelClass = null): string {
    if (!evo()->getConfig('check_sMultisite', false)) {
        return 'default';
    }

    $parent = (int) (is_object($resource) ? ($resource->parent ?? 0) : 0);
    if ($parent <= 0 && is_numeric($resource) && $modelClass && class_exists($modelClass)) {
        $parent = (int) $modelClass::query()->whereKey((int) $resource)->value('parent');
    }

    if ($parent <= 0) {
        return (string) evo()->getConfig('site_key', 'default');
    }

    $multisiteClass = '\Seiger\sMultisite\Models\sMultisite';
    if (!class_exists($multisiteClass)) {
        return (string) evo()->getConfig('site_key', 'default');
    }

    foreach ($multisiteClass::query()->where('active', 1)->get(['key', 'resource']) as $domain) {
        $key = trim((string) ($domain->key ?? ''));
        if ($key === '') {
            continue;
        }

        $siteResources = Cache::get('sMultisite-' . $key . '-resources');
        if (is_array($siteResources) && in_array($parent, array_map('intval', $siteResources), true)) {
            return $key;
        }

        $root = (int) ($domain->resource ?? 0);
        if ($root > 0) {
            $parents = array_map('intval', array_values(evo()->getParentIds($parent, 20)));
            if ($parent === $root || in_array($root, $parents, true)) {
                return $key;
            }
        }
    }

    return 'default';
};

$sseoResourceData = static function (int $resourceId, string $resourceType, string $lang = 'base', ?string $domainKey = null) use ($sseoResourceDefaults): array {
    if ($resourceId <= 0 || trim($resourceType) === '') {
        return $sseoResourceDefaults();
    }

    $domainKey = trim((string) ($domainKey ?: evo()->getConfig('site_key', 'default'))) ?: 'default';
    $rows = SeoModel::query()
        ->where('resource_id', $resourceId)
        ->where('resource_type', $resourceType)
        ->whereIn('domain_key', array_values(array_unique([$domainKey, 'default'])))
        ->where('lang', $lang)
        ->get();

    $row = $rows->firstWhere('domain_key', $domainKey) ?: $rows->firstWhere('domain_key', 'default');
    if (!$row) {
        return $sseoResourceDefaults();
    }

    return array_replace($sseoResourceDefaults(), [
        'robots' => (string) ($row->robots ?? ''),
        'meta_title' => (string) ($row->meta_title ?? ''),
        'meta_description' => (string) ($row->meta_description ?? ''),
        'meta_keywords' => (string) ($row->meta_keywords ?? ''),
        'canonical_url' => (string) ($row->canonical_url ?? ''),
        'exclude_from_sitemap' => (bool) ($row->exclude_from_sitemap ?? false),
        'priority' => number_format((float) ($row->priority ?? 0.5), 1, '.', ''),
        'changefreq' => (string) ($row->changefreq ?? 'weekly'),
    ]);
};

$sseoResourceParseTemplate = static function (string $source, array $context): string {
    $callback = static function (array $matches) use ($context): string {
        $key = (string) collect([$matches[1] ?? '', $matches[2] ?? '', $matches[3] ?? ''])
            ->first(static fn ($value) => (string) $value !== '', '');
        $value = data_get($context, $key, evo()->getConfig($key, ''));

        return is_scalar($value) ? (string) $value : '';
    };

    return trim((string) preg_replace_callback('/\[\*([^\]]+)\*\]|\[\(([^\]]+)\)\]|\[\+([^\]]+)\+\]/', $callback, $source));
};

$sseoResourcePlaceholders = static function (array $params, string $resourceType, string $lang = 'base', ?string $modelClass = null) use ($sseoResourceParseTemplate): array {
    $data = (array) ($params['data'] ?? []);
    $source = (array) data_get($data, 'translations.' . $lang, $data);
    $resource = $params['resource'] ?? $params['article'] ?? null;
    $resourceId = (int) ($params['resourceId'] ?? $params['articleId'] ?? (is_object($resource) ? ($resource->id ?? 0) : 0));

    if ($resourceId > 0 && $modelClass && class_exists($modelClass)) {
        $model = $modelClass::query()->whereKey($resourceId)->first();
        if ($model) {
            $source = array_replace($model->toArray(), $source);
            $source['link'] = (string) ($model->link ?? '');
        }
    }

    $context = array_merge(evo()->allConfig(), $source, [
        'id' => $resourceId,
        'resource_type' => $resourceType,
        'lang' => $lang,
    ]);

    return [
        'meta_title' => $sseoResourceParseTemplate(
            evo()->getConfig("sseo_meta_title_{$resourceType}_{$lang}", '[*pagetitle*] - [(site_name)]'),
            $context
        ),
        'meta_description' => $sseoResourceParseTemplate(
            evo()->getConfig("sseo_meta_description_{$resourceType}_{$lang}", '[*pagetitle*] - [(site_name)]'),
            $context
        ),
        'meta_keywords' => trim($sseoResourceParseTemplate(
            evo()->getConfig("sseo_meta_keywords_{$resourceType}_{$lang}", '[*pagetitle*], [*longtitle*]'),
            $context
        ), ','),
        'canonical_url' => (string) ($context['link'] ?? ''),
    ];
};

$sseoResourceFields = static function (string $prefix = 'seo.', string $tab = 'seo', string $section = '', array $placeholders = []): array {
    return [
        ['name' => $prefix . 'robots', 'type' => 'select', 'label' => 'sSeo::global.robots', 'help' => 'sSeo::global.robots_help', 'tab' => $tab, 'section' => $section, 'span' => 'full', 'options_provider' => 'articleModalOptions', 'rules' => ['nullable', 'string']],
        ['name' => $prefix . 'meta_title', 'type' => 'text', 'label' => 'sSeo::global.meta_title', 'help' => 'sSeo::global.meta_title_help', 'tab' => $tab, 'section' => $section, 'span' => 'full', 'placeholder' => (string) ($placeholders['meta_title'] ?? ''), 'rules' => ['nullable', 'string', 'max:255']],
        ['name' => $prefix . 'meta_description', 'type' => 'textarea', 'label' => 'sSeo::global.meta_description', 'help' => 'sSeo::global.meta_description_help', 'tab' => $tab, 'section' => $section, 'span' => 'full', 'rows' => 3, 'placeholder' => (string) ($placeholders['meta_description'] ?? ''), 'rules' => ['nullable', 'string']],
        ['name' => $prefix . 'meta_keywords', 'type' => 'text', 'label' => 'sSeo::global.meta_keywords', 'help' => 'sSeo::global.meta_keywords_help', 'tab' => $tab, 'section' => $section, 'span' => 'full', 'placeholder' => (string) ($placeholders['meta_keywords'] ?? ''), 'rules' => ['nullable', 'string']],
        ['name' => $prefix . 'canonical_url', 'type' => 'text', 'label' => 'sSeo::global.canonical', 'help' => 'sSeo::global.canonical_help', 'tab' => $tab, 'section' => $section, 'span' => 'full', 'placeholder' => (string) ($placeholders['canonical_url'] ?? ''), 'rules' => ['nullable', 'string', 'max:255']],
        ['name' => $prefix . 'exclude_from_sitemap', 'type' => 'checkbox', 'label' => 'sSeo::global.exclude_from_sitemap', 'help' => 'sSeo::global.exclude_from_sitemap_help', 'tab' => $tab, 'section' => $section, 'rules' => ['boolean']],
        ['name' => $prefix . 'priority', 'type' => 'select', 'label' => 'sSeo::global.priority', 'help' => 'sSeo::global.priority_help', 'tab' => $tab, 'section' => $section, 'options_provider' => 'articleModalOptions', 'rules' => ['nullable', 'string']],
        ['name' => $prefix . 'changefreq', 'type' => 'select', 'label' => 'sSeo::global.change_frequency', 'help' => 'sSeo::global.change_frequency_help', 'tab' => $tab, 'section' => $section, 'options_provider' => 'articleModalOptions', 'rules' => ['nullable', 'string']],
    ];
};

/**
 * Correct url formatting
 */
Event::listen('evolution.OnLoadSettings', function($params) {
    if (!IN_MANAGER_MODE) {
        if (
            (defined('EVO_API_MODE') && EVO_API_MODE)
            || (defined('MODX_API_MODE') && MODX_API_MODE)
        ) {
            return;
        }

        // Skip SEO redirects for API endpoints (we don't want 301/302 canonicalization for APIs).
        // - `SAPI_BASE_PATH` controls sApi base prefix (e.g. "rest")
        // - also exclude the conventional "/api/*" prefix used by other integrations
        $apiBasePath = trim((string)env('SAPI_BASE_PATH', 'api'), '/');
        $skipPrefixes = array_values(array_unique(array_filter([$apiBasePath, 'api'])));
        if ($skipPrefixes !== []) {
            $requestPath = (string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            if ($requestPath !== '' && EVO_BASE_URL !== '' && EVO_BASE_URL !== '/' && str_starts_with($requestPath, EVO_BASE_URL)) {
                $requestPath = trim($requestPath, EVO_BASE_URL);
            }
            $requestPath = trim($requestPath, '/');

            foreach ($skipPrefixes as $prefix) {
                if ($requestPath === $prefix || str_starts_with($requestPath, $prefix . '/')) {
                    return;
                }
            }
        }

        $redirect = false;
        // Check protocol
        $requestProtocol = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? $_SERVER['REQUEST_SCHEME'] ?? '';
        $requestProtocol = trim(explode(',', (string)$requestProtocol)[0] ?? '');
        if ($requestProtocol === '') {
            $requestProtocol = (
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (string)($_SERVER['SERVER_PORT'] ?? '') === '443'
            ) ? 'https' : 'http';
        }

        $url = $requestProtocol;
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

Event::listen('evolution.sArticlesManagerModalTabsEvent', function($params) {
    if (evo()->getConfig('check_sLang', false)) {
        return;
    }

    return [
        'name' => 'seo',
        'label' => 'sSeo::global.title',
        'icon' => 'chart-line',
    ];
});

Event::listen('evolution.sArticlesManagerModalDefaultsEvent', function($params) use ($sseoResourceDefaults) {
    $languages = array_values(array_filter((array) ($params['languages'] ?? [])));
    if ($languages !== []) {
        return [
            'seo' => collect($languages)
                ->mapWithKeys(fn (string $language) => [$language => $sseoResourceDefaults()])
                ->all(),
        ];
    }

    return ['seo' => $sseoResourceDefaults()];
});

Event::listen('evolution.sArticlesManagerModalDataEvent', function($params) use ($sseoResourceData, $sseoResourceDomainKey) {
    $article = $params['article'] ?? null;
    $articleId = (int) ($params['articleId'] ?? (is_object($article) ? ($article->id ?? 0) : 0));
    $domainKey = $sseoResourceDomainKey($article ?: $articleId, '\Seiger\sArticles\Models\sArticle');
    $languages = array_values(array_filter((array) ($params['languages'] ?? [])));

    if ($languages !== []) {
        return [
            'seo' => collect($languages)
                ->mapWithKeys(fn (string $language) => [$language => $sseoResourceData($articleId, 'article', $language, $domainKey)])
                ->all(),
        ];
    }

    return ['seo' => $sseoResourceData($articleId, 'article', 'base', $domainKey)];
});

Event::listen('evolution.sArticlesManagerModalFieldsEvent', function($params) use ($sseoResourceFields, $sseoResourcePlaceholders) {
    if (($params['multilingual'] ?? false) === true) {
        $language = (string) ($params['language'] ?? 'base');

        return $sseoResourceFields(
            (string) ($params['prefix'] ?? 'seo.' . $language . '.'),
            (string) ($params['tab'] ?? ''),
            (string) ($params['section'] ?? 'relations'),
            $sseoResourcePlaceholders($params, 'article', $language, '\Seiger\sArticles\Models\sArticle')
        );
    }

    if (evo()->getConfig('check_sLang', false)) {
        return;
    }

    return $sseoResourceFields('seo.', 'seo', '', $sseoResourcePlaceholders($params, 'article', 'base', '\Seiger\sArticles\Models\sArticle'));
});

Event::listen('evolution.sArticlesManagerModalOptionsEvent', function($params) {
    $name = (string) data_get($params, 'field.name', '');

    if (Str::endsWith($name, '.robots') || $name === 'seo.robots') {
        return [
            ['value' => '', 'label' => '-'],
            ['value' => 'index,follow', 'label' => 'index,follow'],
            ['value' => 'index,nofollow', 'label' => 'index,nofollow'],
            ['value' => 'noindex,nofollow', 'label' => 'noindex,nofollow'],
        ];
    }

    if (Str::endsWith($name, '.priority') || $name === 'seo.priority') {
        return collect(range(10, 1))
            ->map(fn (int $value) => ['value' => number_format($value / 10, 1, '.', ''), 'label' => number_format($value / 10, 1, '.', '')])
            ->all();
    }

    if (Str::endsWith($name, '.changefreq') || $name === 'seo.changefreq') {
        return collect(['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])
            ->map(fn (string $value) => ['value' => $value, 'label' => $value])
            ->all();
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
Event::listen('evolution.sArticlesAfterContentSave', function($params) use ($sseoResourceDomainKey, $sseoResourceDefaults) {
    $article = $params['article'] ?? null;
    $content = $params['content'] ?? null;
    $articleId = is_object($article) ? ($article->id ?? 0) : (int)$article;

    if ($articleId > 0) {
        $lang = $content?->lang ?? 'base';
        $seo = (array) data_get($params, 'data.seo', request()->input('sseo', []));
        $seo = array_replace($sseoResourceDefaults(), (array) data_get($seo, $lang, $seo));
        $domainKey = $sseoResourceDomainKey($article ?: $articleId, '\Seiger\sArticles\Models\sArticle');
        $data = [
            'resource_id' => $articleId,
            'resource_type' => 'article',
            'domain_key' => $domainKey,
            $lang => array_merge($seo, ['domain_key' => $domainKey]),
        ];
        sSeo::updateSeoFields($data);

        if ($domainKey !== 'default') {
            SeoModel::query()
                ->where('resource_id', $articleId)
                ->where('resource_type', 'article')
                ->where('domain_key', 'default')
                ->delete();
        }
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
