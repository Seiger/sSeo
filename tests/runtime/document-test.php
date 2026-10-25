<?php

/**
 * Support isolated package assertions without a site bootstrap.
 *
 * @since 1.4.0
 */
function evo()
{
    return $GLOBALS['evo'];
}
require __DIR__ . '/bootstrap.php';
$evo = new class
{
    public array $documentObject = ['id' => 7, 'type' => 'document', 'pagetitle' => 'Page'];
    public array $config = ['lang' => 'en', 'site_key' => 'site'];
    /**
     * Expose only the controlled configuration used by this isolated regression.
     *
     * @since 1.4.0
     */
    function getConfig($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
};
$db = new Illuminate\Database\Capsule\Manager();
$db->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
$db->setAsGlobal();
$db->bootEloquent();
$conn = $db->getConnection();
$conn->statement('CREATE TABLE s_seo (seoid INTEGER PRIMARY KEY, resource_id INTEGER, resource_type TEXT, domain_key TEXT, lang TEXT, meta_title TEXT, meta_description TEXT, structured_data TEXT, extra_meta TEXT, created_at TEXT, updated_at TEXT)');
foreach (['en', 'uk', 'fr'] as $lang) {
    $conn->table('s_seo')->insert(['resource_id' => 7, 'resource_type' => 'document', 'domain_key' => 'site', 'lang' => $lang, 'meta_title' => 'Title ' . $lang, 'meta_description' => 'Description ' . $lang, 'structured_data' => '{"@type":"WebPage"}', 'extra_meta' => '[{"name":"robots","content":"index"}]', 'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-02-01 12:00:00']);
}
/**
 * Support isolated package assertions without a site bootstrap.
 *
 * @since 1.4.0
 */
function document($service)
{
    return (new ReflectionMethod($service, 'getDocument'))->invoke($service);
}
/**
 * Support isolated package assertions without a site bootstrap.
 *
 * @since 1.4.0
 */
function check($yes, $name)
{
    if (!$yes) {
        throw new RuntimeException($name);
    }
    echo "OK {$name}\n";
}
$service = new Seiger\sSeo\sSeo();
$conn->enableQueryLog();
$first = document($service);
document($service);
check(count($conn->getQueryLog()) === 1, 'same-locale request cache');
check($first['structured_data'] === ['@type' => 'WebPage'] && $first['extra_meta'] === [['name' => 'robots', 'content' => 'index']], 'both JSON casts');
check(!array_key_exists('created_at', $first) && !array_key_exists('updated_at', $first), 'automatic timestamps omitted');
check(!class_exists(Illuminate\Support\Carbon::class, false) && !class_exists(Carbon\Carbon::class, false), 'metadata load does not initialize Carbon');
$source = file_get_contents(dirname(__DIR__, 2) . '/src/sSeo.php');
$source = str_replace("->makeHidden(['created_at', 'updated_at'])", '', $source);
$source = preg_replace('/class sSeo\b/', 'class LegacySeo', $source, 1);
eval(substr($source, 5));
foreach (['en', 'uk', 'fr', 'en'] as $lang) {
    $evo->config['lang'] = $lang;
    $new = document($service);
    $old = document(new Seiger\sSeo\LegacySeo());
    unset($old['created_at'], $old['updated_at']);
    check($new === $old, 'result equivalence ' . $lang);
}
$evo->documentObject['meta_title'] = 'Page override';
$evo->documentObject['structured_data'] = ['custom' => true];
$evo->documentObject['created_at'] = 'page-owned';
$new = document(new Seiger\sSeo\sSeo());
check($new['meta_title'] === 'Page override' && $new['structured_data'] === ['custom' => true] && $new['created_at'] === 'page-owned', 'documentObject wins including explicit dates');
$evo->documentObject = ['id' => 99, 'type' => 'document', 'pagetitle' => 'No SEO'];
$new = document(new Seiger\sSeo\sSeo());
$old = document(new Seiger\sSeo\LegacySeo());
check($new === $old, 'missing SEO row fallback');
echo "ALL SSEO CHECKS PASSED\n";
