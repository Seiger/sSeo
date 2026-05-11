<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LegacyAssetCleanupContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_evo_ui_manager_no_longer_publishes_legacy_manager_assets(): void
    {
        $provider = $this->read('src/sSeoServiceProvider.php');
        $publishCommand = $this->read('src/Console/PublishAssets.php');

        $this->assertStringContainsString('use Seiger\\sSeo\\Console\\PublishAssets;', $provider);
        $this->assertStringContainsString('PublishAssets::class', $provider);
        $this->assertStringContainsString('namespace Seiger\\sSeo\\Console;', $publishCommand);
        $this->assertStringContainsString("'--force' => true", $publishCommand);
        $this->assertStringNotContainsString("public_path('core/vendor/seiger/sseo/config/sSeoCheck.php')", $publishCommand);
        $this->assertStringNotContainsString('InstalledVersions::getVersion', $publishCommand);
        $this->assertStringNotContainsString("'/css/tailwind.min.css'", $provider);
        $this->assertStringNotContainsString("'/js/main.js'", $provider);
        $this->assertStringNotContainsString("'/js/tooltip.js'", $provider);
        $this->assertStringNotContainsString("public_path('assets/site/sseo.min.css') =>", $provider);
        $this->assertStringNotContainsString("public_path('assets/site/sseo.js') =>", $provider);
        $this->assertStringNotContainsString("'/css/module.css'", $provider);
        $this->assertStringNotContainsString("public_path('assets/modules/sseo/module.css')", $provider);
        $this->assertStringContainsString("public_path('assets/modules/sseo/module.css')", $publishCommand);
        $this->assertStringContainsString("public_path('assets/site/seigerit.tooltip.js')", $publishCommand);
        $this->assertStringNotContainsString('Illuminate\\Support\\Facades\\Http', $provider);
        $this->assertStringNotContainsString('sseo_subscription', $provider);
        $this->assertStringNotContainsString('pro_routes.php', $provider);
        $this->assertStringNotContainsString("views/pro", $provider);

        foreach ([
            'css/tailwind.css',
            'css/tailwind.min.css',
            'css/tailwind.config.js',
            'css/theme.css',
            'js/main.js',
            'js/tooltip.js',
        ] as $legacyAsset) {
            $this->assertFileDoesNotExist($this->root . '/' . $legacyAsset, $legacyAsset . ' should not remain in package source.');
        }
    }

    public function test_legacy_manager_get_render_blocks_are_removed_after_evo_ui_delegation(): void
    {
        $controller = $this->read('src/Controllers/sSeoController.php');

        $this->assertStringContainsString("return \$this->moduleTabRedirect('templates');", $controller);
        $this->assertStringContainsString("return \$this->moduleTabRedirect('robots');", $controller);
        $this->assertStringContainsString("return \$this->moduleTabRedirect('configure');", $controller);
        $this->assertStringNotContainsString("return \$this->view('templatesTab'", $controller);
        $this->assertStringNotContainsString("return \$this->view('robotsTab'", $controller);
        $this->assertStringNotContainsString("return \$this->view('analyticsTab'", $controller);
        $this->assertStringNotContainsString('Seiger\\sCommerce\\Models\\sAttribute', $controller);
    }

    public function test_legacy_tab_blade_sources_are_not_active_after_evo_ui_migration(): void
    {
        foreach ([
            'views/analyticsTab.blade.php',
            'views/configureTab.blade.php',
            'views/dashboardTab.blade.php',
            'views/redirectsTab.blade.php',
            'views/robotsTab.blade.php',
            'views/templatesTab.blade.php',
            'views/fieldsBlock.blade.php',
            'views/index.blade.php',
            'views/partials/menu.blade.php',
            'views/partials/pagination.blade.php',
            'views/partials/perPageSelector.blade.php',
            'views/partials/redirects/tableRow.blade.php',
            'views/productSection.blade.php',
        ] as $legacyView) {
            $this->assertFalse(is_file($this->root . '/' . $legacyView), $legacyView . ' should be removed from active view sources.');
        }

        foreach ([
            'views/module/shell.blade.php',
            'views/module/dashboard.blade.php',
            'views/livewire/module-panel.blade.php',
            'views/livewire/robots-editor.blade.php',
            'views/livewire/meta-templates-editor.blade.php',
            'views/partials/fieldsBlock.blade.php',
            'views/resourceTab.blade.php',
            'views/moduleTab.blade.php',
        ] as $activeView) {
            $this->assertFileExists($this->root . '/' . $activeView);
        }

        $this->assertFileDoesNotExist($this->root . '/css/module.css');
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }
}
