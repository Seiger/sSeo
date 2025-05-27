<?php namespace Seiger\sSeo;

use Carbon\Carbon;
use EvolutionCMS\Models\SiteContent;
use Illuminate\Support\Str;
use Seiger\sArticles\Models\sArticle;
use Seiger\sCommerce\Facades\sCommerce;
use Seiger\sCommerce\Models\sProduct;
use Seiger\sMultisite\Models\sMultisite;
use Seiger\sSeo\Controllers\sSeoController;
use Seiger\sSeo\Models\sSeoModel;
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
    public function checkCanonical(): array
    {
        $document = $this->getDocument();
        $canonical = ['show' => false, 'value' => ''];

        // canonical
        if (isset(evo()->documentObject['canonical'])) {
            if (is_scalar(evo()->documentObject['canonical']) && evo()->documentObject['canonical'] != 'default') {
                $canonical = ['show' => true, 'value' => strtolower(evo()->documentObject['canonical'])];
            } elseif (is_array(evo()->documentObject['canonical']) && isset(evo()->documentObject['canonical'][1]) && evo()->documentObject['canonical'][1] != 'default') {
                $canonical = ['show' => true, 'value' => strtolower(evo()->documentObject['canonical'][1])];
            }
        }

        // tv_canonical
        if (isset(evo()->documentObject['tv_canonical'])) {
            if (is_scalar(evo()->documentObject['tv_canonical']) && evo()->documentObject['tv_canonical'] != 'default') {
                $canonical = ['show' => true, 'value' => strtolower(evo()->documentObject['tv_canonical'])];
            } elseif (is_array(evo()->documentObject['tv_canonical']) && isset(evo()->documentObject['tv_canonical'][1]) && evo()->documentObject['tv_canonical'][1] != 'default') {
                $canonical = ['show' => true, 'value' => strtolower(evo()->documentObject['tv_canonical'][1])];
            }
        }

        // Paginate
        $paginates_get = config('seiger.settings.sSeo.paginates_get', 'page');
        if (
            in_array($paginates_get, request()->segments()) ||
            in_array($paginates_get, array_keys(request()->except('q')))
        ) {
            $canonical = ['show' => true, 'value' => url($document['id'], '', '', 'full')];
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
            $title = evo()->parseDocumentSource(evo()->getConfig("sseo_meta_title_{$document['resource_type']}_base", '[*pagetitle*] - [(site_name)]'));
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
            $description = evo()->parseDocumentSource(evo()->getConfig("sseo_meta_description_{$document['resource_type']}_base", '[*pagetitle*] - [(site_name)]'));
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
            $description = evo()->parseDocumentSource(evo()->getConfig("sseo_meta_keywords_{$document['resource_type']}_base", '[*pagetitle*], [*longtitle*]'));;
        }
        return trim($description);
    }

    /**
     * Check and generate the Robots settings and return the value to be used
     *
     * @return array Returns an array with keys "show" (boolean) and "value" (string)
     */
    public function checkRobots(): array
    {
        $robots = ['show' => false, 'value' => 'index,follow'];

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
            if (is_scalar(evo()->documentObject['seorobots']) && evo()->documentObject['seorobots'] != 'default') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['seorobots'])];
            } elseif (is_array(evo()->documentObject['seorobots']) && isset(evo()->documentObject['seorobots'][1]) && evo()->documentObject['seorobots'][1] != 'default') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['seorobots'][1])];
            }
        }

        // seo_robots
        if (isset(evo()->documentObject['seo_robots'])) {
            if (is_scalar(evo()->documentObject['seo_robots']) && evo()->documentObject['seo_robots'] != 'default') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['seo_robots'])];
            } elseif (is_array(evo()->documentObject['seo_robots']) && isset(evo()->documentObject['seo_robots'][1]) && evo()->documentObject['seo_robots'][1] != 'default') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['seo_robots'][1])];
            }
        }

        // tv_robots
        if (isset(evo()->documentObject['tv_robots'])) {
            if (is_scalar(evo()->documentObject['tv_robots']) && evo()->documentObject['tv_robots'] != 'default') {
                $robots = ['show' => true, 'value' => strtolower(evo()->documentObject['tv_robots'])];
            } elseif (is_array(evo()->documentObject['tv_robots']) && isset(evo()->documentObject['tv_robots'][1]) && evo()->documentObject['tv_robots'][1] != 'default') {
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

        return $robots;
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
            $fields = sSeoModel::where('resource_id', $data['resource_id'])
                ->where('resource_type', $data['resource_type'])
                ->firstOrNew();

            foreach ($data as $key => $value) {
                $fields->{$key} = $value;
            }

            $fields->save();
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
        if ($this->document === null) {
            $document = sSeoModel::where('resource_id', evo()->documentObject['id'])
                ->where('resource_type', evo()->documentObject['type'])
                ->first()?->toArray();

            if (is_array($document) && count($document)) {
                $this->document = array_merge($document, evo()->documentObject);
            } else {
                $this->document = evo()->documentObject;
            }

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
        }

        return $this->document;
    }
}
