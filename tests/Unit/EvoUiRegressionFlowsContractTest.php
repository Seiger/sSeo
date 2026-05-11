<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Seiger\sSeo\Controllers\sSeoController;
use Seiger\sSeo\Livewire\RobotsEditor;

final class EvoUiRegressionFlowsContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_settings_dump_preserves_scalar_array_and_boolean_types(): void
    {
        $controller = new sSeoController();
        $method = new ReflectionMethod($controller, 'dumpSettingsPhp');
        $method->setAccessible(true);

        $php = $method->invoke($controller, [
            'meta_tags_mode' => 'replace',
            'manage_www' => 2,
            'redirects_enabled' => 1,
            'generate_sitemap' => 0,
            'noindex_get' => ['page', 'filter'],
            'product_attribute_aliases' => ['sku', 'brand'],
            'strict' => true,
        ]);

        $this->assertStringContainsString('"meta_tags_mode" => "replace"', $php);
        $this->assertStringContainsString('"manage_www" => 2', $php);
        $this->assertStringContainsString('"redirects_enabled" => 1', $php);
        $this->assertStringContainsString('"generate_sitemap" => 0', $php);
        $this->assertStringContainsString('"noindex_get" => [', $php);
        $this->assertStringContainsString('"page"', $php);
        $this->assertStringContainsString('"product_attribute_aliases" => [', $php);
        $this->assertStringContainsString('"brand"', $php);
        $this->assertStringContainsString('"strict" => true', $php);
    }

    public function test_robots_editor_writes_only_when_target_directory_is_writable(): void
    {
        $tmp = sys_get_temp_dir() . '/sseo-robots-' . bin2hex(random_bytes(4));
        $target = $tmp . '/nested/robots.txt';

        try {
            $editor = new RobotsEditor();
            $method = new ReflectionMethod($editor, 'writeFile');
            $method->setAccessible(true);

            $this->assertTrue($method->invoke($editor, $target, "User-agent: *\nAllow: /\n"));
            $this->assertFileExists($target);
            $this->assertSame("User-agent: *\nAllow: /\n", file_get_contents($target));
        } finally {
            if (is_file($target)) {
                unlink($target);
            }
            if (is_dir(dirname($target))) {
                rmdir(dirname($target));
            }
            if (is_dir($tmp)) {
                rmdir($tmp);
            }
        }
    }

    public function test_evo_ui_crud_settings_resource_regression_contracts_are_covered(): void
    {
        $redirects = $this->read('src/Tables/RedirectsTableData.php');
        $controller = $this->read('src/Controllers/sSeoController.php');
        $seo = $this->read('src/sSeo.php');
        $templates = $this->read('src/Livewire/MetaTemplatesEditor.php');
        $robots = $this->read('src/Livewire/RobotsEditor.php');

        $this->assertStringContainsString('public function saveModal(array $data, ?int $redirectId = null', $redirects);
        $this->assertStringContainsString("whereIn('site_key', [\$siteKey, 'all'])", $redirects);
        $this->assertStringContainsString('ValidationException::withMessages', $redirects);
        $this->assertStringContainsString('public function deleteRow(int $redirectId)', $redirects);
        $this->assertSame(2, substr_count($redirects, "evo()->clearCache('full');"));

        $this->assertStringContainsString("protected function saveSettings(array \$updates)", $controller);
        $this->assertStringContainsString("\$this->ensureSettingsDir(\$path)", $controller);
        $this->assertStringContainsString("\$this->canWriteSettingsFile(\$path)", $controller);
        $this->assertStringContainsString("'noindex_get' => \$noindexGet", $controller);
        $this->assertStringContainsString("'product_attribute_aliases' => array_values(array_filter", $controller);
        $this->assertStringContainsString('AnalyticsIdParser::parseGtmStrict', $controller);
        $this->assertStringContainsString('AnalyticsIdParser::parseGa4Strict', $controller);
        $this->assertStringContainsString("with('error', __('sSeo::global.analytics_invalid_ids'", $controller);

        $this->assertStringContainsString("str_starts_with(\$key, 'sseo_')", $templates);
        $this->assertStringContainsString("DB::table('system_settings')->updateOrInsert", $templates);
        $this->assertStringContainsString('removeSanitizeSeed($value)', $templates);
        $this->assertStringContainsString('evo()->setConfig($key, $value)', $templates);

        $this->assertStringContainsString('trim($content) === \'\'', $robots);
        $this->assertStringContainsString("__('sSeo::global.not_writable'", $robots);
        $this->assertStringContainsString('file_put_contents($path, $content)', $robots);

        $this->assertStringContainsString("case 'int':", $seo);
        $this->assertStringContainsString("case 'bool':", $seo);
        $this->assertStringContainsString("case 'float':", $seo);
        $this->assertStringContainsString("case 'json':", $seo);
        $this->assertStringContainsString("\$item->save();", $seo);
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }
}
