<?php namespace Seiger\sSeo\Controllers;

use Carbon\Carbon;
use EvolutionCMS\Models\SiteContent;
use Illuminate\Support\Facades\DB;
use Seiger\sArticles\Models\sArticle;
use Seiger\sCommerce\Models\sProduct;
use Seiger\sLang\Facades\sLang;
use Seiger\sMultisite\Models\sMultisite;
use Seiger\sSeo\Facades\sSeo;
use Seiger\sSeo\Models\sRedirect;
use Seiger\sSeo\Models\sSeoModel;
use Seiger\sSeo\Support\AnalyticsIdParser;
use Seiger\sSeo\Support\Sitemaper;
use View;

/**
 * Show tabs with custom system settings
 *
 * @return \Illuminate\View\View
 */
class sSeoController
{
    protected const SETTINGS_FILE = 'custom/config/seiger/settings/sSeo.php';

    public function module(?string $activeTab = null)
    {
        $tabs = collect(config('sseo.module.tabs', []))
            ->filter(function (array $tab): bool {
                $setting = (string) ($tab['setting'] ?? '');
                $cmsSetting = (string) ($tab['cms_setting'] ?? '');

                if ($setting !== '' && (int) config('seiger.settings.sSeo.' . $setting, 0) !== 1) {
                    return false;
                }

                return $cmsSetting === '' || (bool) evo()->getConfig($cmsSetting, false);
            })
            ->map(function (array $tab): array {
                $tab['label'] = __($tab['label'] ?? $tab['key']);

                return $tab;
            })
            ->values()
            ->all();

        $activeTab = $activeTab ?: (string) request()->get('tab', 'dashboard');

        return View::make('sSeo::module.shell', [
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'moduleUrl' => sSeo::route('sSeo.module'),
            'context' => [
                'moduleUrl' => sSeo::route('sSeo.module'),
                'sitemaps' => $sitemaps = $this->dashboardSitemaps(),
                'activity' => $this->dashboardActivity($sitemaps),
            ],
        ]);
    }

    /**
     * Returns the view for the dashboard page.
     *
     * @return mixed The view for the dashboard page.
     */
    public function dashboard()
    {
        return $this->moduleTabRedirect('dashboard');
    }

    protected function dashboardSitemaps(): array
    {
        $sitemaps = [];

        if (evo()->getConfig('check_sMultisite', false)) {
            foreach (sMultisite::all() as $domain) {
                $file = EVO_STORAGE_PATH . $domain->key . DIRECTORY_SEPARATOR . 'sitemap.xml';
                $site = trim($domain->site_name) ? $domain->site_name . ' Sitemap' : __('sSeo::global.pages_in_sitemap');
                $sitemaps[] = $this->sitemapSummary($file, $site);
            }
        } else {
            $file = EVO_BASE_PATH . 'sitemap.xml';
            $site = evo()->getConfig('site_name', __('sSeo::global.pages_in_sitemap')) . ' Sitemap';
            $sitemaps[] = $this->sitemapSummary($file, $site);
        }

        return $sitemaps;
    }

    protected function sitemapSummary(string $file, string $site): array
    {
        clearstatcache(false, $file);

        $exists = file_exists($file);

        return [
            'site' => $site,
            'file' => $file,
            'exists' => $exists,
            'status' => $exists ? 'ready' : 'missing',
            'pages' => $exists ? (Sitemaper::count($file) ?? 0) : 0,
            'time' => $exists ? (filemtime($file) ?? 0) : 0,
        ];
    }

    protected function dashboardActivity(array $sitemaps): array
    {
        $activity = [];

        foreach ($sitemaps as $sitemap) {
            $exists = (bool) ($sitemap['exists'] ?? false);
            $time = (int) ($sitemap['time'] ?? 0);

            $activity[] = [
                'icon' => $exists ? 'list-check' : 'alert-triangle',
                'label' => $exists ? __('sSeo::global.activity_sitemap_ready') : __('sSeo::global.activity_sitemap_missing'),
                'summary' => (string) ($sitemap['site'] ?? __('sSeo::global.pages_in_sitemap')),
                'meta' => $exists && $time > 0 ? date('j M Y H:i', $time) : __('sSeo::global.none'),
                'timestamp' => $time ?: 1,
            ];
        }

        try {
            sRedirect::query()
                ->latest('updated_at')
                ->limit(12)
                ->get()
                ->each(function (sRedirect $redirect) use (&$activity): void {
                    $updatedAt = optional($redirect->updated_at);

                    $activity[] = [
                        'icon' => 'refresh-cw',
                        'label' => __('sSeo::global.activity_redirect_updated'),
                        'summary' => trim((string) $redirect->old_url) . ' -> ' . trim((string) $redirect->new_url),
                        'meta' => $updatedAt->format('j M Y H:i') ?: __('sSeo::global.none'),
                        'timestamp' => $updatedAt->timestamp ?? 1,
                    ];
                });
        } catch (\Throwable) {
            // Dashboard must stay available while migrations are being installed.
        }

        try {
            sSeoModel::query()
                ->latest('updated_at')
                ->limit(12)
                ->get()
                ->each(function (sSeoModel $seo) use (&$activity): void {
                    $updatedAt = optional($seo->updated_at);
                    $resource = trim((string) $seo->resource_type) . ' #' . (int) $seo->resource_id;

                    $activity[] = [
                        'icon' => 'tags',
                        'label' => __('sSeo::global.activity_seo_updated'),
                        'summary' => $resource,
                        'meta' => $updatedAt->format('j M Y H:i') ?: __('sSeo::global.none'),
                        'timestamp' => $updatedAt->timestamp ?? 1,
                    ];
                });
        } catch (\Throwable) {
            // Dashboard must stay available while migrations are being installed.
        }

        usort($activity, static fn (array $left, array $right): int => ($right['timestamp'] ?? 0) <=> ($left['timestamp'] ?? 0));

        return array_slice($activity, 0, 50);
    }

    /**
     * Returns the view for the redirects page.
     *
     * @return mixed The view for the redirects page.
     */
    public function redirects()
    {
        return $this->moduleTabRedirect('redirects');
    }

    /**
     * Prepare the templates for editing and return the view.
     *
     * This method initializes the templates for SEO fields, including the document's meta title, description,
     * and keywords. If the `sCommerce` module is enabled, additional templates for product and category pages
     * are added. The method then prepares a code editor for these templates using the `textEditor` method.
     * Finally, it returns the view with the editor configurations and the templates to be edited.
     *
     * @return \Illuminate\View\View The view for editing SEO templates, including the code editor for the templates.
     */
    public function templates()
    {
        return $this->moduleTabRedirect('templates');
    }

    /**
     * Update SEO templates in the system settings.
     *
     * This method processes the POST request containing the template data. For each template,
     * it checks if the key starts with 'sseo_' (indicating an SEO template setting). It then sanitizes
     * the value, updates the corresponding setting in the database, and also updates the system configuration.
     * After processing the templates, it clears the cache and redirects back with a success message.
     *
     * @return \Illuminate\Http\RedirectResponse The response after updating the templates,
     *         including a redirect with a success message.
     */
    public function updateTemplates()
    {
        $templates = request()->post();

        foreach ($templates as $key => $value) {
            if (str_starts_with($key, 'sseo_')) {
                $value = removeSanitizeSeed($value);
                DB::table('system_settings')->updateOrInsert(
                    ['setting_name' => $key],
                    ['setting_value' => $value]
                );
                evo()->setConfig($key, $value);
            }
        }

        evo()->clearCache('full');
        return redirect()->back()->with('success', __('sSeo::global.success_updated'));
    }

    /**
     * Retrieves the robots.txt file for the current site or multisite setup.
     *
     * This method checks if the `sMultisite` configuration is enabled and retrieves the `robots.txt`
     * file for each site in the multisite setup. If the multisite is not enabled, it checks for the
     * existence of a single `robots.txt` file for the current site. The method also prepares the necessary
     * data for rendering a code editor, specifically `Codemirror`, for editing the robots.txt file(s).
     *
     * - If multisite is enabled, it fetches the corresponding `robots.txt` files for each site.
     * - If multisite is disabled, it retrieves the `robots.txt` file for the current site.
     * - In both cases, the file's path is checked, and if the file exists, it's assigned to the `$robots` variable.
     * - A `Codemirror` editor instance is prepared for editing the robots.txt files.
     *
     * @return \Illuminate\View\View The view with the robots.txt file(s), site data, and code editor instance.
     */
    public function robots()
    {
        return $this->moduleTabRedirect('robots');
    }

    /**
     * Update the content of the robots.txt file.
     *
     * This method handles the update of the robots.txt file by taking the
     * content passed from the request, validating that it is not empty,
     * and then writing it to the `robots.txt` file. If the input is empty,
     * it redirects the user back with an error message. Otherwise, it writes
     * the content to the file and returns a success message.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRobots()
    {
        if (evo()->getConfig('check_sMultisite', false)) {
            $sites = sMultisite::all();
            if ($sites->isEmpty()) {
                $robots = request()->input('robots', '');

                if (empty($robots)) {
                    return redirect()->back()->with('error', trans('sSeo::global.robots_text_empty'));
                }

                file_put_contents(EVO_BASE_PATH . 'robots.txt', $robots);
            } else {
                foreach ($sites as $site) {
                    if (!is_dir(EVO_STORAGE_PATH . $site->key)) {
                        mkdir(EVO_STORAGE_PATH . $site->key, octdec(evo()->getConfig('new_folder_permissions', '0777')), true);
                        chmod(EVO_STORAGE_PATH . $site->key, octdec(evo()->getConfig('new_folder_permissions', '0777')));
                    }

                    $robots = request()->input($site->key . '_robots', '');
                    file_put_contents(EVO_STORAGE_PATH . $site->key . DIRECTORY_SEPARATOR . 'robots.txt', $robots);
                }
            }
        } else {
            $robots = request()->input('robots', '');

            if (empty($robots)) {
                return redirect()->back()->with('error', trans('sSeo::global.robots_text_empty'));
            }

            file_put_contents(EVO_BASE_PATH . 'robots.txt', $robots);
        }

        return redirect()->back()->with('success', trans('sSeo::global.success_updated'));
    }

    /**
     * Returns the view for the configure page.
     *
     * @return mixed The view for the configure page.
     */
    public function configure()
    {
        return $this->moduleTabRedirect('configure');
    }

    /**
     * Returns the view for the analytics page.
     */
    public function analytics()
    {
        return $this->moduleTabRedirect('configure');
    }

    protected function moduleTabRedirect(string $tab)
    {
        $url = sSeo::route('sSeo.module');

        if ($tab !== 'dashboard') {
            $url .= (str_contains($url, '?') ? '&' : '?') . 'tab=' . rawurlencode($tab);
        }

        return redirect()->to($url);
    }

    /**
     * Updates the configure file with the new values.
     *
     * @return \Illuminate\Http\RedirectResponse The redirect response to the previous page.
     */
    public function updateConfigure()
    {
        $noindexGet = array_map('trim', explode(',', (string)request()->get('noindex_get', '')));

        $updates = [
            'meta_tags_mode' => (string)request()->get('meta_tags_mode', 'replace'),
            'manage_www' => (int)request()->integer('manage_www'),
            'paginates_get' => (string)request()->get('paginates_get', 'page'),
            'noindex_get' => $noindexGet,
            'redirects_enabled' => (int)request()->integer('redirects_enabled'),
            'generate_sitemap' => (int)request()->integer('generate_sitemap'),
            'product_attribute_aliases' => array_values(array_filter(array_map('trim', explode(',', (string)request()->get('product_attribute_aliases', ''))))),
        ];

        return $this->saveSettings($updates);
    }

    /**
     * Update analytics settings (GTM + GA4) with strict validation.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAnalytics()
    {
        $updates = [];
        $invalidTokens = [];

        if (evo()->getConfig('check_sMultisite', false)) {
            $sites = sMultisite::all();
            if ($sites->isEmpty()) {
                $rawGtm = (string)request()->get('gtm_container_id', '');
                $rawGa4 = (string)request()->get('ga4_measurement_id', '');

                $gtm = AnalyticsIdParser::parseGtmStrict($rawGtm);
                $ga4 = AnalyticsIdParser::parseGa4Strict($rawGa4);

                $invalidTokens = array_merge($invalidTokens, $gtm['invalid'], $ga4['invalid']);
                $updates['gtm_container_id'] = implode(', ', $gtm['valid']);
                $updates['ga4_measurement_id'] = implode(', ', $ga4['valid']);
            } else {
                foreach ($sites as $site) {
                    $siteKey = (string)$site->key;
                    if ($siteKey === '') continue;

                    $rawGtm = (string)request()->get($siteKey . '_gtm_container_id', '');
                    $rawGa4 = (string)request()->get($siteKey . '_ga4_measurement_id', '');

                    $gtm = AnalyticsIdParser::parseGtmStrict($rawGtm);
                    $ga4 = AnalyticsIdParser::parseGa4Strict($rawGa4);

                    foreach ($gtm['invalid'] as $tok) {
                        $invalidTokens[] = $siteKey . ': ' . $tok;
                    }
                    foreach ($ga4['invalid'] as $tok) {
                        $invalidTokens[] = $siteKey . ': ' . $tok;
                    }

                    $updates[$siteKey . '_gtm_container_id'] = implode(', ', $gtm['valid']);
                    $updates[$siteKey . '_ga4_measurement_id'] = implode(', ', $ga4['valid']);
                }
            }
        } else {
            $rawGtm = (string)request()->get('gtm_container_id', '');
            $rawGa4 = (string)request()->get('ga4_measurement_id', '');

            $gtm = AnalyticsIdParser::parseGtmStrict($rawGtm);
            $ga4 = AnalyticsIdParser::parseGa4Strict($rawGa4);

            $invalidTokens = array_merge($invalidTokens, $gtm['invalid'], $ga4['invalid']);
            $updates['gtm_container_id'] = implode(', ', $gtm['valid']);
            $updates['ga4_measurement_id'] = implode(', ', $ga4['valid']);
        }

        $invalidTokens = array_values(array_unique(array_filter(array_map('trim', $invalidTokens))));
        if (!empty($invalidTokens)) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('sSeo::global.analytics_invalid_ids', ['ids' => implode(', ', $invalidTokens)]));
        }

        return $this->saveSettings($updates);
    }

    /**
     * Persist sSeo settings into the config file (keeps existing keys).
     *
     * @param array<string, mixed> $updates
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function saveSettings(array $updates)
    {
        $path = EVO_CORE_PATH . self::SETTINGS_FILE;

        if (!$this->ensureSettingsDir($path) || !$this->canWriteSettingsFile($path)) {
            return redirect()->back()->with('error', __('sSeo::global.not_writable', ['file' => $path]));
        }

        $current = $this->loadSettingsArray($path);

        $settings = array_merge($current, $updates);

        $preferred = [
            'meta_tags_mode',
            'manage_www',
            'paginates_get',
            'noindex_get',
            'redirects_enabled',
            'generate_sitemap',
            'product_attribute_aliases',
            'gtm_container_id',
            'ga4_measurement_id',
        ];

        $ordered = [];
        foreach ($preferred as $key) {
            if (array_key_exists($key, $settings)) {
                $ordered[$key] = $settings[$key];
                unset($settings[$key]);
            }
        }
        if (!empty($settings)) {
            ksort($settings);
            foreach ($settings as $k => $v) {
                $ordered[$k] = $v;
            }
        }

        file_put_contents($path, $this->dumpSettingsPhp($ordered));

        evo()->clearCache('full');
        return redirect()->back()->with('success', __('sSeo::global.success_updated'));
    }

    protected function ensureSettingsDir(string $path): bool
    {
        $dir = dirname($path);
        if (is_dir($dir)) {
            return true;
        }
        return @mkdir($dir, octdec(evo()->getConfig('new_folder_permissions', '0777')), true) || is_dir($dir);
    }

    protected function canWriteSettingsFile(string $path): bool
    {
        if (is_file($path)) {
            return is_writable($path);
        }

        return is_writable(dirname($path));
    }

    protected function loadSettingsArray(string $path): array
    {
        if (is_file($path)) {
            $arr = require $path;
            return is_array($arr) ? $arr : [];
        }
        return [];
    }

    /**
     * @param array<string, mixed> $settings
     */
    protected function dumpSettingsPhp(array $settings): string
    {
        $out = "<?php return [\n";
        foreach ($settings as $key => $value) {
            $out .= "\t" . $this->dumpString((string)$key) . ' => ' . $this->dumpValue($value, 1) . ",\n";
        }
        $out .= "];\n";
        return $out;
    }

    protected function dumpValue(mixed $value, int $indent): string
    {
        if (is_array($value)) {
            return $this->dumpArray($value, $indent);
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }
        if ($value === null) {
            return 'null';
        }
        return $this->dumpString((string)$value);
    }

    protected function dumpArray(array $value, int $indent): string
    {
        $pad = str_repeat("\t", $indent);
        $padInner = str_repeat("\t", $indent + 1);

        if ($value === []) {
            return '[]';
        }

        $isAssoc = array_keys($value) !== range(0, count($value) - 1);
        $out = "[\n";
        foreach ($value as $k => $v) {
            $out .= $padInner;
            if ($isAssoc) {
                $out .= $this->dumpString((string)$k) . ' => ';
            }
            $out .= $this->dumpValue($v, $indent + 1) . ",\n";
        }
        $out .= $pad . ']';
        return $out;
    }

    protected function dumpString(string $value): string
    {
        $value = str_replace(
            ["\\", "\"", "\r", "\n", "\t"],
            ["\\\\", "\\\"", "\\r", "\\n", "\\t"],
            $value
        );
        return '"' . $value . '"';
    }

    /**
     * Generate sitemap.xml file.
     *
     * This method generates a sitemap file by fetching resources from the Evolution CMS (including
     * site content, sCommerce products, and sArticles publications). The method renders an XML structure
     * and saves the contents to the "sitemap.xml" file in the MODX base path.
     *
     * - It considers settings from `seiger.settings.sSeo.generate_sitemap` to decide whether to generate
     *   the sitemap.
     * - It gathers the URLs from site content, sCommerce products, and sArticles publications, including
     *   their metadata like `lastmod`, `changefreq`, and `priority`.
     * - The sitemap file is saved as "sitemap.xml" in the base path of the MODX site.
     *
     * @return void
     */
    public function generateSitemap()
    {
        $urls = [];
        $isLang = evo()->getConfig('check_sLang', false);
        $baseUrl = trim(evo()->getConfig('site_url', '/'), '/');

        // Evolution CMS Resources
        $resources = SiteContent::leftJoin('s_seo', function($join) {
            $join->on('site_content.id', '=', 's_seo.resource_id');
            $join->where('s_seo.resource_type', '=', 'document');
        })->where(function($query) {
            $query->whereNot('exclude_from_sitemap', true)->orWhereNull('exclude_from_sitemap');
        })
            ->wherePublished(1)
            ->whereDeleted(0)
            ->get();

        if (!empty($resources)) {
            foreach ($resources as $resource) {
                if ($isLang && $resource->lang != 'base' && ($resource->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                    $siteUrl = trim($baseUrl . '/' . trim($resource->lang ?? ''), '/');
                } else {
                    $siteUrl = $baseUrl;
                }

                if ($resource->id == evo()->getConfig('site_start', 1)) {
                    if ($isLang && $resource->lang != 'base' && ($resource->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                        $loc = $siteUrl . '/';
                    } else {
                        $loc = $siteUrl;
                    }
                } else {
                    $loc = $siteUrl . str_replace($baseUrl, '', url($resource->id));
                }

                $lastmod = $resource->last_modified ? Carbon::parse($resource->last_modified)->toAtomString() : Carbon::parse($resource->editedon)->toAtomString();
                $changefreq = $resource->changefreq ?? 'always';
                $priority = $resource->priority ?? '0.5';
                $urls[$loc] = compact('loc', 'lastmod', 'changefreq', 'priority');
            }
        }

        // sCommerce Products
        if (evo()->getConfig('check_sCommerce', false)) {
            $products = sProduct::select('*', 's_seo.lang as lang')
                ->leftJoin('s_seo', function($join) {
                    $join->on('s_products.id', '=', 's_seo.resource_id');
                    $join->where('s_seo.resource_type', '=', 'product');
                })->where(function($query) {
                    $query->whereNot('exclude_from_sitemap', true)->orWhereNull('exclude_from_sitemap');
                })
                ->active()
                ->get();

            if (!empty($products)) {
                foreach ($products as $product) {
                    $productLang = (string)($product->lang ?? '');
                    if ($isLang && $productLang !== '' && $productLang !== 'base' && ($productLang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                        $siteUrl = $baseUrl . '/' . trim($productLang);
                    } else {
                        $siteUrl = $baseUrl;
                    }

                    $productLink = (string)($product->link ?? '');
                    $loc = $siteUrl . trim($productLink, '.');
                    $lastmod = $product->last_modified ? Carbon::parse($product->last_modified)->toAtomString() : Carbon::parse($product->updated_at)->toAtomString();
                    $changefreq = $product->changefreq ?? 'always';
                    $priority = $product->priority ?? '0.5';
                    $urls[$loc] = compact('loc', 'lastmod', 'changefreq', 'priority');
                }
            }
        }

        // sArticles Publications
        if (evo()->getConfig('check_sArticles', false)) {
            $publications = sArticle::leftJoin('s_seo', function($join) {
                $join->on('s_articles.id', '=', 's_seo.resource_id');
                $join->where('s_seo.resource_type', '=', 'publication');
            })
                ->where(function($q) {
                    $q->whereNot('exclude_from_sitemap', true)->orWhereNull('exclude_from_sitemap');
                })
                ->get();

            if (!empty($publications)) {
                foreach ($publications as $publication) {
                    $publicationLang = (string)($publication->lang ?? '');
                    if ($isLang && $publicationLang !== '' && $publicationLang !== 'base' && ($publicationLang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                        $siteUrl = $baseUrl . '/' . trim($publicationLang);
                    } else {
                        $siteUrl = $baseUrl;
                    }

                    $publicationLink = (string)($publication->link ?? '');
                    $loc = $siteUrl . trim($publicationLink, '.');
                    $lastmod = $publication->last_modified ? Carbon::parse($publication->last_modified)->toAtomString() : Carbon::parse($publication->updated_at)->toAtomString();
                    $changefreq = $publication->changefreq ?? 'always';
                    $priority = $publication->priority ?? '0.5';
                    $urls[$loc] = compact('loc', 'lastmod', 'changefreq', 'priority');
                }
            }
        }

        $this->writeSitemap(EVO_BASE_PATH . 'sitemap.xml', array_values($urls));
    }

    /**
     * Generate Multisite sitemap.xml file.
     *
     * This method generates a sitemap file for a multisite setup by fetching resources from the Evolution CMS (including
     * site content, sCommerce products, and sArticles publications). It renders an XML structure and saves the contents
     * to the "sitemap.xml" file in the storage path of the specific site.
     *
     * - It considers settings from `seiger.settings.sSeo.generate_sitemap` to decide whether to generate
     *   the sitemap.
     * - It gathers the URLs from site content, sCommerce products, and sArticles publications, including
     *   their metadata like `lastmod`, `changefreq`, and `priority`.
     * - The sitemap file is saved as "sitemap.xml" in the storage path specific to the domain in a multisite setup.
     *
     * @param int $root The root ID for the specific multisite. If no multisite is found, it defaults to `0`.
     * @return void
     */
    public function generateMultisiteSitemap(int $root = 0)
    {
        $urls = [];
        $isLang = evo()->getConfig('check_sLang', false);

        $domain = sMultisite::whereResource($root)->whereActive(1)->first();
        if (empty($domain)) {
            $domain = sMultisite::where('key', 'default')->first();
        }

        $baseUrl = trim(\Seiger\sMultisite\Facades\sMultisite::scheme(evo()->getConfig('server_protocol', 'https') . '://' . $domain->domain), '/');

        if ($domain->resource == 0) {
            $domainBaseIds = evo()->getChildIds(0, 1);
            $domains = sMultisite::all();
            $multisiteResources = $domains->pluck('resource')->toArray();
            if (count($multisiteResources)) {
                $domainBaseIds = array_diff($domainBaseIds, $multisiteResources);
            }
            $domainIds = $domainBaseIds ?? [];
            foreach ($domainBaseIds as $domainBaseId) {
                $domainIds = array_merge($domainIds, evo()->getChildIds($domainBaseId));
            }
        } else {
            $domainIds = array_merge(evo()->getChildIds($domain->resource));
        }
        if (empty($domainIds)) {
            $domainIds = [0];
        }

        // Evolution CMS Resources
        $resources = SiteContent::leftJoin('s_seo', function($join) {
                $join->on('site_content.id', '=', 's_seo.resource_id');
                $join->where('s_seo.resource_type', '=', 'document');
            })->where(function($query) {
                $query->whereNot('exclude_from_sitemap', true)->orWhereNull('exclude_from_sitemap');
            })->where(function($q) use($domain) {
                $q->where('domain_key', $domain->key)->orWhereNull('domain_key');
            })->whereIn('id', $domainIds)
            ->wherePublished(1)
            ->whereDeleted(0)
            ->get();

        if (!empty($resources)) {
            foreach ($resources as $resource) {
                if ($isLang && $resource->lang != 'base' && ($resource->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                    $siteUrl = trim($baseUrl . '/' . trim($resource->lang ?? ''), '/');
                } else {
                    $siteUrl = $baseUrl;
                }

                if ($resource->id == $domain->site_start) {
                    if ($isLang && $resource->lang != 'base' && ($resource->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                        $loc = $siteUrl . '/';
                    } else {
                        $loc = $siteUrl;
                    }
                } else {
                    $loc = $siteUrl . str_replace($baseUrl, '', url($resource->id));
                }

                $lastmod = $resource->last_modified ? Carbon::parse($resource->last_modified)->toAtomString() : Carbon::parse($resource->editedon)->toAtomString();
                $changefreq = $resource->changefreq ?? 'always';
                $priority = $resource->priority ?? '0.5';
                $urls[$loc] = compact('loc', 'lastmod', 'changefreq', 'priority');
            }
        }

        // sCommerce Products
        if (evo()->getConfig('check_sCommerce', false)) {
            $products = sProduct::select('*', 's_seo.lang as lang')
                ->leftJoin('s_seo', function($join) {
                    $join->on('s_products.id', '=', 's_seo.resource_id');
                    $join->where('s_seo.resource_type', '=', 'product');
                })->where(function($q) {
                    $q->whereNot('exclude_from_sitemap', true)->orWhereNull('exclude_from_sitemap');
                //})->where(function($q) use($domain) {
                //    $q->where('domain_key', $domain->key)->orWhereNull('domain_key');
                })->whereHas('categories', function ($q) use ($domainIds) {
                    $q->whereIn('category', $domainIds);
                })->active()
                ->get();

            if (!empty($products)) {
                foreach ($products as $product) {
                    if ($isLang && $product->lang != 'base' && ($product->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                        $siteUrl = trim($baseUrl . '/' . trim($product->lang ?? ''), '/');
                    } else {
                        $siteUrl = $baseUrl;
                    }

                    $loc = $siteUrl . str_replace($baseUrl, '', $product->link);
                    $lastmod = $product->last_modified ? Carbon::parse($product->last_modified)->toAtomString() : Carbon::parse($product->updated_at)->toAtomString();
                    $changefreq = $product->changefreq ?? 'always';
                    $priority = $product->priority ?? '0.5';
                    $urls[$loc] = compact('loc', 'lastmod', 'changefreq', 'priority');
                }
            }
        }

        // sArticles Publications
        if (evo()->getConfig('check_sArticles', false)) {
            $publications = sArticle::select('*', 's_seo.lang as lang')
                ->leftJoin('s_seo', function($join) {
                    $join->on('s_articles.id', '=', 's_seo.resource_id');
                    $join->where('s_seo.resource_type', '=', 'publication');
                })->where(function($q) {
                    $q->whereNot('exclude_from_sitemap', true)->orWhereNull('exclude_from_sitemap');
                })->whereIn('parent', $domainIds)
                ->get();

            if (!empty($publications)) {
                foreach ($publications as $publication) {
                    if ($isLang && $publication->lang != 'base' && ($publication->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                        $siteUrl = $baseUrl . '/' . trim($product->lang);
                    } else {
                        $siteUrl = $baseUrl;
                    }

                    $loc = $siteUrl . str_replace(EVO_SITE_URL, '/', $publication->link);
                    $lastmod = $publication->last_modified ? Carbon::parse($publication->last_modified)->toAtomString() : Carbon::parse($publication->updated_at)->toAtomString();
                    $changefreq = $publication->changefreq ?? 'always';
                    $priority = $publication->priority ?? '0.5';
                    $urls[$loc] = compact('loc', 'lastmod', 'changefreq', 'priority');
                }
            }
        }

        if (!is_dir(EVO_STORAGE_PATH . $domain->key)) {
            mkdir(EVO_STORAGE_PATH . $domain->key, octdec(evo()->getConfig('new_folder_permissions', '0777')), true);
            chmod(EVO_STORAGE_PATH . $domain->key, octdec(evo()->getConfig('new_folder_permissions', '0777')));
        }

        $this->writeSitemap(EVO_STORAGE_PATH . $domain->key . DIRECTORY_SEPARATOR . 'sitemap.xml', array_values($urls));
    }

    /**
     * Write the sitemap to a file.
     *
     * This method generates an XML structure for the sitemap and writes the content to a file.
     * It loops through all provided URLs and includes their metadata such as `loc`, `lastmod`,
     * `changefreq`, and `priority` in the XML file.
     *
     * @param string $file The path where the sitemap file will be saved.
     * @param array $urls An array of URLs to be included in the sitemap, each containing `loc`, `lastmod`, `changefreq`, and `priority`.
     * @return void
     */
    public function writeSitemap(string $file, array $urls): void
    {
        // Start the XML structure
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        // Loop through each URL and add it to the sitemap
        foreach ($urls as $url) {
            $sitemap .= '    <url>' . PHP_EOL;
            $sitemap .= '        <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
            $sitemap .= '        <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            $sitemap .= '        <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $sitemap .= '        <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
            $sitemap .= '    </url>' . PHP_EOL;
        }

        // Close the XML structure
        $sitemap .= '</urlset>' . PHP_EOL;

        // Write the XML file
        file_put_contents($file, $sitemap);
    }

    /**
     * Updates SEO module fields and redirects back to the previous page.
     *
     * This method retrieves SEO field data from the request input and updates
     * the corresponding fields using `sSeo::updateSeoFields()`. After updating,
     * it redirects the user back to the previous page.
     *
     * @return void
     */
    public function updateModuleFields()
    {
        sSeo::updateSeoFields(request()->input('sseo', []));
        header('Location: ' . htmlspecialchars_decode(back()->getTargetUrl()));
        exit();
    }

    /**
     * Connecting the visual editor to the required fields
     *
     * @param string $ids List of id fields separated by commas
     * @param string $height Window height
     * @param string $editor Which editor to use TinyMCE5, Codemirror
     * @return string
     */
    public function textEditor(string $ids, string $height = '500px', string $editor = ''): string
    {
        $theme = null;
        $elements = [];
        $options = [];
        $ids = explode(",", $ids);

        if (!trim($editor)) {
            $editor = evo()->getConfig('which_editor', 'TinyMCE5');
        }
        if ($editor == 'TinyMCE5') {
            $theme = evo()->getConfig('sart_tinymce5_theme', 'custom');
        }

        foreach ($ids as $id) {
            $elements[] = trim($id);
            if ($theme) {
                $options[trim($id)]['theme'] = $theme;
            }
        }

        return implode("", evo()->invokeEvent('OnRichTextEditorInit', [
            'editor' => $editor,
            'elements' => $elements,
            'height' => $height,
            'contentType' => 'htmlmixed',
            'options' => $options
        ]));
    }

    /**
     * Returns the view for the specified template.
     *
     * @param string $tpl The template name.
     * @param array $data Optional data to be passed to the view.
     * @return mixed The view for the specified template.
     */
    public function view(string $tpl, array $data = [])
    {
        return View::make('sSeo::'.$tpl, $data);
    }
}
