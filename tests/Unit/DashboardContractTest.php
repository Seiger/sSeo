<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DashboardContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_dashboard_exposes_sitemap_status_contract(): void
    {
        $controller = $this->read('src/Controllers/sSeoController.php');
        $dashboard = $this->read('views/module/dashboard.blade.php');

        $this->assertStringContainsString("'file' => \$file", $controller);
        $this->assertStringContainsString("'exists' => \$exists", $controller);
        $this->assertStringContainsString("'status' => \$exists ? 'ready' : 'missing'", $controller);
        $this->assertStringContainsString("'activity' => \$this->dashboardActivity(\$sitemaps)", $controller);
        $this->assertStringContainsString('protected function dashboardActivity(array $sitemaps): array', $controller);
        $this->assertStringContainsString('sRedirect::query()', $controller);
        $this->assertStringContainsString('sSeoModel::query()', $controller);
        $this->assertStringContainsString('array_slice($activity, 0, 50)', $controller);
        $this->assertStringContainsString('->limit(12)', $controller);
        $this->assertFileExists($this->root . '/config/activity/table.php');
        $activityConfig = include $this->root . '/config/activity/table.php';
        $this->assertSame('sseo.activity', $activityConfig['key']);
        $this->assertSame('sSeo::global.recent_activity', $activityConfig['title']);
        $this->assertSame('activity', $activityConfig['title_icon']);
        $this->assertArrayNotHasKey('title_placement', $activityConfig);
        $this->assertSame(\Seiger\sSeo\Tables\ActivityTableData::class, $activityConfig['provider']);
        $this->assertStringContainsString('<x-evo::dashboard :cards="$dashboardCards" data-sseo-dashboard>', $dashboard);
        $this->assertStringContainsString("'span' => 6", $dashboard);
        $this->assertStringContainsString("'status' => \$status", $dashboard);
        $this->assertStringContainsString("'color' => \$exists ? '#16A34A' : '#F59E0B'", $dashboard);
        $this->assertStringNotContainsString('sseo-dashboard-card--span-6', $dashboard);
        $this->assertStringNotContainsString('sseo-dashboard-cards', $dashboard);
        $this->assertStringContainsString('data-sseo-dashboard-activity', $dashboard);
        $this->assertStringContainsString('preset="sseo.activity"', $dashboard);
        $this->assertStringContainsString('<x-evo::table.livewire', $dashboard);
        $this->assertStringNotContainsString('data-sseo-activity-table', $dashboard);
        $this->assertStringNotContainsString('data-sseo-activity-search', $dashboard);
        $this->assertStringNotContainsString('data-sseo-activity-sort', $dashboard);
        $this->assertStringNotContainsString('data-sseo-activity-pagination', $dashboard);
        $this->assertStringNotContainsString('bindActivityTable', $dashboard);
        $this->assertStringNotContainsString('sseo-dashboard-activity__icon', $dashboard);
        $this->assertStringNotContainsString('sseo-dashboard-activity__item', $dashboard);
        $this->assertStringNotContainsString("sSeo::global.recent_activity", $dashboard);
        $this->assertStringNotContainsString('sseo-dashboard-activity__title', $dashboard);
        $this->assertStringNotContainsString('<style>', $dashboard);
        $this->assertStringNotContainsString('style=', $dashboard);
        $this->assertStringContainsString("sSeo::global.sitemap_ready", $dashboard);
        $this->assertStringContainsString("sSeo::global.sitemap_missing", $dashboard);
        $this->assertStringContainsString("\$sitemap['file']", $dashboard);
    }

    public function test_sitemap_status_translations_are_available(): void
    {
        foreach (['en', 'uk', 'ru'] as $locale) {
            $translations = include $this->root . '/lang/' . $locale . '/global.php';

            foreach ([
                'sitemap_ready',
                'sitemap_missing',
                'recent_activity',
                'activity_sitemap_ready',
                'activity_sitemap_missing',
                'activity_redirect_updated',
                'activity_seo_updated',
                'activity_empty',
                'activity_search_placeholder',
                'activity_column_activity',
                'activity_column_details',
                'activity_column_time',
            ] as $key) {
                $this->assertArrayHasKey($key, $translations);
            }
        }
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }
}
