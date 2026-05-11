<?php

$boolean = ['boolean'];
$csv = ['nullable', 'string', 'max:500'];
$ga4Ids = ['nullable', 'string', 'max:500', 'regex:/^\s*(?:G-[A-Z0-9]+(?:\s*,\s*G-[A-Z0-9]+)*)?\s*$/i'];
$gtmIds = ['nullable', 'string', 'max:500', 'regex:/^\s*(?:GTM-[A-Z0-9]+(?:\s*,\s*GTM-[A-Z0-9]+)*)?\s*$/i'];
$text = ['nullable', 'string', 'max:255'];

return [
    'key' => 'sseo-settings',
    'title' => 'global.settings_config',
    'icon' => null,
    'variant' => 'config',
    'density' => 'compact',
    'section_headers' => true,
    'layout' => 'settings',
    'show_heading' => false,
    'section_columns' => [
        ['key' => 'left', 'sections' => ['analytics', 'indexing', 'commerce']],
        ['key' => 'right', 'sections' => ['features', 'server']],
    ],
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
            'icon' => null,
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
        [
            'key' => 'indexing',
            'label' => 'sSeo::global.indexing',
            'icon' => null,
            'span' => 6,
            'fields' => [
                [
                    'name' => 'paginates_get',
                    'label' => 'sSeo::global.paginates_get',
                    'type' => 'text',
                    'default' => 'page',
                    'rules' => $text,
                    'help' => 'sSeo::global.paginates_get_help',
                    'hint' => 'sSeo::global.paginates_get_help',
                    'hint_html' => true,
                ],
                [
                    'name' => 'noindex_get',
                    'label' => 'sSeo::global.noindex_get',
                    'type' => 'csv',
                    'default' => [],
                    'rules' => $csv,
                    'help' => 'sSeo::global.noindex_get_help',
                    'hint' => 'sSeo::global.noindex_get_help',
                ],
            ],
        ],
        [
            'key' => 'features',
            'label' => 'sSeo::global.functionality',
            'icon' => null,
            'span' => 6,
            'fields' => [
                [
                    'name' => 'meta_tags_mode',
                    'label' => 'sSeo::global.meta_tags_mode',
                    'type' => 'select',
                    'default' => 'replace',
                    'rules' => ['required', 'string', 'in:replace,fill'],
                    'help' => 'sSeo::global.meta_tags_mode_help',
                    'hint' => 'sSeo::global.meta_tags_mode_help',
                    'options' => [
                        ['value' => 'replace', 'label' => 'sSeo::global.replace'],
                        ['value' => 'fill', 'label' => 'sSeo::global.fill'],
                    ],
                ],
                [
                    'name' => 'redirects_enabled',
                    'label' => 'sSeo::global.redirects_enabled',
                    'type' => 'checkbox',
                    'default' => 1,
                    'rules' => $boolean,
                    'help' => 'sSeo::global.redirects_enabled_help',
                    'hint' => 'sSeo::global.redirects_enabled_help',
                ],
                [
                    'name' => 'generate_sitemap',
                    'label' => 'sSeo::global.generate_sitemap',
                    'type' => 'checkbox',
                    'default' => 1,
                    'rules' => $boolean,
                    'help' => 'sSeo::global.generate_sitemap_help',
                    'hint' => 'sSeo::global.generate_sitemap_help',
                ],
            ],
        ],
        [
            'key' => 'commerce',
            'label' => 'sSeo::global.product_attribute_aliases',
            'icon' => null,
            'span' => 6,
            'fields' => [
                [
                    'name' => 'product_attribute_aliases',
                    'label' => 'sSeo::global.product_attribute_aliases',
                    'type' => 'csv',
                    'default' => [],
                    'rules' => $csv,
                    'help' => 'sSeo::global.product_attribute_aliases_help',
                    'hint' => 'sSeo::global.product_attribute_aliases_help',
                ],
            ],
        ],
        [
            'key' => 'server',
            'label' => 'sSeo::global.server',
            'icon' => null,
            'span' => 6,
            'fields' => [
                [
                    'name' => '_server_protocol',
                    'label' => 'global.server_protocol_title',
                    'type' => 'sseo-server-protocol',
                    'default' => 'http',
                    'save' => false,
                    'help' => 'sSeo::global.protocol_help',
                    'hint' => 'sSeo::global.protocol_help',
                    'hint_html' => true,
                ],
                [
                    'name' => 'manage_www',
                    'label' => 'sSeo::global.manage_www',
                    'type' => 'select',
                    'default' => 0,
                    'rules' => ['required', 'integer', 'in:0,1,2'],
                    'help' => 'sSeo::global.manage_www_help',
                    'hint' => 'sSeo::global.manage_www_help',
                    'options' => [
                        ['value' => 0, 'label' => 'sSeo::global.ignore'],
                        ['value' => 1, 'label' => 'sSeo::global.without_www'],
                        ['value' => 2, 'label' => 'sSeo::global.using_www'],
                    ],
                ],
            ],
        ],
    ],
];
