<?php namespace Seiger\sSeo\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Class Sitemaper
 *
 * Lightweight sitemap utilities with a focus on speed:
 * - count()      → total number of <url> entries (supports urlset & sitemapindex, .gz)
 * - eachUrl()    → generator yielding all <loc> URLs (recursively for indexes)
 * - detectType() → quick root type detection ('urlset' | 'sitemapindex' | 'unknown')
 *
 * Implementation details:
 * - Prefers built-in XMLReader for streaming & low memory usage.
 * - Falls back to file_get_contents + regex parsing when XMLReader is unavailable.
 * - Includes per-request runtime cache in count() and optional persistent cache (Cache::rememberForever).
 */
class Sitemaper
{
    /** @var array<string,int> In-request runtime cache for count() results. */
    private static array $runtime = [];

    /**
     * Count URLs in a sitemap (supports both urlset and sitemapindex).
     * The result is cached forever using a stable key based on the URL/path (SHA-1),
     * and also stored in a per-request runtime cache to avoid repeated work.
     *
     * - Supports .gz files/URLs via "compress.zlib://" wrapper.
     * - For sitemap indexes, recursively sums child sitemaps.
     *
     * @param string $pathOrUrl Local filesystem path or remote URL to a sitemap (may end with .gz).
     * @return int Total number of <url> entries across the sitemap (and recursively for indexes).
     */
    public static function count(string $pathOrUrl): int
    {
        $cacheKey = 'pagesInSitemap.' . sha1(strtolower($pathOrUrl));

        // Runtime (per-request) cache
        if (isset(self::$runtime[$cacheKey])) {
            return self::$runtime[$cacheKey];
        }

        // Persistent cache (Laravel)
        $val = Cache::rememberForever($cacheKey, function () use ($pathOrUrl) {
            $visited = [];
            return self::countInternal($pathOrUrl, $visited);
        });

        return self::$runtime[$cacheKey] = (int) $val;
    }

    /**
     * Iterate all URLs contained in a sitemap.
     * For a sitemap index, this yields URLs from each child sitemap recursively.
     *
     * - Yields decoded string URLs from <loc> elements.
     * - Works with .gz sources as well.
     *
     * @param string $pathOrUrl Local path or URL to a sitemap (may end with .gz).
     * @return \Generator<string> Generator yielding URL strings.
     */
    public static function eachUrl(string $pathOrUrl): \Generator
    {
        $visited = [];
        yield from self::eachUrlInternal($pathOrUrl, $visited);
    }

    /**
     * Detect the root type of a sitemap.
     *
     * @param string $pathOrUrl Local path or URL (may end with .gz).
     * @return 'urlset'|'sitemapindex'|'unknown' Root element kind, or 'unknown' if not recognized.
     */
    public static function detectType(string $pathOrUrl): string
    {
        $src = self::wrapIfGz($pathOrUrl);

        if (\class_exists('\XMLReader')) {
            $r = new \XMLReader();
            if ($r->open($src, null, \LIBXML_NONET | \LIBXML_COMPACT)) {
                while ($r->read()) {
                    if ($r->nodeType === \XMLReader::ELEMENT) {
                        $root = \strtolower($r->localName);
                        $r->close();
                        return ($root === 'urlset' || $root === 'sitemapindex') ? $root : 'unknown';
                    }
                }
                $r->close();
            }
            return 'unknown';
        }

        $xml = @\file_get_contents($src) ?: '';
        if (\stripos($xml, '<sitemapindex') !== false) return 'sitemapindex';
        if (\stripos($xml, '<urlset') !== false)       return 'urlset';
        return 'unknown';
    }

    // ------------------ internals ------------------

    /**
     * Internal recursive counter with loop protection.
     * Uses XMLReader when available; otherwise falls back to regex parsing.
     *
     * @param string                $pathOrUrl Local path or URL (may end with .gz).
     * @param array<string,bool>    $visited   Loop guard for sitemap indexes.
     * @return int Total <url> count for this node (or sum of children for indexes).
     */
    private static function countInternal(string $pathOrUrl, array &$visited): int
    {
        $src     = self::wrapIfGz($pathOrUrl);
        $loopKey = \strtolower($src);
        if (isset($visited[$loopKey])) return 0;
        $visited[$loopKey] = true;

        if (\class_exists('\XMLReader')) {
            $r = new \XMLReader();
            if (!$r->open($src, null, \LIBXML_NONET | \LIBXML_COMPACT)) return 0;

            // Detect root element
            $root = '';
            while ($r->read()) {
                if ($r->nodeType === \XMLReader::ELEMENT) {
                    $root = \strtolower($r->localName);
                    break;
                }
            }

            if ($root === 'urlset') {
                // Count <url> elements
                $count = 0;
                while ($r->read()) {
                    if ($r->nodeType === \XMLReader::ELEMENT && $r->localName === 'url') {
                        $count++;
                    }
                }
                $r->close();
                return $count;
            }

            if ($root === 'sitemapindex') {
                // Sum all child sitemaps referenced by <loc>
                $total = 0;
                while ($r->read()) {
                    if ($r->nodeType === \XMLReader::ELEMENT && $r->localName === 'loc') {
                        $loc = $r->readInnerXML();
                        $loc = \html_entity_decode(\trim(\strip_tags($loc)), \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
                        if ($loc !== '') $total += self::countInternal($loc, $visited);
                    }
                }
                $r->close();
                return $total;
            }

            $r->close();
            // Unknown root → fallback
            return self::fallbackCount($src, $visited);
        }

        // No XMLReader → fallback
        return self::fallbackCount($src, $visited);
    }

    /**
     * Internal iterator with loop protection.
     * Yields URLs from urlset; for sitemap indexes, traverses child sitemaps recursively.
     *
     * @param string                $pathOrUrl Local path or URL (may end with .gz).
     * @param array<string,bool>    $visited   Loop guard for sitemap indexes.
     * @return \Generator<string>             Yields URL strings.
     */
    private static function eachUrlInternal(string $pathOrUrl, array &$visited): \Generator
    {
        $src     = self::wrapIfGz($pathOrUrl);
        $loopKey = \strtolower($src);
        if (isset($visited[$loopKey])) return;
        $visited[$loopKey] = true;

        if (\class_exists('\XMLReader')) {
            $r = new \XMLReader();
            if (!$r->open($src, null, \LIBXML_NONET | \LIBXML_COMPACT)) return;

            // Detect root
            $root = '';
            while ($r->read()) {
                if ($r->nodeType === \XMLReader::ELEMENT) {
                    $root = \strtolower($r->localName);
                    break;
                }
            }

            if ($root === 'urlset') {
                // Walk each <url> and extract its first <loc>
                while ($r->read()) {
                    if ($r->nodeType === \XMLReader::ELEMENT && $r->localName === 'url') {
                        $targetDepth = $r->depth;
                        while ($r->read()) {
                            if ($r->nodeType === \XMLReader::END_ELEMENT
                                && $r->depth === $targetDepth
                                && $r->localName === 'url') {
                                break;
                            }
                            if ($r->nodeType === \XMLReader::ELEMENT && $r->localName === 'loc') {
                                $loc = $r->readInnerXML();
                                $loc = \html_entity_decode(\trim(\strip_tags($loc)), \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
                                if ($loc !== '') { yield $loc; }
                            }
                        }
                    }
                }
                $r->close();
                return;
            }

            if ($root === 'sitemapindex') {
                while ($r->read()) {
                    if ($r->nodeType === \XMLReader::ELEMENT && $r->localName === 'loc') {
                        $loc = $r->readInnerXML();
                        $loc = \html_entity_decode(\trim(\strip_tags($loc)), \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
                        if ($loc !== '') { yield from self::eachUrlInternal($loc, $visited); }
                    }
                }
                $r->close();
                return;
            }

            $r->close();
            // Unknown → fallback
        }

        // Fallback parser
        $xml = @\file_get_contents($src) ?: '';
        if ($xml === '') return;

        if (\stripos($xml, '<sitemapindex') !== false) {
            if (\preg_match_all('~<loc\b[^>]*>(.*?)</loc>~is', $xml, $m)) {
                foreach ($m[1] as $loc) {
                    $loc = \html_entity_decode(\trim(\strip_tags($loc)), \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
                    if ($loc !== '') { yield from self::eachUrlInternal($loc, $visited); }
                }
            }
            return;
        }

        if (\stripos($xml, '<urlset') !== false) {
            if (\preg_match_all('~<url\b[^>]*>(.*?)</url>~is', $xml, $m)) {
                foreach ($m[1] as $block) {
                    if (\preg_match('~<loc\b[^>]*>(.*?)</loc>~is', $block, $mm)) {
                        $loc = \html_entity_decode(\trim(\strip_tags($mm[1])), \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
                        if ($loc !== '') { yield $loc; }
                    }
                }
            }
        }
    }

    /**
     * Fallback counter using full-buffer regex parsing.
     * Handles both urlset and sitemapindex by recursively descending into <loc> entries.
     *
     * @param string                $src     Already wrapped path/URL (gz → 'compress.zlib://...').
     * @param array<string,bool>    $visited Loop guard for sitemap indexes.
     * @return int Number of <url> entries (or sum of children for indexes).
     */
    private static function fallbackCount(string $src, array &$visited): int
    {
        $xml = @\file_get_contents($src);
        if ($xml === false || $xml === '') return 0;

        // Sitemap index: sum all <loc> targets
        if (\stripos($xml, '<sitemapindex') !== false) {
            if (\preg_match_all('~<loc\b[^>]*>(.*?)</loc>~is', $xml, $m)) {
                $total = 0;
                foreach ($m[1] as $loc) {
                    $loc = \html_entity_decode(\trim(\strip_tags($loc)), \ENT_QUOTES | \ENT_SUBSTITUTE, 'UTF-8');
                    if ($loc !== '') $total += self::countInternal($loc, $visited);
                }
                return $total;
            }
            return 0;
        }

        // urlset: count <url ...>
        if (\preg_match_all('~<url\b~i', $xml, $mm)) {
            return \count($mm[0]);
        }
        return 0;
    }

    /**
     * Wrap a path/URL with the "compress.zlib://" stream wrapper when it points to a .gz resource.
     * If the source is already wrapped, it is returned unchanged.
     *
     * @param string $pathOrUrl Path or URL to the sitemap (may end with .gz or contain a query string).
     * @return string Wrapped source suitable for XMLReader/file_get_contents.
     */
    private static function wrapIfGz(string $pathOrUrl): string
    {
        if (\preg_match('~\.gz($|\?)~i', $pathOrUrl) && \strncmp($pathOrUrl, 'compress.zlib://', 17) !== 0) {
            return 'compress.zlib://' . $pathOrUrl;
        }
        return $pathOrUrl;
    }
}
