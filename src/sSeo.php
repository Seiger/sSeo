<?php namespace Seiger\sSeo;
/**
 * Class sSeo - Seiger oollection of SEO Tools for Evolution CMS.
 */

use ReflectionClass;

class sSeo
{
    const TYPE_TEXT = 'Text';
    const TYPE_TEXTAREA = 'Textarea';

    /**
     * Return list of type fields and labels
     *
     * @return array
     */
    public function listType(): array
    {
        $list = [];
        $class = new ReflectionClass(__CLASS__);
        foreach ($class->getConstants() as $constant => $value) {
            if (str_starts_with($constant, 'TYPE_')) {
                $const = strtolower($constant);
                $list[strtolower($value)] = $value;
            }
        }
        return $list;
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
