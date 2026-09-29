<?php

namespace Seiger\sSeo\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Seiger\sSeo\Support\RedirectGuard;

class RedirectGuardTest extends TestCase
{
    private const API_PREFIXES = ['api'];

    public function testGetPageIsRedirectable(): void
    {
        $this->assertFalse(RedirectGuard::shouldSkip('GET', '/catalog/item', '/', self::API_PREFIXES));
    }

    public function testHeadIsRedirectable(): void
    {
        $this->assertFalse(RedirectGuard::shouldSkip('head', '/catalog/item', '/', self::API_PREFIXES));
    }

    public function testEmptyMethodFallsBackToPathRules(): void
    {
        $this->assertFalse(RedirectGuard::shouldSkip('', '/catalog/item', '/', self::API_PREFIXES));
        $this->assertTrue(RedirectGuard::shouldSkip('', '/api/token', '/', self::API_PREFIXES));
    }

    /**
     * A 301 turns these into a body-less GET, so they must never be canonicalized.
     */
    #[DataProvider('unsafeMethods')]
    public function testNonGetRequestsAreSkipped(string $method): void
    {
        $this->assertTrue(RedirectGuard::shouldSkip($method, '/mcp/content', '/', self::API_PREFIXES));
    }

    public static function unsafeMethods(): array
    {
        return [['POST'], ['PUT'], ['PATCH'], ['DELETE'], ['OPTIONS'], ['post']];
    }

    #[DataProvider('apiPaths')]
    public function testApiPrefixesAreSkipped(string $uri): void
    {
        $this->assertTrue(RedirectGuard::shouldSkip('GET', $uri, '/', self::API_PREFIXES));
    }

    public static function apiPaths(): array
    {
        return [['/api'], ['/api/'], ['/api/v1/token'], ['/api/v1/token?x=1'], ['//api//v1']];
    }

    public function testPrefixMustMatchWholeSegment(): void
    {
        $this->assertFalse(RedirectGuard::shouldSkip('GET', '/apiary/honey', '/', self::API_PREFIXES));
    }

    public function testConfiguredPrefixesAreSkipped(): void
    {
        $prefixes = ['api', '/mcp/', ' rest ', '', null, ['nested']];

        $this->assertTrue(RedirectGuard::shouldSkip('GET', '/mcp/content', '/', $prefixes));
        $this->assertTrue(RedirectGuard::shouldSkip('GET', '/rest/v2/items', '/', $prefixes));
        $this->assertFalse(RedirectGuard::shouldSkip('GET', '/blog/post', '/', $prefixes));
    }

    public function testNestedPrefixIsSupported(): void
    {
        $this->assertTrue(RedirectGuard::shouldSkip('GET', '/integrations/hooks/stripe', '/', ['integrations/hooks']));
        $this->assertFalse(RedirectGuard::shouldSkip('GET', '/integrations/about', '/', ['integrations/hooks']));
    }

    public function testSubfolderBaseUrlIsStrippedAsPrefix(): void
    {
        $this->assertTrue(RedirectGuard::shouldSkip('GET', '/shop/api/v1/token', '/shop/', self::API_PREFIXES));
        $this->assertFalse(RedirectGuard::shouldSkip('GET', '/shop/catalog', '/shop/', self::API_PREFIXES));
    }

    /**
     * The previous trim($path, EVO_BASE_URL) stripped a character set, not a prefix.
     */
    public function testRelativePathStripsBaseUrlAsPrefixNotCharacterSet(): void
    {
        $this->assertSame('hops', RedirectGuard::relativePath('/shop/hops', '/shop/'));
        $this->assertSame('api/v1', RedirectGuard::relativePath('/shop/api/v1/', '/shop'));
        $this->assertSame('', RedirectGuard::relativePath('/shop', '/shop/'));
    }

    public function testRelativePathLeavesOtherFoldersAndRootAlone(): void
    {
        $this->assertSame('shopping/cart', RedirectGuard::relativePath('/shopping/cart', '/shop/'));
        $this->assertSame('catalog/item', RedirectGuard::relativePath('/catalog/item/?page=2', '/'));
        $this->assertSame('catalog', RedirectGuard::relativePath('/catalog', ''));
    }

    public function testNormalizePrefixesDropsInvalidAndDuplicateValues(): void
    {
        $this->assertSame(
            ['api', 'mcp', 'a/b'],
            RedirectGuard::normalizePrefixes(['api', '/api/', 'mcp', '', '  ', null, 42 => '/a/b/', ['x']])
        );
    }
}
