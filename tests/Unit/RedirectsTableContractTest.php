<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Seiger\sSeo\Tables\RedirectsTableData;

final class RedirectsTableContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_redirects_table_config_targets_evo_ui_module_table(): void
    {
        $config = include $this->root . '/config/redirects/table.php';

        $this->assertSame('sseo.redirects', $config['key']);
        $this->assertSame(RedirectsTableData::class, $config['provider']);
        $this->assertContains('table', $config['views']);
        $this->assertContains('list', $config['views']);
        $this->assertSame('old_url', $config['default_sort']);
        $this->assertTrue($config['modal']['enabled']);
        $this->assertSame('after_fields', $config['modal']['notices_position']);
        $this->assertSame('info', $config['modal']['notices'][0]['tone']);
        $this->assertSame(['old_url', 'new_url', 'type', 'site_key'], array_column($config['modal']['fields'], 'name'));
        $this->assertSame('sSeo::global.message_for_large_number_redirects', $config['modal']['notices'][0]['body']);
        $this->assertStringContainsString('openCreateModal', $config['wire_target']);
        $this->assertStringContainsString('deleteConfirmed', $config['wire_target']);

        foreach ($config['modal']['fields'] as $field) {
            $this->assertArrayHasKey('label', $field, $field['name']);
            $this->assertArrayHasKey('help', $field, $field['name']);
            $this->assertArrayHasKey('hint', $field, $field['name']);
        }
    }

    public function test_redirects_provider_exposes_module_table_contract_methods(): void
    {
        $provider = new RedirectsTableData([], [], include $this->root . '/config/redirects/table.php');

        $this->assertSame([
            'old_url' => '',
            'new_url' => '',
            'type' => '301',
            'site_key' => 'all',
        ], $provider->modalDefaults());

        foreach (['total', 'rows', 'filterGroups', 'modalData', 'saveModal', 'deleteName', 'deleteRow', 'modalOptionsForField'] as $method) {
            $this->assertTrue(method_exists($provider, $method), $method);
        }
    }

    public function test_redirect_modal_helper_translations_are_available(): void
    {
        foreach (['en', 'uk', 'ru'] as $locale) {
            $translations = include $this->root . '/lang/' . $locale . '/global.php';

            foreach ([
                'old_url_hint',
                'old_url_placeholder',
                'new_url_hint',
                'new_url_placeholder',
                'redirect_type_hint',
                'site_key_help',
                'site_key_hint',
                'message_for_large_number_redirects',
            ] as $key) {
                $this->assertArrayHasKey($key, $translations);
            }
        }
    }

    public function test_module_panel_renders_redirects_through_evo_ui_table_preset(): void
    {
        $component = (string) file_get_contents($this->root . '/src/Livewire/ModulePanel.php');
        $view = (string) file_get_contents($this->root . '/views/livewire/module-panel.blade.php');

        $this->assertStringContainsString("'redirects' => 'sseo.redirects'", $component);
        $this->assertStringContainsString('<x-evo::table.livewire', $view);
        $this->assertStringContainsString('$preset === \'sseo.redirects\'', $view);
        $this->assertStringContainsString(':preset="$preset"', $view);
    }

    public function test_redirects_provider_keeps_duplicate_guard_and_cache_clear_contract(): void
    {
        $provider = (string) file_get_contents($this->root . '/src/Tables/RedirectsTableData.php');

        $this->assertStringContainsString("where('old_url', \$oldUrl)", $provider);
        $this->assertStringContainsString("whereIn('site_key', [\$siteKey, 'all'])", $provider);
        $this->assertStringContainsString('ValidationException::withMessages', $provider);
        $this->assertStringContainsString("__('sSeo::global.redirect_exists'", $provider);
        $this->assertSame(2, substr_count($provider, "evo()->clearCache('full');"));
    }

    public function test_legacy_redirects_ajax_path_is_removed(): void
    {
        $routes = (string) file_get_contents($this->root . '/src/Http/routes.php');
        $controller = (string) file_get_contents($this->root . '/src/Controllers/sSeoController.php');

        $this->assertStringNotContainsString("name('aredirect')", $routes);
        $this->assertStringNotContainsString("name('dredirect')", $routes);
        $this->assertStringNotContainsString('public function addRedirect()', $controller);
        $this->assertStringNotContainsString('public function delRedirect()', $controller);
        $this->assertStringNotContainsString('partials.redirects.tableRow', $controller);
        $this->assertFileDoesNotExist($this->root . '/views/partials/redirects/tableRow.blade.php');
    }

    public function test_sseo_tables_do_not_adopt_dnd_reorder_when_rows_are_not_positioned(): void
    {
        $redirects = include $this->root . '/config/redirects/table.php';
        $activity = include $this->root . '/config/activity/table.php';
        $redirectsProvider = (string) file_get_contents($this->root . '/src/Tables/RedirectsTableData.php');
        $activityProvider = (string) file_get_contents($this->root . '/src/Tables/ActivityTableData.php');

        foreach ([$redirects, $activity] as $config) {
            $this->assertArrayNotHasKey('reorder', $config);
            $this->assertStringNotContainsString('reorder', $config['wire_target']);
            $this->assertNotContains('position', array_column($config['columns'], 'key'));

            foreach (array_merge($config['actions'] ?? [], $config['row_actions'] ?? []) as $action) {
                $this->assertNotContains($action['key'] ?? '', ['move', 'reorder', 'sort']);
                $this->assertNotContains($action['method'] ?? '', ['moveRow', 'reorderRows', 'saveOrder']);
            }
        }

        foreach ([$redirectsProvider, $activityProvider] as $provider) {
            $this->assertStringNotContainsString('function reorder', $provider);
            $this->assertStringNotContainsString('function moveRow', $provider);
            $this->assertStringNotContainsString('saveOrder', $provider);
        }
    }
}
