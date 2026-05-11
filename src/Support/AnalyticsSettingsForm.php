<?php namespace Seiger\sSeo\Support;

class AnalyticsSettingsForm
{
    public static function make(array $base): array
    {
        $sites = self::sites();

        if ($sites === []) {
            return $base;
        }

        $base['sections'] = array_map(static function (array $site): array {
            $key = (string) $site['key'];
            $label = trim((string) ($site['site_name'] ?? '')) ?: $key;

            return [
                'key' => 'analytics-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $key),
                'label' => $label . ' (' . $key . ')',
                'icon' => 'chart-line',
                'span' => 12,
                'fields' => self::fields($key . '_'),
            ];
        }, $sites);

        return $base;
    }

    protected static function sites(): array
    {
        if (!function_exists('evo')) {
            return [];
        }

        try {
            if (!evo()->getConfig('check_sMultisite', false)) {
                return [];
            }
        } catch (\Throwable) {
            return [];
        }

        $model = \Seiger\sMultisite\Models\sMultisite::class;

        if (!class_exists($model)) {
            return [];
        }

        $sites = $model::all();

        if (!is_iterable($sites)) {
            return [];
        }

        $out = [];

        foreach ($sites as $site) {
            $key = trim((string) ($site->key ?? ''));

            if ($key === '') {
                continue;
            }

            $out[] = [
                'key' => $key,
                'site_name' => (string) ($site->site_name ?? ''),
            ];
        }

        return $out;
    }

    protected static function fields(string $prefix = ''): array
    {
        $ga4Ids = ['nullable', 'string', 'max:500', 'regex:/^\s*(?:G-[A-Z0-9]+(?:\s*,\s*G-[A-Z0-9]+)*)?\s*$/i'];
        $gtmIds = ['nullable', 'string', 'max:500', 'regex:/^\s*(?:GTM-[A-Z0-9]+(?:\s*,\s*GTM-[A-Z0-9]+)*)?\s*$/i'];

        return [
            [
                'name' => $prefix . 'gtm_container_id',
                'label' => 'sSeo::global.gtm_container_id',
                'type' => 'text',
                'default' => '',
                'rules' => $gtmIds,
                'help' => 'sSeo::global.gtm_container_id_help',
                'hint' => 'sSeo::global.gtm_container_id_help',
            ],
            [
                'name' => $prefix . 'ga4_measurement_id',
                'label' => 'sSeo::global.ga4_measurement_id',
                'type' => 'text',
                'default' => '',
                'rules' => $ga4Ids,
                'help' => 'sSeo::global.ga4_measurement_id_help',
                'hint' => 'sSeo::global.ga4_measurement_id_help',
            ],
        ];
    }
}
