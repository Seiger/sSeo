<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Seiger\sSeo\Support\AnalyticsIdParser;
use Seiger\sSeo\Support\FastTagParser;
use Seiger\sSeo\Support\Sitemaper;

final class SupportRuntimeContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
        FastTagParser::clearCache();
        FastTagParser::setCacheEnabled(true);
        FastTagParser::bumpNamespace('test-' . uniqid('', true));
    }

    public function test_analytics_id_parser_normalizes_deduplicates_and_reports_invalid_tokens(): void
    {
        $gtm = AnalyticsIdParser::parseGtmStrict(' gtm-abc123, GTM-ABC123, nope, GTM-Z9 ');
        $ga4 = AnalyticsIdParser::parseGa4Strict(' g-abc123, bad, G-ABC123, G-Z9 ');

        $this->assertSame(['GTM-ABC123', 'GTM-Z9'], $gtm['valid']);
        $this->assertSame(['nope'], $gtm['invalid']);

        $this->assertSame(['G-ABC123', 'G-Z9'], $ga4['valid']);
        $this->assertSame(['bad'], $ga4['invalid']);

        $this->assertSame(['GTM-ONE'], AnalyticsIdParser::parseGtmIds('GTM-ONE,wrong'));
        $this->assertSame(['G-TWO'], AnalyticsIdParser::parseGa4Ids('wrong,G-TWO'));
    }

    public function test_fast_tag_parser_resolves_common_evo_tags_filters_and_cache_stats(): void
    {
        FastTagParser::setResolvers(
            variableResolver: static fn (string $name, array $ctx): ?string => $ctx['vars'][$name] ?? null,
            fieldResolver: static fn (string $name, array $ctx): ?string => $ctx['fields'][$name] ?? null,
            chunkResolver: static fn (string $name, array $ctx): ?string => $ctx['chunks'][$name] ?? null,
            snippetInvoker: static fn (string $name, array $params, array $ctx): string => $name . ':' . ($params['value'] ?? ''),
            linkResolver: static fn (string $ref, array $ctx): string => '/resource-' . $ref,
        );

        $source = '[(site_name)]|[*pagetitle*]:trim:upper|[+missing+]:default=`fallback`|{{intro}}|[[Echo? &value=`ok`]]|[~12~]';
        $context = [
            'vars' => ['site_name' => 'Demo'],
            'fields' => ['pagetitle' => ' hello '],
            'chunks' => ['intro' => 'Chunk'],
        ];

        $this->assertSame('Demo|HELLO|fallback|Chunk|Echo:ok|/resource-12', FastTagParser::parse($source, $context));

        $firstStats = FastTagParser::getCacheStats();
        $this->assertSame(0, $firstStats['hits']);
        $this->assertSame(1, $firstStats['miss']);
        $this->assertSame(1, $firstStats['source']);

        $this->assertSame('Demo|HELLO|fallback|Chunk|Echo:ok|/resource-12', FastTagParser::parse($source, $context));

        $secondStats = FastTagParser::getCacheStats();
        $this->assertSame(1, $secondStats['hits']);
        $this->assertSame(1, $secondStats['miss']);
    }

    public function test_sitemaper_detects_urlset_and_yields_decoded_urls(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'sseo-sitemap-');
        $this->assertIsString($file);

        file_put_contents($file, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>https://example.test/one</loc></url>
    <url><loc>https://example.test/two?x=1&amp;y=2</loc></url>
</urlset>
XML);

        try {
            $this->assertSame('urlset', Sitemaper::detectType($file));
            $this->assertSame([
                'https://example.test/one',
                'https://example.test/two?x=1&y=2',
            ], iterator_to_array(Sitemaper::eachUrl($file), false));
        } finally {
            @unlink($file);
        }
    }

    public function test_models_and_initial_migration_keep_seo_storage_contract(): void
    {
        $seoModel = $this->read('src/Models/sSeoModel.php');
        $redirectModel = $this->read('src/Models/sRedirect.php');
        $migration = $this->read('database/migrations/2024_11_18_094556_create_s_seo_table.php');

        $this->assertStringContainsString("protected \$table = 's_seo';", $seoModel);
        $this->assertStringContainsString("protected \$primaryKey = 'seoid';", $seoModel);
        $this->assertStringContainsString("'structured_data' => 'array'", $seoModel);
        $this->assertStringContainsString("'extra_meta' => 'array'", $seoModel);

        foreach (['resource_id', 'resource_type', 'lang', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'robots', 'priority', 'changefreq'] as $field) {
            $this->assertStringContainsString("'" . $field . "'", $seoModel);
        }

        $this->assertStringContainsString("protected \$fillable = ['site_key', 'old_url', 'new_url', 'type'];", $redirectModel);
        $this->assertStringContainsString('function scopeOrderByNatural(', $redirectModel);
        $this->assertStringContainsString('function forCurrentSite(', $redirectModel);

        foreach (["Schema::create('s_seo'", "Schema::create('s_redirects'", "\$table->id('seoid')", "\$table->jsonb('extra_meta')", "\$table->string('site_key')->default('all')"] as $schemaRule) {
            $this->assertStringContainsString($schemaRule, $migration);
        }
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }
}
