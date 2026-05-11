<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ConfigureSettingsFormContractTest extends TestCase
{
    private array $form;

    protected function setUp(): void
    {
        $this->form = include dirname(__DIR__, 2) . '/config/settings/form.php';
    }

    public function test_settings_form_targets_sseo_config_file_and_evo_ui_config_variant(): void
    {
        $this->assertSame('sseo-settings', $this->form['key']);
        $this->assertSame('global.settings_config', $this->form['title']);
        $this->assertSame('config', $this->form['variant']);
        $this->assertSame('compact', $this->form['density']);
        $this->assertSame('settings', $this->form['layout']);
        $this->assertFalse($this->form['show_heading']);
        $this->assertSame([
            ['key' => 'left', 'sections' => ['analytics', 'indexing', 'commerce']],
            ['key' => 'right', 'sections' => ['features', 'server']],
        ], $this->form['section_columns']);
        $this->assertNull($this->form['icon']);
        $this->assertTrue($this->form['section_headers']);
        $this->assertSame('config', $this->form['source']['type']);
        $this->assertSame('custom/config/seiger/settings/sSeo.php', $this->form['source']['file']);
        $this->assertSame('seiger.settings.sSeo', $this->form['source']['root']);
        $this->assertSame('save', $this->form['actions'][0]['type']);
    }

    public function test_configuration_label_uses_system_translation_key(): void
    {
        $root = dirname(__DIR__, 2);
        $tabs = include $root . '/config/module/tabs.php';

        $this->assertSame('global.settings_config', $tabs[4]['label']);
        $this->assertNotContains('analytics', array_column($tabs, 'key'));
        $this->assertFileDoesNotExist($root . '/views/partials/menu.blade.php');

        foreach (['en', 'uk', 'ru'] as $locale) {
            $translations = include $root . '/lang/' . $locale . '/global.php';
            $this->assertArrayNotHasKey('configure', $translations);
        }
    }

    public function test_configure_form_covers_legacy_non_analytics_settings(): void
    {
        $fields = $this->fieldsByName();

        $this->assertSame([
            'gtm_container_id',
            'ga4_measurement_id',
            'paginates_get',
            'noindex_get',
            'meta_tags_mode',
            'redirects_enabled',
            'generate_sitemap',
            'product_attribute_aliases',
            '_server_protocol',
            'manage_www',
        ], array_keys($fields));

        $this->assertSame('text', $fields['gtm_container_id']['type']);
        $this->assertSame('text', $fields['ga4_measurement_id']['type']);
        $this->assertSame('text', $fields['paginates_get']['type']);
        $this->assertSame('csv', $fields['noindex_get']['type']);
        $this->assertSame('select', $fields['meta_tags_mode']['type']);
        $this->assertSame('checkbox', $fields['redirects_enabled']['type']);
        $this->assertSame('checkbox', $fields['generate_sitemap']['type']);
        $this->assertSame('csv', $fields['product_attribute_aliases']['type']);
        $this->assertSame('sseo-server-protocol', $fields['_server_protocol']['type']);
        $this->assertFalse($fields['_server_protocol']['save']);
        $this->assertSame('select', $fields['manage_www']['type']);

        foreach (array_keys($fields) as $name) {
            $this->assertArrayHasKey('help', $fields[$name], $name . ' must keep tooltip help.');
            $this->assertArrayHasKey('hint', $fields[$name], $name . ' must show visible helper text.');
        }

        $this->assertTrue($fields['paginates_get']['hint_html']);
        $this->assertTrue($fields['_server_protocol']['hint_html']);
    }

    public function test_configure_form_keeps_legacy_section_placement_and_compact_columns(): void
    {
        $sections = collect($this->form['sections'])->keyBy('key')->all();

        $this->assertSame(12, $sections['analytics']['span']);
        $this->assertSame(6, $sections['indexing']['span']);
        $this->assertSame(6, $sections['features']['span']);
        $this->assertSame(6, $sections['commerce']['span']);
        $this->assertSame(6, $sections['server']['span']);

        foreach ($sections as $section) {
            $this->assertNull($section['icon'] ?? null);
        }

        $this->assertSame(['gtm_container_id', 'ga4_measurement_id'], array_column($sections['analytics']['fields'], 'name'));
        $this->assertSame(['paginates_get', 'noindex_get'], array_column($sections['indexing']['fields'], 'name'));
        $this->assertSame(['meta_tags_mode', 'redirects_enabled', 'generate_sitemap'], array_column($sections['features']['fields'], 'name'));
        $this->assertSame(['product_attribute_aliases'], array_column($sections['commerce']['fields'], 'name'));
        $this->assertSame(['_server_protocol', 'manage_www'], array_column($sections['server']['fields'], 'name'));
    }

    public function test_sseo_settings_forms_delegate_horizontal_labels_to_evo_ui_settings_layout(): void
    {
        $analytics = include dirname(__DIR__, 2) . '/config/analytics/form.php';

        foreach ([$this->form, $analytics] as $form) {
            $this->assertSame('settings', $form['layout']);
            $this->assertSame('compact', $form['density']);
            $this->assertSame('config', $form['variant']);
            $this->assertTrue($form['section_headers']);

            foreach ($form['sections'] as $section) {
                foreach ($section['fields'] as $field) {
                    $this->assertArrayHasKey('label', $field, $field['name']);
                    $this->assertArrayHasKey('help', $field, $field['name']);
                    $this->assertArrayHasKey('hint', $field, $field['name']);
                }
            }
        }
    }

    public function test_selects_and_csv_fields_keep_storage_contracts_stable(): void
    {
        $fields = $this->fieldsByName();

        $this->assertSame(['replace', 'fill'], array_column($fields['meta_tags_mode']['options'], 'value'));
        $this->assertSame([0, 1, 2], array_column($fields['manage_www']['options'], 'value'));
        $this->assertSame([], $fields['noindex_get']['default']);
        $this->assertSame([], $fields['product_attribute_aliases']['default']);
        $this->assertContains('in:replace,fill', $fields['meta_tags_mode']['rules']);
        $this->assertContains('in:0,1,2', $fields['manage_www']['rules']);
    }

    private function fieldsByName(): array
    {
        $fields = [];

        foreach ($this->form['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $fields[$field['name']] = $field;
            }
        }

        return $fields;
    }
}
