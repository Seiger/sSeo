<?php namespace Seiger\sSeo;
/**
 * Class sSeo - Seiger oollection of SEO Tools for Evolution CMS.
 */

use Illuminate\Support\Str;
use ReflectionClass;

class sSeo
{
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
        $request_get = array_keys(request()->except('q'));
        if (count($request_get)) {
            $noindex_get = config('seiger.settings.sSeo.noindex_get', []);
            foreach ($noindex_get as $item) {
                if (in_array($item, $request_get)) {
                    $robots = ['show' => true, 'value' => 'noindex,nofollow'];
                }
            }
        }

        return $robots;
    }

    /**
     * Get url from route name
     *
     * @param string $name Route name
     * @return string
     */
    public function route(string $name): string
    {
        $route = rtrim(route($name), '/');
        if (evo()->getConfig('friendly_url_suffix', '') != '/') {
            $route = str_ireplace(evo()->getConfig('friendly_url_suffix', ''), '', route($name));
        }
        return $route;
    }
}
