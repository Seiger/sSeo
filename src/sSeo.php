<?php namespace Seiger\sSeo;

use Carbon\Carbon;
use EvolutionCMS\Facades\UrlProcessor;
use EvolutionCMS\Models\SiteContent;
use Illuminate\Support\Str;
use Seiger\sArticles\Models\sArticle;
use Seiger\sCommerce\Facades\sCommerce;
use Seiger\sCommerce\Models\sProduct;
use Seiger\sLang\Facades\sLang;
use Seiger\sMultisite\Models\sMultisite;
use Seiger\sSeo\Controllers\sSeoController;
use Seiger\sSeo\Models\sSeoModel;
use Seiger\sSeo\Support\FastTagParser;
use Seiger\sSeo\Support\MetaBuilder;
use View;

/**
 * Class sSeo - Seiger collection of SEO Tools for Evolution CMS.
 *
 * This class provides methods for handling SEO-related tasks such as checking robots meta tag,
 * generating sitemap, and getting route URLs.
 */
class sSeo
{
    private $document;

    /**
     * Check and generate the canonical URL for the current page.
     *
     * @return array Returns an array with keys "show" (boolean) and "value" (string)
     */
    public function checkCanonical(): string
    {
        $document = $this->getDocument();
        $canonical = trim($document['canonical_url'] ?? '');

        // canonical
        if (isset(evo()->documentObject['canonical']) && empty($canonical)) {
            if (is_scalar(evo()->documentObject['canonical']) && evo()->documentObject['canonical'] != '') {
                $canonical = strtolower(evo()->documentObject['canonical']);
            } elseif (is_array(evo()->documentObject['canonical']) && isset(evo()->documentObject['canonical'][1]) && evo()->documentObject['canonical'][1] != 'default') {
                $canonical = strtolower(evo()->documentObject['canonical'][1]);
            }
        }

        // tv_canonical
        if (isset(evo()->documentObject['tv_canonical']) && empty($canonical)) {
            if (is_scalar(evo()->documentObject['tv_canonical']) && evo()->documentObject['tv_canonical'] != '') {
                $canonical = strtolower(evo()->documentObject['tv_canonical']);
            } elseif (is_array(evo()->documentObject['tv_canonical']) && isset(evo()->documentObject['tv_canonical'][1]) && evo()->documentObject['tv_canonical'][1] != 'default') {
                $canonical = strtolower(evo()->documentObject['tv_canonical'][1]);
            }
        }

        // For Product or any custom type document
        if (isset(evo()->documentObject['link']) && empty($canonical)) {
            $canonical = evo()->documentObject['link'];
        }

        // Paginate
        $paginates_get = config('seiger.settings.sSeo.paginates_get', 'page');
        if (
            empty($canonical) ||
            in_array($paginates_get, request()->segments()) ||
            in_array($paginates_get, array_keys(request()->except('q')))
        ) {
            $canonical = UrlProcessor::makeUrl((int)$document['id']);
        }

        if (evo()->isBackend() && str_starts_with($canonical, 'http')) {
            $canonical = explode('/', $canonical);

            evo()->setConfig('site_url', implode('/', [$canonical[0], $canonical[1], $canonical[2]]) . '/');

            unset($canonical[0], $canonical[1], $canonical[2]);
            $canonical = '/' . implode('/', $canonical);
        }

        if (evo()->getConfig('check_sLang', false)) {
            if (
                evo()->getConfig('lang') != 'base' &&
                (evo()->getConfig('lang') != sLang::langDefault() || evo()->getConfig('s_lang_default_show', 0) == 1)
            ) {
                $canonical = str_replace('/' . evo()->getConfig('lang', '') . '/', '/', $canonical);
                $canonical = '/' . evo()->getConfig('lang', '') . '/' . ltrim($canonical, '/');
            }
        }

        if (str_starts_with($canonical, '/')) {
            $canonical = evo()->getConfig('site_url', '') . ltrim($canonical, '/');
        }

        return $canonical;
    }

    /**
     * Check and generate the Meta Title value
     *
     * @return string Returns a title value
     */
    public function checkMetaTitle(): string
    {
        $document = $this->getDocument();

        if (isset($document['meta_title']) && !empty($document['meta_title'])) {
            $title = $document['meta_title'];
        } else {
            $title = $this->parseSource(evo()->getConfig("sseo_meta_title_{$document['resource_type']}_{$document['lang']}", '[*pagetitle*] - [(site_name)]'));
        }

        return trim($title);
    }

    /**
     * Check and generate the Meta Description value
     *
     * @return string Returns a title value
     */
    public function checkMetaDescription(): string
    {
        $document = $this->getDocument();

        if (isset($document['meta_description']) && !empty($document['meta_description'])) {
            $description = $document['meta_description'];
        } else {
            $description = $this->parseSource(evo()->getConfig("sseo_meta_description_{$document['resource_type']}_{$document['lang']}", '[*pagetitle*] - [(site_name)]'));
        }

        return trim($description);
    }

    /**
     * Check and retrieve meta keywords for the current document.
     *
     * This method checks if the `meta_keywords` field is set and not empty for the current document.
     * If available, it returns the value of `meta_keywords`. If the field is empty or not set,
     * it retrieves the default meta keywords from the system configuration based on the document's type.
     * The default value is parsed from a template using the `[pagetitle]` and `[longtitle]` placeholders.
     *
     * @return string The meta keywords for the document, either custom or default, trimmed of whitespace.
     */
    public function checkMetaKeywords(): string
    {
        $document = $this->getDocument();

        if (isset($document['meta_keywords']) && !empty($document['meta_keywords'])) {
            $description = $document['meta_keywords'];
        } else {
            $description = $this->parseSource(evo()->getConfig("sseo_meta_keywords_{$document['resource_type']}_{$document['lang']}", '[*pagetitle*], [*longtitle*]'));;
        }

        $description = trim($description);
        $description = trim($description, ',');
        return trim($description);
    }

    /**
     * Check and generate the Robots settings and return the value to be used
     *
     * @return array Returns an array with keys "show" (boolean) and "value" (string)
     */
    public function checkRobots(): string
    {
        $document = $this->getDocument();
        $robots = ['show' => false, 'value' => 'index,follow'];

        // robots
        if (isset($document['robots']) && !empty($document['robots'])) {
            $robots = ['show' => true, 'value' => strtolower($document['robots'])];
        }

        // Paginate
        $paginates_get = config('seiger.settings.sSeo.paginates_get', 'page');
        if (
            in_array($paginates_get, request()->segments()) ||
            in_array($paginates_get, array_keys(request()->except('q')))
        ) {
            $robots = ['show' => true, 'value' => 'noindex,follow'];
        }

        // seorobots
        if (isset(evo()->documentObject['seorobots'])) {
            if (is_scalar(evo()->documentObject['seorobots']) && evo()->documentObject['seorobots'] != '') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['seorobots'])];
            } elseif (is_array(evo()->documentObject['seorobots']) && isset(evo()->documentObject['seorobots'][1]) && evo()->documentObject['seorobots'][1] != '') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['seorobots'][1])];
            }
        }

        // seo_robots
        if (isset(evo()->documentObject['seo_robots'])) {
            if (is_scalar(evo()->documentObject['seo_robots']) && evo()->documentObject['seo_robots'] != '') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['seo_robots'])];
            } elseif (is_array(evo()->documentObject['seo_robots']) && isset(evo()->documentObject['seo_robots'][1]) && evo()->documentObject['seo_robots'][1] != '') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['seo_robots'][1])];
            }
        }

        // tv_robots
        if (isset(evo()->documentObject['tv_robots'])) {
            if (is_scalar(evo()->documentObject['tv_robots']) && evo()->documentObject['tv_robots'] != '') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['tv_robots'])];
            } elseif (is_array(evo()->documentObject['tv_robots']) && isset(evo()->documentObject['tv_robots'][1]) && evo()->documentObject['tv_robots'][1] != '') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['tv_robots'][1])];
            }
        }

        // $_GET
        $request_get = request()->except('q');
        if (count($request_get)) {
            $noindex_get = config('seiger.settings.sSeo.noindex_get', []);
            foreach ($request_get as $key => $item) {
                if (in_array($key, $noindex_get) || strpos($item, ',') !== false) {
                    $robots = ['show' => true, 'value' => 'noindex,nofollow'];
                }
            }
        }

        return $robots['show'] ? $robots['value'] : '';
    }

    /**
     * Event handler for 'OnWebPagePrerender'.
     *
     * - Skips manager area
     * - Skips non-HTML outputs and empty buffers
     * - Injects meta fragment before the last </head>
     *
     * @return void
     */
    public function headInjection(): void
    {
        // Front-end only
        if (!evo()->isFrontend()) {
            return;
        }

        // Current output snapshot
        $out = evo()->documentOutput ?? '';
        if ($out === '' || stripos($out, '<head') === false || stripos($out, '</head>') === false) {
            return;
        }

        // Cheap JSON guard
        $trim = ltrim($out);
        if (($trim !== '' && ($trim[0] === '{' || $trim[0] === '['))
            && stripos($trim, '<!doctype') === false
            && stripos($trim, '<html') === false) {
            return;
        }

        // Avoid double injection if somehow called twice
        if (strpos($out, 'Meta Tags') !== false) {
            return;
        }

        // Build once per request
        static $built = false, $headHtml = '';
        if (!$built) {
            $meta['title'] = $this->checkMetaTitle();
            $meta['description'] = $this->checkMetaDescription();
            $meta['keywords'] = $this->checkMetaKeywords();
            $meta['robots'] = $this->checkRobots();
            $meta['canonical'] = $this->checkCanonical();

            $headHtml = MetaBuilder::buildHeadHtml($meta, $out);
            $built = true;
        }
        if ($headHtml === '') {
            return;
        }

        // Inject before the last </head>
        $pos = strripos($out, '</head>');
        if ($pos !== false) {
            evo()->documentOutput = substr($out, 0, $pos) . "<!-- Meta Tags -->\n" . $headHtml . substr($out, $pos);
        }
    }

    /**
     * Set or Update SEO fields
     *
     * @param $data
     * @return void
     */
    public function updateSeoFields($data)
    {
        if (is_array($data) && isset($data['resource_id']) && (int)$data['resource_id']) {
            $langs = ['base'];
            $langDefault = 'base';

            if (evo()->getConfig('check_sLang', false)) {
                $langs = sLang::langConfig();
                $langDefault = sLang::langDefault();
            }

            $fields = sSeoModel::describe();

            $items = sSeoModel::where('resource_id', $data['resource_id'])
                ->where('resource_type', $data['resource_type'])
                ->get();

            foreach ($langs as $lang) {
                $request = $data[$lang] ?? null;
                if ($request) {
                    $request['resource_id'] = $data['resource_id'];
                    $request['resource_type'] = $data['resource_type'];
                    $request['lang'] = $lang;

                    if ($lang == $langDefault) {
                        $item = $items->whereIn('lang', [$lang, 'base'])->sort(function ($a, $b) {
                            $la = $a->lang ?? '';
                            $lb = $b->lang ?? '';
                            $wa = ($la === 'base') ? 1 : 0;
                            $wb = ($lb === 'base') ? 1 : 0;
                            return $wa <=> $wb ?: strcmp($la, $lb);
                        })->first();
                    } else {
                        $item = $items->where('lang', $lang)->first();
                    }

                    if (!$item) {
                        $item = new sSeoModel();
                    }

                    foreach ($fields as $field) {
                        if (in_array($field['name'], ['seoid'])) {
                            continue;
                        }
                        if (isset($request[$field['name']])) {
                            switch ($field['type']) {
                                case 'int':
                                    $item->{$field['name']} = (int)$request[$field['name']];
                                    break;
                                default:
                                    $item->{$field['name']} = (string)$request[$field['name']];
                                    break;
                            }
                        }
                    }

                    $item->save();
                }
            }
        }
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
    public function generateSitemap(int $id = 0)
    {
        if (config('seiger.settings.sSeo.generate_sitemap', 0) == 1) {
            if (evo()->getConfig('check_sMultisite', false)) {
                if ($id > 0) {
                    $parents = evo()->getParentIds($id);
                    $root = (int)array_shift($parents);
                    (new sSeoController())->generateMultisiteSitemap($root);
                }
            } else {
                (new sSeoController())->generateSitemap();
            }
        }
    }

    /**
     * Get url from route name with action id
     *
     * @param string $name Route name
     * @return string
     */
    public function route(string $name): string
    {
        // Generate the base route URL and remove trailing slashes
        $route = route($name);

        // Trim friendly URL suffix
        if (!empty(evo()->getConfig('friendly_url_suffix'))) {
            $route = rtrim($route, evo()->getConfig('friendly_url_suffix'));
        }

        // Generate a unique action ID based on the route name
        $a = array_sum(array_map('ord', str_split(__('sSeo::global.title')))) + 999;
        $a = $a < 999 ? $a + 999 : $a;

        return $route . '?a=' . $a;
    }

    /**
     * Retrieves and caches the document data for the current request.
     *
     * This method fetches the document details from the `sSeoModel` based on the
     * current `resource_id` and `resource_type`. If no corresponding entry is found
     * in the database, it falls back to `evo()->documentObject`.
     *
     * The result is cached in `$this->document` to avoid redundant database queries.
     *
     * @return array The document data merged with `evo()->documentObject` if found in `sSeoModel`, otherwise returns `evo()->documentObject` as is.
     */
    private function getDocument()
    {
        $lang = evo()->getConfig('lang', 'base');
        if ($this->document === null || !isset($this->document['lang']) || $this->document['lang'] !== $lang) {
            $document = sSeoModel::where('resource_id', (int)evo()->documentObject['id'])
                ->where('resource_type', evo()->documentObject['type'])
                ->where('lang', $lang)
                ->first()?->toArray();

            if (is_array($document) && count($document)) {
                $this->document = array_merge($document, evo()->documentObject);
            } else {
                $this->document = evo()->documentObject;
            }

            $this->document['lang'] = $lang;

            if (empty($this->document['resource_type'])) {
                $this->document['resource_type'] = evo()->documentObject['type'];
            }

            if (evo()->getConfig('check_sCommerce', false)) {
                if ($this->document['type'] == 'document') {
                    $catalogRoot = (int)sCommerce::config('basic.catalog_root', 0);
                    if ($catalogRoot > 0) {
                        $catPages = array_merge([$catalogRoot], evo()->getChildIds($catalogRoot));
                        if (in_array($this->document['id'], $catPages)) {
                            $this->document['resource_type'] = 'prodcat';
                        }
                    }
                }
            }

            foreach ($this->document as $k => $v) {
                if (str_starts_with($k, $lang . '_')) {
                    unset($this->document[$k]);
                    $this->document[ltrim($k, $lang . '_')] = $v;
                }
            }
        }

        return $this->document;
    }

    /**
     * Parse document source using FastTagParser.
     *
     * This method provides a lightweight alternative to evo()->parseDocumentSource().
     * It builds a context array from the current document and global configuration,
     * wires FastTagParser resolvers to EvoCMS internals (snippets, chunks, links, placeholders),
     * and executes the parser with static caching for improved performance.
     *
     * Key points:
     * - Context is built once per call (documentObject + config).
     * - Resolvers are initialized only once per request (static flag).
     * - Uses FastTagParser::parse() with a limited number of passes (default: 6).
     * - Designed specifically for rendering meta tags faster than the core parser.
     *
     * @param string $source Raw document source containing EVO-like tags.
     * @return string Parsed output string with all tags resolved.
     */
    private function parseSource(string $source): string
    {
        $ctx = array_merge(evo()->allConfig(), $this->document);

        static $wired = false;
        if (!$wired) {
            FastTagParser::setResolvers(
                fn(string $name, array $ctx): ?string => $ctx[$name] ?? null,                  // [+x+] / [(x)]
                fn(string $name, array $ctx): ?string => $ctx[$name] ?? null,                  // [*x*]
                fn(string $name, array $ctx): ?string => evo()->getChunk($name) ?? null,       // {{chunk}}
                fn(string $name, array $params, array $ctx): string => (string)(evo()->runSnippet($name, $params) ?? ''), // [[Snippet]]
                fn(string $ref, array $ctx): string => (string)(\EvolutionCMS\Facades\UrlProcessor::makeUrl($ref) ?? '#'.$ref) // [~id~]
            );
            $wired = true;
        }

        return FastTagParser::parse($source, $ctx, 6);
    }
}
