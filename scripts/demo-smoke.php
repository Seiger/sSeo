#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$extrasRoot = dirname($root);
$demoCore = getenv('SSEO_DEMO_CORE') ?: $extrasRoot . '/sArticles/demo/core';
$database = $demoCore . '/database/database.sqlite';

$checks = [];

try {
    assertFile($demoCore . '/custom/composer.json', 'demo custom composer.json exists');
    assertFile($demoCore . '/vendor/composer/installed.json', 'demo installed package metadata exists');
    assertFile($database, 'demo SQLite database exists');

    $customComposer = readJson($demoCore . '/custom/composer.json');
    $checks[] = ['custom composer requires seiger/sseo', isset($customComposer['require']['seiger/sseo'])];
    $checks[] = ['custom composer has sSeo path repository', hasPathRepository($customComposer, '../../../../sSeo')];

    $installed = readJson($demoCore . '/vendor/composer/installed.json');
    $sseoPackage = installedPackage($installed, 'seiger/sseo');
    $checks[] = ['installed metadata contains seiger/sseo', $sseoPackage !== null];
    $checks[] = ['installed seiger/sseo comes from path repository', str_contains((string) dataGet($sseoPackage, 'dist.url', ''), '../../../../sSeo')];
    $checks[] = ['installed seiger/sseo registers service provider', in_array('Seiger\\sSeo\\sSeoServiceProvider', dataGet($sseoPackage, 'extra.laravel.providers', []), true)];

    $routeList = run($demoCore, [PHP_BINARY, 'artisan', 'route:list']);
    foreach (['sSeo.module', 'sSeo.dashboard', 'sSeo.redirects', 'sSeo.templates', 'sSeo.robots', 'sSeo.analytics', 'sSeo.configure', 'sSeo.modulesave'] as $routeName) {
        $checks[] = ['route is registered: ' . $routeName, str_contains($routeList, $routeName)];
    }

    $migrationStatus = run($demoCore, [PHP_BINARY, 'artisan', 'migrate:status']);
    $checks[] = ['sSeo migration has run', str_contains($migrationStatus, '2024_11_18_094556_create_s_seo_table') && str_contains($migrationStatus, 'Ran')];

    $pdo = new PDO('sqlite:' . $database);
    foreach (['s_seo', 's_redirects'] as $table) {
        $checks[] = ['database table exists: ' . $table, tableExists($pdo, 'evo_' . $table) || tableExists($pdo, $table)];
    }

    foreach ([
        '/config/module/tabs.php',
        '/config/redirects/table.php',
        '/config/settings/form.php',
        '/config/analytics/form.php',
        '/views/module/shell.blade.php',
        '/views/livewire/module-panel.blade.php',
        '/views/livewire/robots-editor.blade.php',
        '/views/livewire/meta-templates-editor.blade.php',
    ] as $path) {
        assertFile($root . $path, 'sSeo evo-ui migration file exists');
    }

    $provider = readText($root . '/src/sSeoServiceProvider.php');
    $plugin = readText($root . '/plugins/sSeoPlugin.php');
    $controller = readText($root . '/src/Controllers/sSeoController.php');
    $redirectsConfig = readText($root . '/config/redirects/table.php');
    $shell = readText($root . '/views/module/shell.blade.php');
    $panel = readText($root . '/views/livewire/module-panel.blade.php');
    $dashboard = readText($root . '/views/module/dashboard.blade.php');
    $robotsEditor = readText($root . '/src/Livewire/RobotsEditor.php');
    $robotsEditorView = readText($root . '/views/livewire/robots-editor.blade.php');
    $resourceFields = readText($root . '/views/partials/fieldsBlock.blade.php');

    foreach ([
        ['provider registers module panel', "Livewire::component('sseo.module-panel'", $provider],
        ['provider registers robots editor', "Livewire::component('sseo.robots-editor'", $provider],
        ['provider registers meta templates editor', "Livewire::component('sseo.meta-templates-editor'", $provider],
        ['provider registers settings form preset', "config/settings/form.php', 'evo-ui.forms.sseo.settings'", $provider],
        ['provider registers analytics form preset', "config/analytics/form.php', 'evo-ui.forms.sseo.analytics'", $provider],
        ['provider registers sSeo server protocol form field', "registerFormField('sseo-server-protocol'", $provider],
        ['manager menu points at evo-ui module route', "sSeo::route('sSeo.module')", $plugin],
        ['manager menu renders Blade Icons Tabler icon', 'svg($icon)', $plugin],
        ['manager menu uses standard Evolution module icon wrapper', '<span class="menu-module-icon" aria-hidden="true">', $plugin],
        ['manager menu limits Blade Icons rendering to Tabler icons', "str_starts_with(\$icon, 'tabler-')", $plugin],
        ['manager menu uses full-size native Tabler SEO icon', "'module_icon' => 'tabler-world-search'", readText($root . '/lang/uk/global.php')],
        ['legacy dashboard route redirects to canonical module URL', "return \$this->moduleTabRedirect('dashboard');", $controller],
        ['legacy redirects route redirects to canonical module URL', "return \$this->moduleTabRedirect('redirects');", $controller],
        ['legacy redirects AJAX create route removed', "name('aredirect')", readText($root . '/src/Http/routes.php'), false],
        ['legacy redirects AJAX delete route removed', "name('dredirect')", readText($root . '/src/Http/routes.php'), false],
        ['legacy redirects AJAX controller method removed', 'public function addRedirect()', $controller, false],
        ['legacy redirects row partial render removed', 'partials.redirects.tableRow', $controller, false],
        ['legacy robots route redirects to canonical module URL', "return \$this->moduleTabRedirect('robots');", $controller],
        ['legacy analytics route redirects to Configuration', "return \$this->moduleTabRedirect('configure');", $controller],
        ['legacy configure route redirects to canonical module URL', "return \$this->moduleTabRedirect('configure');", $controller],
        ['legacy templates route redirects to canonical module URL', "return \$this->moduleTabRedirect('templates');", $controller],
        ['module shell loads evo-ui assets', "@include('evo::partials.assets')", $shell],
        ['module shell does not load sSeo scoped module stylesheet', "asset('assets/modules/sseo/module.css')", $shell, false],
        ['provider does not publish sSeo scoped module stylesheet', "public_path('assets/modules/sseo/module.css')", $provider, false],
        ['publish command prunes old sSeo module stylesheet', "public_path('assets/modules/sseo/module.css')", readText($root . '/src/Console/PublishAssets.php')],
        ['resource SEO fields avoid global evo-ui assets', "@include('evo::partials.assets')", $resourceFields, false],
        ['resource SEO fields expose compact manager form marker', 'data-sseo-resource-fields', $resourceFields],
        ['module shell mounts sSeo panel', '<livewire:sseo.module-panel', $shell],
        ['module panel renders redirects table', '<x-evo::table.livewire', $panel],
        ['redirect modal includes field help metadata', "'help' => 'sSeo::global.old_url_help'", $redirectsConfig],
        ['redirect modal includes field hints', "'hint' => 'sSeo::global.old_url_hint'", $redirectsConfig],
        ['redirect modal includes warning notice', "'body' => 'sSeo::global.message_for_large_number_redirects'", $redirectsConfig],
        ['module panel renders evo-ui forms', '<livewire:evo-ui.form', $panel],
        ['module panel renders robots editor', '<livewire:sseo.robots-editor', $panel],
        ['module panel renders templates editor', '<livewire:sseo.meta-templates-editor', $panel],
        ['module panel renders dashboard partial', "sSeo::module.dashboard", $panel],
        ['dashboard uses shared evo-ui dashboard primitive', '<x-evo::dashboard', $dashboard],
        ['dashboard declares half-width sitemap card span', "'span' => 6", $dashboard],
        ['dashboard renders recent activity block', 'data-sseo-dashboard-activity', $dashboard],
        ['dashboard activity uses runtime controller context', "'activity' => \$this->dashboardActivity(\$sitemaps)", $controller],
        ['robots editor initializes CodeMirror', "editor: 'Codemirror'", $robotsEditor],
        ['robots editor falls back to bundled CodeMirror assets', '/assets/plugins/codemirror/', $robotsEditor],
        ['robots editor initializes CodeMirror from Alpine lifecycle', 'loadCodeMirrorAssets()', $robotsEditorView],
        ['robots editor uses shared evo-ui code editor field styling', 'evo-ui-code-editor-field', $robotsEditorView],
        ['robots editor uses named textarea for manager editor', 'name="{{ $editorId }}"', $robotsEditorView],
        ['robots editor renders initial file content before CodeMirror mount', "{{ \$item['content'] ?? '' }}</textarea>", $robotsEditorView],
        ['robots editor refreshes stale CodeMirror registry entries', 'delete window.myCodeMirrors[key]', $robotsEditorView],
        ['robots editor syncs CodeMirror before save', 'window.myCodeMirrors', $robotsEditorView],
    ] as $check) {
        [$label, $needle, $haystack] = $check;
        $expected = $check[3] ?? true;
        $checks[] = [$label, str_contains($haystack, $needle) === $expected];
    }

    foreach ([
        "css/tailwind.css",
        "css/tailwind.min.css",
        "css/tailwind.config.js",
        "css/theme.css",
        "js/main.js",
        "js/tooltip.js",
    ] as $legacyAsset) {
        $checks[] = ['legacy manager asset is not published: ' . $legacyAsset, !str_contains($provider, $legacyAsset)];
        $checks[] = ['legacy manager source asset is removed: ' . $legacyAsset, !is_file($root . '/' . $legacyAsset)];
    }

    $checks[] = ['legacy redirects row partial is removed', !is_file($root . '/views/partials/redirects/tableRow.blade.php')];
    $checks[] = ['sSeo manager module stylesheet source is removed', !is_file($root . '/css/module.css')];
} catch (Throwable $exception) {
    fwrite(STDERR, 'sSeo demo smoke failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

$failed = array_values(array_filter($checks, static fn (array $check): bool => !$check[1]));

foreach ($checks as [$label, $passed]) {
    echo ($passed ? '[OK] ' : '[FAIL] ') . $label . PHP_EOL;
}

if ($failed !== []) {
    exit(1);
}

echo 'sSeo demo smoke OK' . PHP_EOL;

function assertFile(string $path, string $label): void
{
    if (!is_file($path)) {
        throw new RuntimeException($label . ': ' . $path);
    }
}

function readJson(string $path): array
{
    $json = json_decode((string) file_get_contents($path), true);

    if (!is_array($json)) {
        throw new RuntimeException('Invalid JSON: ' . $path);
    }

    return $json;
}

function readText(string $path): string
{
    assertFile($path, 'file exists');

    return (string) file_get_contents($path);
}

function hasPathRepository(array $composer, string $url): bool
{
    foreach (($composer['repositories'] ?? []) as $repository) {
        if (($repository['type'] ?? '') === 'path' && ($repository['url'] ?? '') === $url) {
            return true;
        }
    }

    return false;
}

function installedPackage(array $installed, string $name): ?array
{
    foreach (($installed['packages'] ?? $installed) as $package) {
        if (($package['name'] ?? '') === $name) {
            return $package;
        }
    }

    return null;
}

function dataGet(?array $array, string $path, mixed $default = null): mixed
{
    if ($array === null) {
        return $default;
    }

    $value = $array;
    foreach (explode('.', $path) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function run(string $cwd, array $command): string
{
    $escaped = implode(' ', array_map('escapeshellarg', $command));
    $descriptorSpec = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($escaped, $descriptorSpec, $pipes, $cwd);

    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start command: ' . $escaped);
    }

    $stdout = stream_get_contents($pipes[1]) ?: '';
    $stderr = stream_get_contents($pipes[2]) ?: '';
    fclose($pipes[1]);
    fclose($pipes[2]);

    $code = proc_close($process);
    if ($code !== 0) {
        throw new RuntimeException(trim($stderr . PHP_EOL . $stdout));
    }

    return $stdout;
}

function tableExists(PDO $pdo, string $table): bool
{
    $statement = $pdo->prepare("select name from sqlite_master where type = 'table' and name = :table");
    $statement->execute(['table' => $table]);

    return (bool) $statement->fetchColumn();
}
