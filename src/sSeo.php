<?php namespace Seiger\sSeo;

use Illuminate\Support\Str;
use ReflectionClass;
use View;

/**
 * Class sSeo - Seiger collection of SEO Tools for Evolution CMS.
 *
 * This class provides methods for handling SEO-related tasks such as checking robots meta tag,
 * generating sitemap, and getting route URLs.
 */
class sSeo
{
    /**
     * Check robots settings and return the value to be used
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
     * Generate sitemap file
     *
     * This method generates a sitemap file by rendering the "sitemapTemplate" view and
     * saving the contents to the "sitemap.xml" file in the MODX base path.
     *
     * @return void
     */
    public function generateSitemap()
    {
        $sitemap = View::make('sSeoAssets::sitemapTemplate')->render();
        file_put_contents(MODX_BASE_PATH . "/sitemap.xml", $sitemap);
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

        // Generate a unique action ID based on the route name
        $a = array_sum(array_map('ord', str_split($name))) + 999;
        $a = $a < 999 ? $a + 999 : $a;

        return str_replace(MODX_MANAGER_URL, '/', $route) . '?a=' . $a;
    }
}
