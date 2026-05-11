<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ProviderWiringContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_composer_declares_evo_ui_dependency_before_manager_rewrite(): void
    {
        $composer = $this->readJson('composer.json');

        $this->assertSame('^1.0', $composer['require']['evolution-cms/evo-ui'] ?? null);
        $this->assertContains('Seiger\\sSeo\\sSeoServiceProvider', $composer['extra']['laravel']['providers'] ?? []);
        $this->assertSame('Seiger\\sSeo\\Facades\\sSeo', $composer['extra']['laravel']['aliases']['sSeo'] ?? null);
    }

    public function test_service_provider_keeps_runtime_bindings_outside_manager_guard(): void
    {
        $provider = $this->read('src/sSeoServiceProvider.php');

        $this->assertStringContainsString('use Livewire\\Livewire;', $provider);
        $this->assertStringContainsString("\$this->mergeConfigFrom(\$this->root . '/config/sSeoSettings.php', 'seiger.settings.sSeo');", $provider);
        $this->assertStringContainsString('$this->app->singleton(sSeo::class);', $provider);
        $this->assertStringContainsString("\$this->app->alias(sSeo::class, 'sSeo');", $provider);
        $this->assertStringContainsString("if (defined('IN_MANAGER_MODE') && IN_MANAGER_MODE) {", $provider);
        $this->assertStringContainsString('protected function bootManager(): void', $provider);

        $runtimeBindingPosition = strpos($provider, '$this->app->singleton(sSeo::class);');
        $managerGuardPosition = strpos($provider, "if (defined('IN_MANAGER_MODE') && IN_MANAGER_MODE) {");

        $this->assertIsInt($runtimeBindingPosition);
        $this->assertIsInt($managerGuardPosition);
        $this->assertLessThan($managerGuardPosition, $runtimeBindingPosition);
    }

    public function test_manager_provider_wiring_registers_evo_ui_surfaces(): void
    {
        $provider = $this->read('src/sSeoServiceProvider.php');

        foreach ([
            "\$this->loadMigrationsFrom(\$this->root . '/database/migrations');",
            "\$this->loadViewsFrom(\$this->root . '/views', 'sSeo');",
            "\$this->loadViewsFrom(evo()->resourcePath('plugins/sseo'), 'sSeoAssets');",
            "\$this->mergeConfigFrom(\$this->root . '/config/sSeoCheck.php', 'cms.settings');",
            "\$this->mergeConfigFrom(\$this->root . '/config/module/tabs.php', 'sseo.module.tabs');",
            "\$this->mergeConfigFrom(\$this->root . '/config/redirects/table.php', 'sseo.redirects.table');",
            "\$this->mergeConfigFrom(\$this->root . '/config/activity/table.php', 'sseo.activity.table');",
            "\$this->mergeConfigFrom(\$this->root . '/config/settings/form.php', 'evo-ui.forms.sseo.settings');",
            "\$this->mergeConfigFrom(\$this->root . '/config/analytics/form.php', 'evo-ui.forms.sseo.analytics');",
            "app(\\EvoUI\\EvoUI::class)->registerFormField('sseo-server-protocol', 'sSeo::components.form.server-protocol');",
            "config()->set('evo-ui.forms.sseo.analytics', \\Seiger\\sSeo\\Support\\AnalyticsSettingsForm::make(config('evo-ui.forms.sseo.analytics', [])));",
            "Livewire::component('sseo.module-panel', \\Seiger\\sSeo\\Livewire\\ModulePanel::class);",
            "Livewire::component('sseo.robots-editor', \\Seiger\\sSeo\\Livewire\\RobotsEditor::class);",
        ] as $expected) {
            $this->assertStringContainsString($expected, $provider);
        }
    }

    public function test_module_tabs_and_settings_form_configs_exist_for_next_slices(): void
    {
        $tabs = include $this->root . '/config/module/tabs.php';
        $form = include $this->root . '/config/settings/form.php';

        $this->assertSame(['dashboard', 'redirects', 'templates', 'robots', 'configure'], array_column($tabs, 'key'));
        $this->assertSame('global.settings_config', $tabs[4]['label']);
        $this->assertSame('sseo-settings', $form['key']);
        $this->assertSame('global.settings_config', $form['title']);
        $this->assertSame('config', $form['variant']);
        $this->assertSame(['analytics', 'indexing', 'commerce'], $form['section_columns'][0]['sections']);
        $this->assertSame(['features', 'server'], $form['section_columns'][1]['sections']);
        $this->assertSame('custom/config/seiger/settings/sSeo.php', $form['source']['file']);
        $this->assertSame('seiger.settings.sSeo', $form['source']['root']);
    }

    public function test_module_panel_component_exists_as_registration_target(): void
    {
        $component = $this->read('src/Livewire/ModulePanel.php');

        $this->assertStringContainsString('namespace Seiger\\sSeo\\Livewire;', $component);
        $this->assertStringContainsString('class ModulePanel extends Component', $component);
        $this->assertStringContainsString("public string \$activeTab = 'dashboard';", $component);
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }

    private function readJson(string $path): array
    {
        $json = json_decode($this->read($path), true);

        $this->assertIsArray($json);

        return $json;
    }
}
