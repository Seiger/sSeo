@php
    $dashboardCards = collect($sitemaps)->map(function (array $sitemap): array {
        $exists = (bool) ($sitemap['exists'] ?? false);
        $status = (string) ($sitemap['status'] ?? ($exists ? 'ready' : 'missing'));

        return [
            'title' => $sitemap['site'] ?? __('sSeo::global.pages_in_sitemap'),
            'icon' => 'list',
            'span' => 6,
            'status' => $status,
            'stats' => [
                [
                    'value' => (int) ($sitemap['pages'] ?? 0),
                ],
            ],
            'badges' => [
                [
                    'label' => $exists ? __('sSeo::global.sitemap_ready') : __('sSeo::global.sitemap_missing'),
                    'color' => $exists ? '#16A34A' : '#F59E0B',
                ],
            ],
            'meta' => array_values(array_filter([
                [
                    'label' => __('sSeo::global.last_generated'),
                    'value' => $exists && !empty($sitemap['time']) ? date('j M Y', (int) $sitemap['time']) : __('sSeo::global.none'),
                    'strong' => true,
                ],
                !empty($sitemap['file']) ? [
                    'value' => $sitemap['file'],
                ] : null,
            ])),
        ];
    })->all();
@endphp

<x-evo::dashboard :cards="$dashboardCards" data-sseo-dashboard>
    <x-slot:body>
        <div data-sseo-dashboard-activity>
        <x-evo::table.livewire
            preset="sseo.activity"
            :context="['activity' => array_values((array) ($activity ?? []))]"
            wire-key="sseo-dashboard-activity"
        />
        </div>
    </x-slot:body>
</x-evo::dashboard>
