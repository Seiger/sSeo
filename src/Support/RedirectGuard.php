<?php namespace Seiger\sSeo\Support;

/**
 * Decide whether a front-end request may be canonicalized with a 301 redirect.
 *
 * Only safe navigations (GET/HEAD) are redirected: a 301 turns POST/PUT/PATCH/DELETE
 * into a body-less GET, which breaks forms, webhooks and JSON-RPC endpoints (e.g. MCP).
 * Requests under API-like path prefixes are never redirected either.
 */
class RedirectGuard
{
    public const REDIRECTABLE_METHODS = ['GET', 'HEAD'];

    /**
     * @param string $method HTTP method of the current request
     * @param string $requestUri Raw REQUEST_URI (path + optional query)
     * @param string $baseUrl Site base URL path, e.g. "/" or "/shop/"
     * @param array<int, mixed> $skipPrefixes Path prefixes excluded from redirects, e.g. ["api", "mcp"]
     */
    public static function shouldSkip(string $method, string $requestUri, string $baseUrl, array $skipPrefixes): bool
    {
        $method = strtoupper(trim($method));
        if ($method !== '' && !in_array($method, self::REDIRECTABLE_METHODS, true)) {
            return true;
        }

        $requestPath = self::relativePath($requestUri, $baseUrl);
        foreach (self::normalizePrefixes($skipPrefixes) as $prefix) {
            if ($requestPath === $prefix || str_starts_with($requestPath, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Request path relative to the site base URL, without surrounding slashes.
     */
    public static function relativePath(string $requestUri, string $baseUrl): string
    {
        // Not parse_url(): it reads "//api/x" as a host, and REQUEST_URI is always a path.
        $path = explode('#', explode('?', $requestUri, 2)[0], 2)[0];
        $path = (string)preg_replace('#/{2,}#', '/', $path);
        $base = '/' . trim($baseUrl, '/') . '/';

        if ($base !== '//' && str_starts_with($path . '/', $base)) {
            $path = substr($path, strlen($base) - 1);
        }

        return trim($path, '/');
    }

    /**
     * @param array<int, mixed> $prefixes
     * @return string[]
     */
    public static function normalizePrefixes(array $prefixes): array
    {
        $normalized = [];
        foreach ($prefixes as $prefix) {
            if (!is_scalar($prefix)) {
                continue;
            }

            $prefix = trim((string)$prefix, " \t\n\r\0\x0B/");
            if ($prefix !== '') {
                $normalized[] = $prefix;
            }
        }

        return array_values(array_unique($normalized));
    }
}
