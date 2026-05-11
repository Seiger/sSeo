<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ResourceSeoFieldsContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_resource_seo_partial_uses_resource_safe_surface_without_changing_payload_names(): void
    {
        $partial = $this->read('views/partials/fieldsBlock.blade.php');

        $this->assertStringNotContainsString("@include('evo::partials.assets')", $partial);
        $this->assertStringNotContainsString('assets/modules/evo-ui/evo-ui.css', $partial);
        $this->assertStringContainsString('data-sseo-resource-fields', $partial);
        $this->assertStringNotContainsString('evo-ui-form-surface', $partial);
        $this->assertStringNotContainsString('evo-ui-input', $partial);
        $this->assertStringContainsString('row form-row', $partial);
        $this->assertStringContainsString('form-control', $partial);
        $this->assertStringContainsString('col-title-11', $partial);
        $this->assertStringContainsString('data-tooltip', $partial);
        $this->assertStringContainsString('form-text text-muted', $partial);

        foreach ([
            'sseo[{{ $fieldLang }}][robots]',
            'sseo[{{ $fieldLang }}][meta_title]',
            'sseo[{{ $fieldLang }}][meta_description]',
            'sseo[{{ $fieldLang }}][meta_keywords]',
            'sseo[{{ $fieldLang }}][canonical_url]',
            'sseo[{{ $fieldLang }}][exclude_from_sitemap]',
            'sseo[{{ $fieldLang }}][priority]',
            'sseo[{{ $fieldLang }}][changefreq]',
            'sseo[{{ $fieldLang }}][domain_key]',
        ] as $name) {
            $this->assertStringContainsString($name, $partial);
        }
    }

    public function test_resource_seo_partial_keeps_visible_helper_hints_translated(): void
    {
        $partial = $this->read('views/partials/fieldsBlock.blade.php');
        $hintKeys = [
            'robots_hint',
            'meta_title_hint',
            'meta_description_hint',
            'meta_keywords_hint',
            'canonical_hint',
            'exclude_from_sitemap_hint',
            'priority_hint',
            'change_frequency_hint',
        ];

        foreach ($hintKeys as $hintKey) {
            $this->assertStringContainsString("sSeo::global.$hintKey", $partial);
        }

        foreach (['en', 'uk', 'ru'] as $locale) {
            $translations = include $this->root . '/lang/' . $locale . '/global.php';

            foreach ($hintKeys as $hintKey) {
                $this->assertArrayHasKey($hintKey, $translations, "$locale is missing $hintKey.");
                $this->assertNotSame('', trim((string) $translations[$hintKey]), "$locale $hintKey must not be empty.");
            }
        }
    }

    public function test_resource_and_product_tabs_still_reuse_the_shared_fields_partial(): void
    {
        $resourceTab = $this->read('views/resourceTab.blade.php');
        $moduleTab = $this->read('views/moduleTab.blade.php');
        $plugin = $this->read('plugins/sSeoPlugin.php');

        $this->assertStringContainsString("@include('sSeo::partials.fieldsBlock')", $resourceTab);
        $this->assertStringContainsString("@include('sSeo::partials.fieldsBlock')", $moduleTab);
        $this->assertStringContainsString("view('sSeo::partials.fieldsBlock'", $plugin);
        $this->assertStringContainsString("sSeo::updateSeoFields(\$data);", $plugin);
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }
}
