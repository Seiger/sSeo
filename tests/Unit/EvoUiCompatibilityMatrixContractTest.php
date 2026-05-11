<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class EvoUiCompatibilityMatrixContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_module_shell_routes_all_evo_ui_surfaces_through_one_panel(): void
    {
        $component = $this->read('src/Livewire/ModulePanel.php');
        $view = $this->read('views/livewire/module-panel.blade.php');
        $tabs = include $this->root . '/config/module/tabs.php';

        foreach (['dashboard', 'redirects', 'templates', 'robots', 'configure'] as $tab) {
            $this->assertContains($tab, array_column($tabs, 'key'));
        }

        $this->assertNotContains('analytics', array_column($tabs, 'key'));

        foreach ([
            "'redirects' => 'sseo.redirects'",
            "'configure' => 'sseo.settings'",
        ] as $marker) {
            $this->assertStringContainsString($marker, $component);
        }

        foreach ([
            '<x-evo::table.livewire',
            '<livewire:evo-ui.form',
            '<livewire:sseo.meta-templates-editor',
            '<livewire:sseo.robots-editor',
            'window.EvoUI.form.isDirty()',
            'data-evo-form-dirty',
            'x-on:evo-ui:form.saved.window',
            'data-sseo-tab-panel',
        ] as $marker) {
            $this->assertStringContainsString($marker, $view);
        }
    }

    public function test_redirects_table_and_provider_cover_evo_ui_crud_contract(): void
    {
        $config = include $this->root . '/config/redirects/table.php';
        $provider = $this->read('src/Tables/RedirectsTableData.php');

        $this->assertSame('sseo.redirects', $config['key']);
        $this->assertSame(\Seiger\sSeo\Tables\RedirectsTableData::class, $config['provider']);
        $this->assertSame(['table', 'list'], $config['views']);
        $this->assertTrue($config['modal']['enabled']);
        $this->assertTrue($config['modal']['row_dblclick']);
        $this->assertSame(['old_url', 'new_url', 'type', 'site_key'], array_column($config['modal']['fields'], 'name'));
        $this->assertSame(['id', 'old_url', 'new_url', 'type', 'site_key', 'updated_at'], array_column($config['columns'], 'key'));

        foreach ([
            'public function modalDefaults',
            'public function modalData',
            'public function saveModal',
            'public function deleteName',
            'public function deleteRow',
            'public function modalOptionsForField',
            "whereIn('site_key', [\$siteKey, 'all'])",
            'ValidationException::withMessages',
            "evo()->clearCache('full');",
        ] as $marker) {
            $this->assertStringContainsString($marker, $provider);
        }
    }

    public function test_settings_analytics_protocol_and_editor_surfaces_are_covered(): void
    {
        $settings = var_export(include $this->root . '/config/settings/form.php', true);
        $analytics = var_export(include $this->root . '/config/analytics/form.php', true);
        $serverProtocol = $this->read('views/components/form/server-protocol.blade.php');
        $robots = $this->read('src/Livewire/RobotsEditor.php') . "\n" . $this->read('views/livewire/robots-editor.blade.php');
        $templates = $this->read('src/Livewire/MetaTemplatesEditor.php') . "\n" . $this->read('views/livewire/meta-templates-editor.blade.php');

        foreach ([
            "'variant' => 'config'",
            "'type' => 'csv'",
            "'type' => 'sseo-server-protocol'",
            "'save' => false",
            "'name' => 'manage_www'",
            "'name' => 'meta_tags_mode'",
            "'hint' => 'sSeo::global.meta_tags_mode_help'",
            "'hint' => 'sSeo::global.generate_sitemap_help'",
            "'hint_html' => true",
        ] as $marker) {
            $this->assertStringContainsString($marker, $settings);
        }

        foreach ([
            "'key' => 'sseo-analytics'",
            "'name' => 'gtm_container_id'",
            "'name' => 'ga4_measurement_id'",
            "'hint' => 'sSeo::global.gtm_container_id_help'",
            "'hint' => 'sSeo::global.ga4_measurement_id_help'",
            'GTM-[A-Z0-9]+',
            'G-[A-Z0-9]+',
        ] as $marker) {
            $this->assertStringContainsString($marker, $analytics);
        }

        foreach ([
            '<x-evo::badge',
            '_server_protocol',
            'server_protocol',
            '#16A34A',
            'evo-ui-field__hint',
            '$hintText',
        ] as $marker) {
            $this->assertStringContainsString($marker, $serverProtocol);
        }

        foreach ([
            'use EvoUI\\Support\\RichTextEditor;',
            'data-sseo-robots-code',
            'data-sseo-robots-editor-key',
            "{{ \$item['content'] ?? '' }}</textarea>",
            'evo-ui-textarea--code',
            'window.CodeMirror.fromTextArea',
            'delete window.myCodeMirrors[key]',
            'file_put_contents($path, $content)',
        ] as $marker) {
            $this->assertStringContainsString($marker, $robots);
        }

        foreach ([
            "str_starts_with(\$key, 'sseo_')",
            "DB::table('system_settings')->updateOrInsert",
            'data-sseo-meta-templates-editor',
            'evo-ui-form-section',
            'wire:model.blur="{{ $model }}"',
        ] as $marker) {
            $this->assertStringContainsString($marker, $templates);
        }
    }

    public function test_resource_seo_and_legacy_route_contracts_stay_intact(): void
    {
        $resource = $this->read('views/resourceTab.blade.php') . "\n" . $this->read('views/partials/fieldsBlock.blade.php');
        $routes = $this->read('src/Http/routes.php');
        $controller = $this->read('src/Controllers/sSeoController.php');
        $plugin = $this->read('plugins/sSeoPlugin.php');

        foreach ([
            "@include('sSeo::partials.fieldsBlock')",
            'data-sseo-resource-fields',
            'form-control',
            'col-title-11',
            'sseo[{{ $fieldLang }}][meta_title]',
            'sseo[{{ $fieldLang }}][meta_description]',
            'sseo[{{ $fieldLang }}][canonical_url]',
            'sseo[{{ $fieldLang }}][exclude_from_sitemap]',
        ] as $marker) {
            $this->assertStringContainsString($marker, $resource);
        }

        foreach ([
            "->name('sSeo.')",
            "name('module')",
            "name('redirects')",
            "name('templates')",
            "name('robots')",
            "name('analytics')",
            "name('configure')",
            "name('modulesave')",
        ] as $marker) {
            $this->assertStringContainsString($marker, $routes);
        }

        $this->assertStringContainsString('moduleTabRedirect', $controller);
        $this->assertStringContainsString('sSeo::updateSeoFields($data);', $plugin);
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }
}
