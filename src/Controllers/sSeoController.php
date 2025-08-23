<?php namespace Seiger\sSeo\Controllers;

use Carbon\Carbon;
use EvolutionCMS\Models\EventLog;
use EvolutionCMS\Models\SiteContent;
use EvolutionCMS\Models\SystemSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Seiger\sArticles\Models\sArticle;
use Seiger\sCommerce\Models\sProduct;
use Seiger\sLang\Facades\sLang;
use Seiger\sMultisite\Models\sMultisite;
use Seiger\sSeo\Facades\sSeo;
use Seiger\sSeo\Models\sRedirect;
use Seiger\sSeo\Support\Sitemaper;
use View;

/**
 * Show tabs with custom system settings
 *
 * @return \Illuminate\View\View
 */
class sSeoController
{
    /**
     * Returns the view for the dashboard page.
     *
     * @return mixed The view for the dashboard page.
     */
    public function dashboard()
    {
        $data = [
            'tabIcon' => '<i data-lucide="layout-dashboard" class="w-6 h-6 text-blue-400 drop-shadow-[0_0_6px_#3b82f6]"></i>',
            'tabName' => __('sSeo::global.dashboard'),
        ];

        $data['sitemaps'] = [];
        if (evo()->getConfig('check_sMultisite', false)) {
            foreach (sMultisite::all() as $domain) {
                $file = EVO_STORAGE_PATH . $domain->key . DIRECTORY_SEPARATOR . 'sitemap.xml';
                $site = trim($domain->site_name) ? $domain->site_name . ' Sitemap' : __('sSeo::global.pages_in_sitemap');
                $pages = 0;
                $time = 0;

                clearstatcache(false, $file);
                if (file_exists($file)) {
                    $time = filemtime($file) ?? 0;
                    $pages = Sitemaper::count($file) ?? 0;
                }

                $data['sitemaps'][] = [
                    'site' => $site,
                    'pages' => $pages,
                    'time' => $time,
                ];
            }
        } else {
            $file = MODX_BASE_PATH . 'sitemap.xml';
            $site = evo()->getConfig('site_name', __('sSeo::global.pages_in_sitemap')) . ' Sitemap';
            $pages = 0;
            $time = 0;

            clearstatcache(false, $file);
            if (file_exists($file)) {
                $time = filemtime($file) ?? 0;
                $pages = Sitemaper::count($file) ?? 0;
            }

            $data['sitemaps'][] = [
                'site' => $site,
                'pages' => $pages,
                'time' => $time,
            ];
        }

        return $this->view('dashboardTab', $data);
    }

    /**
     * Returns the view for the redirects page.
     *
     * @return mixed The view for the redirects page.
     */
    public function redirects()
    {
        $data = [
            'tabIcon' => '<i data-lucide="refresh-cw" class="w-6 h-6 text-blue-400 drop-shadow-[0_0_6px_#3b82f6]"></i>',
            'tabName' => __('sSeo::global.redirects'),
        ];
        Paginator::defaultView('sSeo::partials.pagination');
        $b = request()->get('b', 'old_url');
        $d = request()->get('d', 'asc');
        $s = request()->get('s', '');

        $query = sRedirect::query();
        if (trim($s)) {
            $query->where('site_key', 'like', '%' . $s . '%')
                ->orWhere('old_url', 'like', '%' . $s . '%')
                ->orWhere('new_url', 'like', '%' . $s . '%')
                ->orWhere('type', 'like', '%' . $s . '%');
        }
        $query->orderByNatural($b, $d);
        $data['redirects'] = $query->paginate((int)Cookie::get('sSeoPerPage', 50));

        $data['availableSites'] = collect([]);
        if (evo()->getConfig('check_sMultisite', false)) {
            $data['availableSites'] = sMultisite::all();
        }

        return $this->view('redirectsTab', $data);
    }

    /**
     * Add a redirect to list with new data.
     *
     * This method:
     * - Retrieves the submitted redirects from the request.
     * - Validates the input and returns an error message if no redirects are provided.
     * - Creates a backup of existing redirects if not already backed up for the current day.
     * - Maintains a maximum of 5 recent backup files and removes backups older than 7 days.
     * - Truncates the `sRedirect` table before inserting new redirects.
     * - Prevents duplicate redirects by checking for existing `old_url` entries.
     * - Inserts the validated redirects into the database.
     * - Clears the site cache after updating redirects.
     *
     * @return \Illuminate\Http\RedirectResponse Redirects back with a success or error message.
     */
    public function addRedirect()
    {
        $redirect = request()->only(['old_url', 'new_url', 'redirect_type', 'site_key']);

        if (empty($redirect)) {
            return [
                'success' => false,
                'message' => __('sSeo::global.no_redirects_provided'),
            ];
        }

        $item['site_key'] = $redirect['site_key'] ?? 'all';
        $item['old_url'] = ltrim(trim($redirect['old_url'] ?? ''), '/');
        $item['new_url'] = trim($redirect['new_url'] ?? '');
        $item['type'] = intval($redirect['redirect_type'] ?? 302);

        if (empty($item['old_url']) || empty($item['new_url']) || !in_array($item['type'], [301, 302, 307])) {
            return [
                'success' => false,
                'message' => __('sSeo::global.error_empty_fields'),
            ];
        }

        if (sRedirect::where('old_url', $item['old_url'])->whereIn('site_key', [$item['site_key'], 'all'])->exists()) {
            return [
                'success' => false,
                'message' => __('sSeo::global.redirect_exists', ['uri' => $item['old_url']]),
            ];
        }

        $itemDb = sRedirect::create($item);
        evo()->clearCache('full');
        return [
            'success' => true,
            'message' => __('sSeo::global.success_updated'),
            'html' => view('sSeo::partials.redirects.tableRow', ['item' => $itemDb])->render(),
        ];
    }

    /**
     * Delete a redirect record by ID (AJAX).
     *
     * This method handles an incoming DELETE request and deletes a redirect entry from the database
     * using the ID provided in the JSON request payload. It returns a success or error response
     * depending on the request method and deletion outcome.
     *
     * Expected JSON payload:
     * {
     *     "id": int // ID of the redirect record to delete
     * }
     *
     * @return array{
     *     success: bool,
     *     message: string
     * }
     */
    public function delRedirect()
    {
        if (!request()->isMethod('DELETE')) {
            return [
                'success' => false,
                'message' => __('global.cm_unknown_error'),
            ];
        }

        sRedirect::find(request()->json()->getInt('id'))->delete();

        return [
            'success' => true,
            'message' => __('sSeo::global.redirect_deleted'),
        ];
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
        $data = [
            'tabIcon' => '<i data-lucide="file-text" class="w-6 h-6 text-blue-400 drop-shadow-[0_0_6px_#3b82f6]"></i>',
            'tabName' => __('sSeo::global.templates'),
        ];

        if(evo()->getConfig('sseo_pro', false)) {
            $langs = ['base'];
            $registereds = ['sseo_meta_title_document', 'sseo_meta_description_document', 'sseo_meta_keywords_document'];

            if (evo()->getConfig('check_sCommerce', false)) {
                $regCommPCat = ['sseo_meta_title_prodcat', 'sseo_meta_description_prodcat', 'sseo_meta_keywords_prodcat'];
                $regCommProd = ['sseo_meta_title_product', 'sseo_meta_description_product', 'sseo_meta_keywords_product'];
                $registereds = array_merge($registereds, $regCommPCat, $regCommProd);
            }

            if (evo()->getConfig('check_sLang', false)) {
                $langs = sLang::langConfig();
            }

            $editor = [];
            foreach ($langs as $lang) {
                foreach ($registereds as $registered) {
                    $editor[] = $registered . '_' . $lang;
                }
            }

            $codeEditor = $this->textEditor(implode(',', $editor), '500px', 'Codemirror');

            return $this->view('templatesTab', array_merge($data, compact('data', 'langs', 'editor', 'codeEditor')));
        } else {
            return $this->view('templatesTab', $data);
        }
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
        $data = [
            'tabIcon' => '<i data-lucide="file-terminal" class="w-6 h-6 text-blue-400 drop-shadow-[0_0_6px_#3b82f6]"></i>',
            'tabName' => __('sSeo::global.robots'),
        ];

        $sites = [];
        $editor = [];
        $robots = [];

        if (evo()->getConfig('check_sMultisite', false)) {
            $sMultisite = sMultisite::all();
            if ($sMultisite->isEmpty()) {
                if (file_exists(MODX_BASE_PATH . 'robots.txt')) {
                    $file = MODX_BASE_PATH . 'robots.txt';
                } else {
                    $file = '';
                }
                $editor[] = 'robots';
                $robots['robots'] = $file;
            } else {
                foreach ($sMultisite as $site) {
                    if (file_exists(EVO_STORAGE_PATH . $site->key . DIRECTORY_SEPARATOR . 'robots.txt')) {
                        $file = EVO_STORAGE_PATH . $site->key . DIRECTORY_SEPARATOR . 'robots.txt';
                    } elseif (file_exists(MODX_BASE_PATH . 'robots.txt')) {
                        $file = MODX_BASE_PATH . 'robots.txt';
                    } else {
                        $file = '';
                    }
                    $editor[] = $site->key . '_robots';
                    $sites[$site->key . '_robots'] = $site->site_name;
                    $robots[$site->key . '_robots'] = $file;
                }
            }
        } else {
            if (file_exists(MODX_BASE_PATH . 'robots.txt')) {
                $file = MODX_BASE_PATH . 'robots.txt';
            } else {
                $file = '';
            }
            $editor[] = 'robots';
            $sites['robots'] = evo()->getConfig('site_name', 'Current website');
            $robots['robots'] = $file;
        }

        $codeEditor = $this->textEditor(implode(',', $editor), '500px', 'Codemirror');
        return $this->view('robotsTab', array_merge($data, compact('robots', 'sites', 'editor', 'codeEditor')));
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

                file_put_contents(MODX_BASE_PATH . 'robots.txt', $robots);
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

            file_put_contents(MODX_BASE_PATH . 'robots.txt', $robots);
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
        $data = [
            'tabIcon' => '<i data-lucide="settings" class="w-6 h-6 text-blue-400 drop-shadow-[0_0_6px_#3b82f6]"></i>',
            'tabName' => __('sSeo::global.configure'),
        ];
        return $this->view('configureTab', $data);
    }

    /**
     * Updates the configure file with the new values.
     *
     * @return \Illuminate\Http\RedirectResponse The redirect response to the previous page.
     */
    public function updateConfigure()
    {
        $string = '<?php return [' . "\n";

        $string .= "\t" . '"meta_tags_mode" => "' . request()->get('meta_tags_mode', 'replace') . '",' . "\n";
        $string .= "\t" . '"manage_www" => ' . request()->integer('manage_www') . ',' . "\n";
        $string .= "\t" . '"paginates_get" => "' . request()->get('paginates_get', 'page') . '",' . "\n";

        $noindex_get = explode(',', request()->get('noindex_get', ''));
        $string .= "\t" . '"noindex_get" => [' . "\n";
        foreach ($noindex_get as $item) {
            $string .= "\t\t" . '"' . trim($item) . '",' . "\n";
        }
        $string .= "\t" . '],' . "\n";

        $string .= "\t" . '"redirects_enabled" => ' . request()->integer('redirects_enabled') . ',' . "\n";
        $string .= "\t" . '"generate_sitemap" => ' . request()->integer('generate_sitemap') . ',' . "\n";

        $string .= '];';

        // Save config
        $handle = fopen(EVO_CORE_PATH . 'custom/config/seiger/settings/sSeo.php', "w");
        fwrite($handle, $string);
        fclose($handle);

        evo()->clearCache('full');
        return redirect()->back()->with('success', trans('sSeo::global.success_updated'));
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
                    $siteUrl = $baseUrl . '/' . trim($resource->lang);
                } else {
                    $siteUrl = $baseUrl;
                }

                if ($resource->id == evo()->getConfig('site_start', 1)) {
                    $loc = $siteUrl;
                } else {
                    $loc = $siteUrl . str_replace(MODX_SITE_URL, '/', url($resource->id));
                }

                $loc = $siteUrl . trim(url($resource->id), '.');
                $lastmod = $resource->last_modified ? Carbon::parse($resource->last_modified)->toAtomString() : Carbon::parse($resource->editedon)->toAtomString();
                $changefreq = $resource->changefreq ?? 'always';
                $priority = $resource->priority ?? '0.5';
                $urls[] = compact('loc', 'lastmod', 'changefreq', 'priority');
            }
        }

        // sCommerce Products
        if (evo()->getConfig('check_sCommerce', false)) {
            $products = sProduct::leftJoin('s_seo', function($join) {
                $join->on('s_products.id', '=', 's_seo.resource_id');
                $join->where('s_seo.resource_type', '=', 'product');
            })
                ->where(function($query) {
                    $query->whereNot('exclude_from_sitemap', true)->orWhereNull('exclude_from_sitemap');
                })
                ->active()
                ->get();

            if (!empty($products)) {
                foreach ($products as $product) {
                    if ($isLang && $product->lang != 'base' && ($product->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                        $siteUrl = $baseUrl . '/' . trim($product->lang);
                    } else {
                        $siteUrl = $baseUrl;
                    }

                    $loc = $siteUrl . trim($product->link, '.');
                    $lastmod = $product->last_modified ? Carbon::parse($product->last_modified)->toAtomString() : Carbon::parse($product->updated_at)->toAtomString();
                    $changefreq = $product->changefreq ?? 'always';
                    $priority = $product->priority ?? '0.5';
                    $urls[] = compact('loc', 'lastmod', 'changefreq', 'priority');
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
                    if ($isLang && $publication->lang != 'base' && ($publication->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                        $siteUrl = $baseUrl . '/' . trim($product->lang);
                    } else {
                        $siteUrl = $baseUrl;
                    }

                    $loc = $siteUrl . trim($publication->link, '.');
                    $lastmod = $publication->last_modified ? Carbon::parse($publication->last_modified)->toAtomString() : Carbon::parse($publication->updated_at)->toAtomString();
                    $changefreq = $publication->changefreq ?? 'always';
                    $priority = $publication->priority ?? '0.5';
                    $urls[] = compact('loc', 'lastmod', 'changefreq', 'priority');
                }
            }
        }

        $this->writeSitemap(MODX_BASE_PATH . 'sitemap.xml', $urls);
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
            $domain = sMultisite::whereResource(0)->whereActive(1)->first();
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
        })
            ->whereIn('id', $domainIds)
            ->wherePublished(1)
            ->whereDeleted(0)
            ->get();

        if (!empty($resources)) {
            foreach ($resources as $resource) {
                if ($isLang && $resource->lang != 'base' && ($resource->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                    $siteUrl = $baseUrl . '/' . trim($resource->lang ?? '');
                } else {
                    $siteUrl = $baseUrl;
                }

                if ($resource->id == $domain->site_start) {
                    $loc = $siteUrl;
                } else {
                    $loc = $siteUrl . str_replace(MODX_SITE_URL, '/', url($resource->id));
                }

                $lastmod = $resource->last_modified ? Carbon::parse($resource->last_modified)->toAtomString() : Carbon::parse($resource->editedon)->toAtomString();
                $changefreq = $resource->changefreq ?? 'always';
                $priority = $resource->priority ?? '0.5';
                $urls[] = compact('loc', 'lastmod', 'changefreq', 'priority');
            }
        }

        // sCommerce Products
        if (evo()->getConfig('check_sCommerce', false)) {
            $products = sProduct::leftJoin('s_seo', function($join) {
                $join->on('s_products.id', '=', 's_seo.resource_id');
                $join->where('s_seo.resource_type', '=', 'product');
            })
                ->where(function($q) {
                    $q->whereNot('exclude_from_sitemap', true)->orWhereNull('exclude_from_sitemap');
                })
                ->whereHas('categories', function ($q) use ($domainIds) {
                    $q->whereIn('category', $domainIds);
                })
                ->active()
                ->get();

            if (!empty($products)) {
                foreach ($products as $product) {
                    if ($isLang && $product->lang != 'base' && ($product->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                        $siteUrl = $baseUrl . '/' . trim($product->lang);
                    } else {
                        $siteUrl = $baseUrl;
                    }

                    $loc = $siteUrl . str_replace(MODX_SITE_URL, '/', $product->link);
                    $lastmod = $product->last_modified ? Carbon::parse($product->last_modified)->toAtomString() : Carbon::parse($product->updated_at)->toAtomString();
                    $changefreq = $product->changefreq ?? 'always';
                    $priority = $product->priority ?? '0.5';
                    $urls[] = compact('loc', 'lastmod', 'changefreq', 'priority');
                }
            }
        }

        if (evo()->getConfig('check_sArticles', false)) {
            $publications = sArticle::leftJoin('s_seo', function($join) {
                $join->on('s_articles.id', '=', 's_seo.resource_id');
                $join->where('s_seo.resource_type', '=', 'publication');
            })
                ->where(function($q) {
                    $q->whereNot('exclude_from_sitemap', true)->orWhereNull('exclude_from_sitemap');
                })
                ->whereIn('parent', $domainIds)
                ->get();

            if (!empty($publications)) {
                foreach ($publications as $publication) {
                    if ($isLang && $publication->lang != 'base' && ($publication->lang != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)) {
                        $siteUrl = $baseUrl . '/' . trim($product->lang);
                    } else {
                        $siteUrl = $baseUrl;
                    }

                    $loc = $siteUrl . str_replace(MODX_SITE_URL, '/', $publication->link);
                    $lastmod = $publication->last_modified ? Carbon::parse($publication->last_modified)->toAtomString() : Carbon::parse($publication->updated_at)->toAtomString();
                    $changefreq = $publication->changefreq ?? 'always';
                    $priority = $publication->priority ?? '0.5';
                    $urls[] = compact('loc', 'lastmod', 'changefreq', 'priority');
                }
            }
        }

        if (!is_dir(EVO_STORAGE_PATH . $domain->key)) {
            mkdir(EVO_STORAGE_PATH . $domain->key, octdec(evo()->getConfig('new_folder_permissions', '0777')), true);
            chmod(EVO_STORAGE_PATH . $domain->key, octdec(evo()->getConfig('new_folder_permissions', '0777')));
        }

        $this->writeSitemap(EVO_STORAGE_PATH . $domain->key . DIRECTORY_SEPARATOR . 'sitemap.xml', $urls);
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
