<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Seiger\sSeo\Livewire\ModulePanel;

final class ModulePanelContractTest extends TestCase
{
    public function test_it_normalizes_active_tab_and_maps_navigation_to_wire_actions(): void
    {
        $panel = new ModulePanel();
        $panel->mount($this->tabs(), 'redirects', ['moduleUrl' => 'index.php?a=123']);

        $this->assertSame('redirects', $panel->activeTab);

        $tabs = $this->callProtected($panel, 'navigationTabs');

        $this->assertCount(3, $tabs);
        $this->assertFalse($tabs[0]['active']);
        $this->assertTrue($tabs[1]['active']);

        foreach ($tabs as $tab) {
            $this->assertSame('wire', $tab['type']);
            $this->assertSame('switchTab', $tab['method']);
            $this->assertSame($tab['key'], $tab['argument']);
            $this->assertArrayNotHasKey('href', $tab);
            $this->assertArrayNotHasKey('data', $tab);
        }
    }

    public function test_it_falls_back_to_first_tab_when_unknown_tab_is_requested(): void
    {
        $panel = new ModulePanel();
        $panel->mount($this->tabs(), 'missing');

        $this->assertSame('dashboard', $panel->activeTab);

        $panel->switchTab('configure');

        $this->assertSame('configure', $panel->activeTab);

        $panel->switchTab('missing');

        $this->assertSame('dashboard', $panel->activeTab);
    }

    public function test_it_maps_configure_tab_to_evo_ui_settings_form_preset(): void
    {
        $panel = new ModulePanel();
        $panel->mount(array_merge($this->tabs(), [
            ['key' => 'configure', 'label' => 'Configure', 'icon' => 'settings'],
        ]), 'configure');

        $this->assertSame('configure', $panel->activeTab);
        $this->assertSame('sseo.settings', $this->callProtected($panel, 'preset'));
    }

    public function test_it_normalizes_legacy_analytics_tab_to_configuration(): void
    {
        $panel = new ModulePanel();
        $panel->mount($this->tabs(), 'analytics');

        $this->assertSame('configure', $panel->activeTab);
        $this->assertSame('sseo.settings', $this->callProtected($panel, 'preset'));
    }

    public function test_shell_and_livewire_view_use_evo_ui_root_and_assets(): void
    {
        $root = dirname(__DIR__, 2);
        $shell = (string) file_get_contents($root . '/views/module/shell.blade.php');
        $panel = (string) file_get_contents($root . '/views/livewire/module-panel.blade.php');
        $routes = (string) file_get_contents($root . '/src/Http/routes.php');
        $controller = (string) file_get_contents($root . '/src/Controllers/sSeoController.php');
        $plugin = (string) file_get_contents($root . '/plugins/sSeoPlugin.php');

        $this->assertStringContainsString("@include('evo::partials.assets')", $shell);
        $this->assertStringNotContainsString("asset('assets/modules/sseo/module.css')", $shell);
        $this->assertStringContainsString('data-evo-ui-root', $shell);
        $this->assertStringNotContainsString('#evo-ui-form-sseo-analytics', $shell);
        $this->assertStringNotContainsString('<style>', $shell);
        $this->assertFileDoesNotExist($root . '/css/module.css');
        $this->assertStringContainsString('<livewire:sseo.module-panel', $shell);
        $this->assertStringContainsString("Route::get('', [sSeoController::class, 'module'])->name('module');", $routes);
        $this->assertStringContainsString('public function module(?string $activeTab = null)', $controller);
        $this->assertStringContainsString('config(\'sseo.module.tabs\', [])', $controller);
        $this->assertStringContainsString("return \$this->moduleTabRedirect('dashboard');", $controller);
        $this->assertStringContainsString("return \$this->moduleTabRedirect('redirects');", $controller);
        $this->assertStringContainsString("return \$this->moduleTabRedirect('robots');", $controller);
        $this->assertStringContainsString("return \$this->moduleTabRedirect('configure');", $controller);
        $this->assertStringContainsString("return \$this->moduleTabRedirect('configure');", $controller);
        $this->assertStringContainsString("sSeo::route('sSeo.module')", $plugin);
        $this->assertStringContainsString('evo-ui-nav-tabs', $panel);
        $this->assertStringContainsString('x-data="{', $panel);
        $this->assertStringContainsString("activeTab: \$wire.entangle('activeTab').live", $panel);
        $this->assertStringContainsString('x-on:click="requestModuleTab(@js($key))"', $panel);
        $this->assertStringContainsString('<div class="tab-content">', $panel);
        $this->assertStringContainsString('data-evo-tab-panel="{{ $activeTab }}"', $panel);
        $this->assertStringContainsString('data-sseo-tab-panel="{{ $activeTab }}"', $panel);
        $this->assertStringContainsString('evo-ui-modal-backdrop', $panel);
        $this->assertStringContainsString("sSeo::module.dashboard", $panel);
        $this->assertStringContainsString("'activity' => \$context['activity'] ?? []", $panel);
        $this->assertStringContainsString('<livewire:evo-ui.form', $panel);
        $this->assertStringContainsString('<livewire:sseo.robots-editor', $panel);
    }

    private function tabs(): array
    {
        return [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'href' => '#legacy'],
            ['key' => 'redirects', 'label' => 'Redirects', 'icon' => 'refresh-cw', 'data' => ['legacy' => true]],
            ['key' => 'configure', 'label' => 'Configure', 'icon' => 'settings'],
        ];
    }

    private function callProtected(object $object, string $method): mixed
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($object);
    }
}
