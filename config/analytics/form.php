<?php

$ga4Ids = ['nullable', 'string', 'max:500', 'regex:/^\s*(?:G-[A-Z0-9]+(?:\s*,\s*G-[A-Z0-9]+)*)?\s*$/i'];
$gtmIds = ['nullable', 'string', 'max:500', 'regex:/^\s*(?:GTM-[A-Z0-9]+(?:\s*,\s*GTM-[A-Z0-9]+)*)?\s*$/i'];

return [
    'key' => 'sseo-analytics',
    'title' => 'sSeo::global.analytics',
    'icon' => 'chart-line',
    'variant' => 'config',
    'density' => 'compact',
    'layout' => 'settings',
    'section_headers' => true,
    'source' => [
        'type' => 'config',
        'file' => 'custom/config/seiger/settings/sSeo.php',
        'root' => 'seiger.settings.sSeo',
    ],
    'actions' => [
        [
            'type' => 'save',
            'icon' => 'check',
            'label' => 'evo::global.action_save',
            'tone' => 'primary',
            'variant' => 'filled',
            'icon_only' => false,
        ],
    ],
    'sections' => [
        [
            'key' => 'analytics',
            'label' => 'sSeo::global.analytics',
            'icon' => 'chart-line',
            'show_header' => false,
            'span' => 12,
            'fields' => [
                [
                    'name' => 'gtm_container_id',
                    'label' => 'sSeo::global.gtm_container_id',
                    'type' => 'text',
                    'default' => '',
                    'rules' => $gtmIds,
                    'help' => 'sSeo::global.gtm_container_id_help',
                    'hint' => 'sSeo::global.gtm_container_id_help',
                ],
                [
                    'name' => 'ga4_measurement_id',
                    'label' => 'sSeo::global.ga4_measurement_id',
                    'type' => 'text',
                    'default' => '',
                    'rules' => $ga4Ids,
                    'help' => 'sSeo::global.ga4_measurement_id_help',
                    'hint' => 'sSeo::global.ga4_measurement_id_help',
                ],
            ],
        ],
    ],
];
