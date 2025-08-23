<?php namespace Seiger\sSeo\Support;

/**
 * Class FastTagParser
 *
 * Ultra-fast, simplified Evolution CMS-like tag parser with static in-memory FIFO caching.
 * Focuses on the most common tag types and a minimal filter system to keep performance high.
 *
 * Supported tags:
 *  - [[Snippet? &a=`1` &b=`x`]]
 *  - {{chunkName}}
 *  - [+placeholder+]   (with simple output filters)
 *  - [*field_or_tv*]   (with simple output filters)
 *  - [(config_key)]
 *  - [~123~]           (link)
 *
 * Built-in filters:
 *  - :lower, :upper, :len, :notempty=`x`, :default=`x`, :trim
 *
 * Extensibility:
 *  - registerFilter(string $name, callable $fn)
 *  - setResolvers(variableResolver, fieldResolver, chunkResolver, snippetInvoker, linkResolver)
 *
 * Caching strategy:
 *  - L1: in-process static arrays (FIFO). Batch trimming reduces array-shift overhead.
 *  - Namespace versioning for instant mass invalidation (bumpNamespace()).
 *
 * Notes:
 *  - Designed for fast meta/inline parsing where full EVO parser is overkill.
 *  - Keep $maxPasses small unless you rely on heavily nested tags.
 *
 * @package Seiger\sSeo\Support
 */
final class FastTagParser
{
    /** @var array<string,string> Whole-source render cache (FIFO). */
    private static array $sourceCache = [];

    /** @var array<string,string> Per-snippet result cache (FIFO). */
    private static array $snippetCache = [];

    /** @var int Limits for caches (FIFO). */
    private static int $maxSourceCache  = 500;
    private static int $maxSnippetCache = 1000;

    /** @var int Batch trimming parameters to reduce per-put overhead. */
    private static int $trimEvery        = 64;   // trim once per N puts
    private static int $putsSourceCount  = 0;
    private static int $putsSnippetCount = 0;

    /** @var bool Enable/disable caches globally (useful for debugging). */
    private static bool $cacheEnabled = true;

    /** @var string Namespace version for fast mass invalidation (bump to purge). */
    private static string $nsVersion = 'v1';

    /** @var array{hits:int,miss:int} Simple hit/miss stats. */
    private static array $stats = ['hits' => 0, 'miss' => 0];

    /** Resolvers/Invokers (DI hooks). */
    private static $variableResolver = null; // fn(string $name, array $ctx): ?string
    private static $fieldResolver    = null; // fn(string $name, array $ctx): ?string
    private static $chunkResolver    = null; // fn(string $name, array $ctx): ?string
    private static $snippetInvoker   = null; // fn(string $name, array $params, array $ctx): string
    private static $linkResolver     = null; // fn(string $ref,  array $ctx): string

    /**
     * @var array<string, callable(string, ?string): string>
     * Output filters registry: name => fn(value, param) => value
     */
    private static array $filters = [];

    /** Precompiled (constant) regex patterns kept minimal for speed. */
    private const RE_SNIPPET     = '/\[\[([A-Za-z_][\w\-]*)\s*(?:\?([^\]]*))?\]\]/u';
    private const RE_CHUNK       = '/\{\{([A-Za-z_][\w\-]*)\}\}/u';
    private const RE_PLACEHOLDER = '/\[\+(.+?)\+\]((?::[A-Za-z_]\w*(?:=`[^`]*`)?)+)?/u';
    private const RE_FIELD       = '/\[\*(.+?)\*\]((?::[A-Za-z_]\w*(?:=`[^`]*`)?)+)?/u';
    private const RE_CONFIG      = '/\[\((.+?)\)\]/u';
    private const RE_LINK        = '/\[\~(.+?)\~\]/u';

    /**
     * Register default filters lazily.
     *
     * @return void
     */
    private static function ensureDefaultFilters(): void
    {
        if (!empty(self::$filters)) return;

        self::$filters['lower']    = static fn(string $v, ?string $p): string => mb_strtolower($v);
        self::$filters['upper']    = static fn(string $v, ?string $p): string => mb_strtoupper($v);
        self::$filters['len']      = static fn(string $v, ?string $p): string => (string) mb_strlen($v);
        self::$filters['default']  = static fn(string $v, ?string $p): string => ($v === '' ? (string)($p ?? '') : $v);
        self::$filters['notempty'] = static fn(string $v, ?string $p): string => ($v !== '' ? $v : (string)($p ?? ''));
        self::$filters['trim']     = static fn(string $v, ?string $p): string => trim($v);
    }

    /**
     * Register or override an output filter.
     *
     * @param string   $name Filter name used in templates.
     * @param callable $fn   Signature: fn(string $value, ?string $param): string
     * @return void
     */
    public static function registerFilter(string $name, callable $fn): void
    {
        self::$filters[$name] = $fn;
    }

    /**
     * Configure resolvers/invokers. Any null argument leaves the current resolver unchanged.
     *
     * @param callable|null $variableResolver fn(string $name, array $ctx): ?string
     * @param callable|null $fieldResolver    fn(string $name, array $ctx): ?string
     * @param callable|null $chunkResolver    fn(string $name, array $ctx): ?string
     * @param callable|null $snippetInvoker   fn(string $name, array $params, array $ctx): string
     * @param callable|null $linkResolver     fn(string $ref, array $ctx): string
     * @return void
     */
    public static function setResolvers(
        ?callable $variableResolver,
        ?callable $fieldResolver,
        ?callable $chunkResolver,
        ?callable $snippetInvoker,
        ?callable $linkResolver
    ): void {
        if ($variableResolver) self::$variableResolver = $variableResolver;
        if ($fieldResolver)    self::$fieldResolver    = $fieldResolver;
        if ($chunkResolver)    self::$chunkResolver    = $chunkResolver;
        if ($snippetInvoker)   self::$snippetInvoker   = $snippetInvoker;
        if ($linkResolver)     self::$linkResolver     = $linkResolver;
    }

    /**
     * Parse a source string with the given context and return the rendered output.
     * Uses static in-memory FIFO caches keyed by a SHA-1 of (namespace|source|ctxHash).
     *
     * @param string $source    Raw template source containing tags.
     * @param array  $ctx       Context: document fields, config, placeholders, etc.
     * @param int    $maxPasses Safety limit for nested tags resolution (default: 8).
     * @return string Rendered output.
     */
    public static function parse(string $source, array $ctx = [], int $maxPasses = 8): string
    {
        self::ensureDefaultFilters();

        $key = self::cacheKey($source, $ctx);

        // L1 FIFO: whole-source
        if (self::$cacheEnabled && isset(self::$sourceCache[$key])) {
            self::$stats['hits']++;
            return self::$sourceCache[$key];
        }

        self::$stats['miss']++;

        $out = $source;
        $pass = 0;

        // Iterative minimal passes to resolve nesting without building heavy ASTs.
        do {
            $prev = $out;

            // 1) [[Snippet? &a=`1` &b=`x`]]
            $out = preg_replace_callback(self::RE_SNIPPET, static function (array $m) use ($ctx): string {
                $name     = $m[1];
                $paramStr = $m[2] ?? '';
                $params   = self::parseSnippetParams($paramStr);

                $invoker = self::$snippetInvoker ?? static fn(string $n, array $p, array $c): string => '';
                $ck      = self::snippetCacheKey($name, $params, $ctx);

                if (self::$cacheEnabled && isset(self::$snippetCache[$ck])) {
                    return self::$snippetCache[$ck];
                }

                $res = (string) $invoker($name, $params, $ctx);

                self::putSnippetCache($ck, $res);
                return $res;
            }, $out) ?? $out;

            // 2) {{chunkName}}
            $out = preg_replace_callback(self::RE_CHUNK, static function (array $m) use ($ctx): string {
                $name     = $m[1];
                $resolver = self::$chunkResolver ?? static fn(string $n, array $c): ?string => null;
                return (string) ($resolver($name, $ctx) ?? '');
            }, $out) ?? $out;

            // 3) [+placeholder+] with filters
            $out = preg_replace_callback(self::RE_PLACEHOLDER, static function (array $m) use ($ctx): string {
                $name       = $m[1];
                $filterSpec = $m[2] ?? '';

                $resolver = self::$variableResolver ?? static fn(string $n, array $c): ?string => ($c[$n] ?? null);
                $val      = (string) ($resolver($name, $ctx) ?? '');

                return self::applyFilters($val, $filterSpec);
            }, $out) ?? $out;

            // 4) [*field_or_tv*] with filters
            $out = preg_replace_callback(self::RE_FIELD, static function (array $m) use ($ctx): string {
                $name       = $m[1];
                $filterSpec = $m[2] ?? '';

                $resolver = self::$fieldResolver ?? static fn(string $n, array $c): ?string => ($c[$n] ?? null);
                $val      = (string) ($resolver($name, $ctx) ?? '');

                return self::applyFilters($val, $filterSpec);
            }, $out) ?? $out;

            // 5) [(config_key)]
            $out = preg_replace_callback(self::RE_CONFIG, static function (array $m) use ($ctx): string {
                $k        = $m[1];
                $resolver = self::$variableResolver ?? static fn(string $n, array $c): ?string => ($c[$n] ?? null);
                return (string) ($resolver($k, $ctx) ?? '');
            }, $out) ?? $out;

            // 6) [~123~]
            $out = preg_replace_callback(self::RE_LINK, static function (array $m) use ($ctx): string {
                $ref      = trim($m[1]);
                $resolver = self::$linkResolver ?? static fn(string $r, array $c): string => '#'.$r;
                return (string) $resolver($ref, $ctx);
            }, $out) ?? $out;

            if ($out === $prev) break;
            $pass++;
        } while ($pass < $maxPasses);

        self::putSourceCache($key, $out);
        return $out;
    }

    /**
     * Enable/disable in-process caches (FIFO). Useful for debugging.
     *
     * @param bool $enabled
     * @return void
     */
    public static function setCacheEnabled(bool $enabled): void
    {
        self::$cacheEnabled = $enabled;
    }

    /**
     * Configure cache limits and batch trimming frequency.
     *
     * @param int $maxSource   Max entries in whole-source cache (min 16).
     * @param int $maxSnippet  Max entries in snippet cache (min 16).
     * @param int $trimEvery   Trim caches once per N put operations (min 8).
     * @return void
     */
    public static function configureCache(int $maxSource = 500, int $maxSnippet = 1000, int $trimEvery = 64): void
    {
        self::$maxSourceCache  = max(16, $maxSource);
        self::$maxSnippetCache = max(16, $maxSnippet);
        self::$trimEvery       = max(8,  $trimEvery);
    }

    /**
     * Bump namespace version to invalidate all cached entries instantly.
     * Example: FastTagParser::bumpNamespace('v2');
     *
     * @param string $newVersion
     * @return void
     */
    public static function bumpNamespace(string $newVersion): void
    {
        self::$nsVersion = $newVersion;
        // optional: also clear local caches to free memory immediately
        self::clearCache();
    }

    /**
     * Clear both caches and reset stats.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$sourceCache       = [];
        self::$snippetCache      = [];
        self::$putsSourceCount   = 0;
        self::$putsSnippetCount  = 0;
        self::$stats             = ['hits' => 0, 'miss' => 0];
    }

    /**
     * Return cache statistics.
     *
     * @return array{hits:int,miss:int,source:int,snippet:int}
     */
    public static function getCacheStats(): array
    {
        return [
            'hits'    => self::$stats['hits'],
            'miss'    => self::$stats['miss'],
            'source'  => count(self::$sourceCache),
            'snippet' => count(self::$snippetCache),
        ];
    }

    /**
     * Apply chained filters like ":lower:notempty=`X`".
     *
     * @param string      $value
     * @param string|null $filterSpec Filter chain suffix including colons (e.g. ":lower:default=`N/A`").
     * @return string
     */
    private static function applyFilters(string $value, ?string $filterSpec): string
    {
        if (!$filterSpec) return $value;

        // Split by ":" and ignore the leading empty chunk.
        $parts = array_values(array_filter(explode(':', $filterSpec), static fn($p) => $p !== ''));
        foreach ($parts as $chunk) {
            // pattern: name or name=`param`
            if (preg_match('/^([A-Za-z_]\w*)(?:=`([^`]*)`)?$/u', $chunk, $mm)) {
                $name  = $mm[1];
                $param = $mm[2] ?? null;

                $fn = self::$filters[$name] ?? null;
                if ($fn) {
                    // Any exception in filter should not break parsing; catch & continue.
                    try {
                        $value = (string) $fn($value, $param);
                    } catch (\Throwable $e) {
                        // swallow filter error to keep parser robust
                    }
                }
            }
        }
        return $value;
    }

    /**
     * Parse snippet parameters from a string "&a=`1` &b=`x`" to an associative array.
     * Only backtick-delimited values are supported for speed.
     *
     * @param string $paramStr
     * @return array<string,string>
     */
    private static function parseSnippetParams(string $paramStr): array
    {
        $out = [];
        if ($paramStr === '') return $out;

        if (preg_match_all('/&([A-Za-z_]\w*)=`([^`]*)`/u', $paramStr, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $out[$m[1]] = $m[2];
            }
        }
        return $out;
    }

    /**
     * Compute a stable cache key for a full source render.
     * Uses SHA-1 for speed as preferred in this codebase.
     *
     * @param string $source
     * @param array  $ctx
     * @return string
     */
    private static function cacheKey(string $source, array $ctx): string
    {
        return sha1(self::$nsVersion . '|' . $source . '|' . self::ctxHash($ctx));
    }

    /**
     * Compute a stable cache key for snippet invocation results.
     *
     * @param string               $name
     * @param array<string,string> $params
     * @param array                $ctx
     * @return string
     */
    private static function snippetCacheKey(string $name, array $params, array $ctx): string
    {
        ksort($params); // stabilize
        return sha1(self::$nsVersion . '|' . $name . '|' . json_encode($params, JSON_UNESCAPED_UNICODE) . '|' . self::ctxHash($ctx));
    }

    /**
     * Reduce context to a stable hash. Only scalars/null are considered to keep hashing fast.
     *
     * @param array $ctx
     * @return string
     */
    private static function ctxHash(array $ctx): string
    {
        $flat = [];
        foreach ($ctx as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $flat[$k] = $v;
            }
        }
        ksort($flat);
        return sha1(json_encode($flat, JSON_UNESCAPED_UNICODE));
    }

    /**
     * FIFO insert with batch trimming for the whole-source cache.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    private static function putSourceCache(string $key, string $value): void
    {
        if (!self::$cacheEnabled) return;

        self::$sourceCache[$key] = $value;

        if ((++self::$putsSourceCount % self::$trimEvery) === 0 && count(self::$sourceCache) > self::$maxSourceCache) {
            // Keep the newest N entries; preserve keys order.
            self::$sourceCache = array_slice(self::$sourceCache, -self::$maxSourceCache, null, true);
        }
    }

    /**
     * FIFO insert with batch trimming for the snippet cache.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    private static function putSnippetCache(string $key, string $value): void
    {
        if (!self::$cacheEnabled) return;

        self::$snippetCache[$key] = $value;

        if ((++self::$putsSnippetCount % self::$trimEvery) === 0 && count(self::$snippetCache) > self::$maxSnippetCache) {
            self::$snippetCache = array_slice(self::$snippetCache, -self::$maxSnippetCache, null, true);
        }
    }
}
