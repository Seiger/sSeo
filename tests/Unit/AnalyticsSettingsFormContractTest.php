<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Seiger\sSeo\Support\AnalyticsSettingsForm;

final class AnalyticsSettingsFormContractTest extends TestCase
{
    private array $form;

    protected function setUp(): void
    {
        $this->form = include dirname(__DIR__, 2) . '/config/analytics/form.php';
    }

    public function test_analytics_form_targets_sseo_settings_config(): void
    {
        $this->assertSame('sseo-analytics', $this->form['key']);
        $this->assertSame('sSeo::global.analytics', $this->form['title']);
        $this->assertSame('config', $this->form['variant']);
        $this->assertSame('compact', $this->form['density']);
        $this->assertSame('settings', $this->form['layout']);
        $this->assertSame('chart-line', $this->form['icon']);
        $this->assertSame('custom/config/seiger/settings/sSeo.php', $this->form['source']['file']);
        $this->assertSame('seiger.settings.sSeo', $this->form['source']['root']);
        $this->assertSame('chart-line', $this->form['sections'][0]['icon']);
        $this->assertFalse($this->form['sections'][0]['show_header']);
    }

    public function test_single_site_form_contains_global_gtm_and_ga4_fields(): void
    {
        $fields = $this->fieldsByName($this->form);

        $this->assertSame(['gtm_container_id', 'ga4_measurement_id'], array_keys($fields));
        $this->assertSame('sSeo::global.gtm_container_id', $fields['gtm_container_id']['label']);
        $this->assertSame('sSeo::global.ga4_measurement_id', $fields['ga4_measurement_id']['label']);
        $this->assertSame('sSeo::global.gtm_container_id_help', $fields['gtm_container_id']['hint']);
        $this->assertSame('sSeo::global.ga4_measurement_id_help', $fields['ga4_measurement_id']['hint']);
        $this->assertContains('regex:/^\s*(?:GTM-[A-Z0-9]+(?:\s*,\s*GTM-[A-Z0-9]+)*)?\s*$/i', $fields['gtm_container_id']['rules']);
        $this->assertContains('regex:/^\s*(?:G-[A-Z0-9]+(?:\s*,\s*G-[A-Z0-9]+)*)?\s*$/i', $fields['ga4_measurement_id']['rules']);
    }

    public function test_runtime_builder_keeps_base_form_when_multisite_is_not_available(): void
    {
        $built = AnalyticsSettingsForm::make($this->form);

        $this->assertSame($this->form, $built);
    }

    public function test_runtime_builder_has_multisite_panel_contract(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2) . '/src/Support/AnalyticsSettingsForm.php');

        $this->assertStringContainsString("evo()->getConfig('check_sMultisite', false)", $source);
        $this->assertStringContainsString('\\Seiger\\sMultisite\\Models\\sMultisite::class', $source);
        $this->assertStringContainsString("'label' => \$label . ' (' . \$key . ')'", $source);
        $this->assertStringContainsString("'icon' => 'chart-line'", $source);
        $this->assertStringContainsString("'span' => 12", $source);
        $this->assertStringNotContainsString("'show_header' => false", $source);
        $this->assertStringContainsString("self::fields(\$key . '_')", $source);
        $this->assertStringContainsString("'hint' => 'sSeo::global.gtm_container_id_help'", $source);
        $this->assertStringContainsString("'hint' => 'sSeo::global.ga4_measurement_id_help'", $source);
    }

    private function fieldsByName(array $form): array
    {
        $fields = [];

        foreach ($form['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $fields[$field['name']] = $field;
            }
        }

        return $fields;
    }
}
