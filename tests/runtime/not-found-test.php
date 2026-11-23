<?php

/** Exercise the real SEO event listener in isolated processes because it may exit. */
/**
 * Resolve services from the isolated SEO fixture container.
 *
 * @since 1.4.0
 */
function app($name = null)
{
    return $name === null ? $GLOBALS['seoApp'] : $GLOBALS['seoApp']->make($name);
}
/**
 * Read fixture settings without loading site configuration.
 *
 * @since 1.4.0
 */
function config($key, $default = null)
{
    return app('config')->get($key, $default);
}
/**
 * Resolve the HTTP request, failing if the fixture deliberately leaves it unbound.
 *
 * @since 1.4.0
 */
function request()
{
    return app('request');
}
/**
 * Expose only the CMS settings required by the not-found listener.
 *
 * @since 1.4.0
 */
function evo()
{
    return $GLOBALS['seoFixture'];
}

require __DIR__ . '/bootstrap.php';
$mode = $argv[1] ?? null;
if ($mode !== null) {
    $directory = $argv[2];
    define('EVO_STORAGE_PATH', $directory . '/storage/');
    define('EVO_BASE_PATH', $directory . '/public/');
    define('EVO_BASE_URL', '/');
    $GLOBALS['seoApp'] = new Illuminate\Container\Container();
    $container = app();
    $redirects = in_array($mode, ['missing-request-redirects', 'redirect'], true);
    $container->instance('config', new Illuminate\Config\Repository([
        'seiger' => ['settings' => ['sSeo' => ['redirects_enabled' => $redirects]]],
    ]));
    $GLOBALS['seoFixture'] = new class($mode) {
        private string $mode;
        public function __construct(string $mode) { $this->mode = $mode; }
        public function getConfig($key, $default = null) {
            return ['check_sMultisite' => $this->mode !== 'single-site', 'site_key' => $this->mode === 'fallback' ? 'other' : 'test'][$key] ?? $default;
        }
    };
    $dispatcher = new Illuminate\Events\Dispatcher($container);
    $container->instance('events', $dispatcher);
    Illuminate\Support\Facades\Facade::setFacadeApplication($container);
    if (!str_starts_with($mode, 'missing-request')) {
        $paths = [
            'robots' => '/robots.txt', 'fallback' => '/robots.txt',
            'llms' => '/llms.txt', 'sitemap' => '/sitemap.xml',
            'single-site' => '/robots.txt', 'ordinary' => '/missing',
            'redirect' => '/old-tour',
        ];
        $path = $paths[$mode];
        $container->instance('request', Illuminate\Http\Request::create($path));
        $_SERVER['REQUEST_URI'] = $path;
    }
    if ($mode === 'redirect') {
        $capsule = new Illuminate\Database\Capsule\Manager($container);
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $container->instance('db', $capsule->getDatabaseManager());
        $capsule->schema()->create('s_redirects', static function ($table) {
            $table->string('site_key');
            $table->string('old_url');
            $table->string('new_url');
            $table->integer('type');
        });
        $capsule->table('s_redirects')->insert([
            'site_key' => 'test', 'old_url' => 'old-tour', 'new_url' => '/new-tour', 'type' => 307,
        ]);
        register_shutdown_function(static function (): void { echo 'REDIRECT:' . http_response_code(); });
    }
    $pluginRoot = dirname(__DIR__, 2);
    require $pluginRoot . '/plugins/sSeoPlugin.php';
    $dispatcher->dispatch('evolution.OnPageNotFound');
    echo 'CONTINUED';
    exit;
}

$directory = sys_get_temp_dir() . '/seo-not-found-' . bin2hex(random_bytes(8));
mkdir($directory . '/storage/test', 0700, true);
mkdir($directory . '/public', 0700, true);
$fixtures = [
    '/storage/test/robots.txt' => 'SITE ROBOTS',
    '/storage/test/llms.txt' => 'SITE LLMS',
    '/storage/test/sitemap.xml' => '<urlset/>',
    '/public/robots.txt' => 'ROOT ROBOTS',
];
foreach ($fixtures as $file => $body) {
    file_put_contents($directory . $file, $body);
}
try {
    $expected = [
        'missing-request' => 'CONTINUED',
        'missing-request-redirects' => 'CONTINUED',
        'ordinary' => 'CONTINUED',
        'robots' => 'SITE ROBOTS',
        'fallback' => 'ROOT ROBOTS',
        'llms' => 'SITE LLMS',
        'sitemap' => '<urlset/>',
        'single-site' => 'CONTINUED',
        'redirect' => 'REDIRECT:307',
    ];
    foreach ($expected as $case => $body) {
        $process = proc_open([PHP_BINARY, __FILE__, $case, $directory],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $status = proc_close($process);
        if ($status !== 0 || $output !== $body || $error !== '') {
            throw new RuntimeException("SEO case {$case} failed ({$status}): {$output} {$error}");
        }
    }
    echo 'PASS ' . count($expected) . " SEO not-found cases (actual listener, disposable files and SQLite)\n";
} finally {
    foreach ($fixtures as $file => $body) {
        unlink($directory . $file);
    }
    rmdir($directory . '/storage/test');
    rmdir($directory . '/storage');
    rmdir($directory . '/public');
    rmdir($directory);
}
