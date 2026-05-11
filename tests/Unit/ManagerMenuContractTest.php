<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ManagerMenuContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_manager_menu_uses_sseo_title_and_full_size_tabler_icon(): void
    {
        $plugin = $this->read('plugins/sSeoPlugin.php');

        $this->assertStringContainsString("\$title = __('sSeo::global.module_title')", $plugin);
        $this->assertStringContainsString("\$icon = __('sSeo::global.module_icon')", $plugin);
        $this->assertStringContainsString("function_exists('svg')", $plugin);
        $this->assertStringContainsString("str_starts_with(\$icon, 'tabler-')", $plugin);
        $this->assertStringContainsString('svg($icon)', $plugin);
        $this->assertStringContainsString('<span class="menu-module-icon" aria-hidden="true">', $plugin);
        $this->assertStringNotContainsString('sseo-manager-menu-icon', $plugin);
        $this->assertStringNotContainsString('vertical-align', $plugin);
        $this->assertStringNotContainsString('float:none!important', $plugin);
        $this->assertStringNotContainsString('transform:none!important', $plugin);
        $this->assertStringContainsString('$iconHtml . $title', $plugin);
        $this->assertStringNotContainsString("'<i class=\"'.\$icon.'\"></i>'.\$title", $plugin);
        $this->assertStringContainsString("sSeo::route('sSeo.module')", $plugin);

        foreach (['en', 'uk', 'ru'] as $locale) {
            $translations = include $this->root . '/lang/' . $locale . '/global.php';

            $this->assertSame('sSeo', $translations['title']);
            $this->assertSame('sSeo', $translations['module_title']);
            $this->assertSame('tabler-world-search', $translations['icon']);
            $this->assertSame('tabler-world-search', $translations['module_icon']);
            $this->assertNotSame($translations['dashboard'], $translations['title']);
        }
    }

    public function test_legacy_tab_routes_redirect_to_canonical_module_url(): void
    {
        $controller = $this->read('src/Controllers/sSeoController.php');

        foreach (['dashboard', 'redirects', 'templates', 'robots', 'configure'] as $tab) {
            $this->assertStringContainsString("return \$this->moduleTabRedirect('" . $tab . "');", $controller);
        }

        $this->assertStringContainsString('protected function moduleTabRedirect(string $tab)', $controller);
        $this->assertStringContainsString("\$url = sSeo::route('sSeo.module');", $controller);
        $this->assertStringContainsString("\$tab !== 'dashboard'", $controller);
        $this->assertStringContainsString("return redirect()->to(\$url);", $controller);
    }

    public function test_module_shell_syncs_parent_manager_tab_title_to_module_name(): void
    {
        $shell = $this->read('views/module/shell.blade.php');

        $this->assertStringContainsString("\$moduleTitle = __('sSeo::global.module_title')", $shell);
        $this->assertStringContainsString("\$legacyDashboardTitle = __('sSeo::global.dashboard') . ' ' . \$moduleTitle;", $shell);
        $this->assertStringContainsString('const syncManagerTabTitle = () => {', $shell);
        $this->assertStringContainsString('document.title = moduleTitle;', $shell);
        $this->assertStringContainsString('const syncDocument = (doc) => {', $shell);
        $this->assertStringContainsString('doc.title = doc.title.split(legacyDashboardTitle).join(moduleTitle);', $shell);
        $this->assertStringContainsString('for (let level = 0; level < 5; level += 1)', $shell);
        $this->assertStringContainsString('new MutationObserver(syncManagerTabTitle)', $shell);
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }
}
