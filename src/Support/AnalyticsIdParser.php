<?php namespace Seiger\sSeo\Support;

/**
 * Parse comma-separated Analytics IDs (GTM / GA4) with strict validation.
 */
class AnalyticsIdParser
{
    public const GTM_REGEX = '/^GTM-[A-Z0-9]+$/i';
    public const GA4_REGEX = '/^G-[A-Z0-9]+$/i';

    /**
     * @param string $raw
     * @return string[]
     */
    public static function parseGtmIds(string $raw): array
    {
        return self::parseValidOnly($raw, self::GTM_REGEX, static fn(string $v): string => strtoupper($v));
    }

    /**
     * @param string $raw
     * @return string[]
     */
    public static function parseGa4Ids(string $raw): array
    {
        return self::parseValidOnly($raw, self::GA4_REGEX, static fn(string $v): string => strtoupper($v));
    }

    /**
     * Strict parse (for admin saves): returns both valid and invalid tokens.
     *
     * @param string $raw
     * @return array{valid: string[], invalid: string[]}
     */
    public static function parseGtmStrict(string $raw): array
    {
        return self::parseStrict($raw, self::GTM_REGEX, static fn(string $v): string => strtoupper($v));
    }

    /**
     * Strict parse (for admin saves): returns both valid and invalid tokens.
     *
     * @param string $raw
     * @return array{valid: string[], invalid: string[]}
     */
    public static function parseGa4Strict(string $raw): array
    {
        return self::parseStrict($raw, self::GA4_REGEX, static fn(string $v): string => strtoupper($v));
    }

    /**
     * @param string $raw
     * @param string $regex
     * @param callable(string):string $normalize
     * @return string[]
     */
    protected static function parseValidOnly(string $raw, string $regex, callable $normalize): array
    {
        return self::parseStrict($raw, $regex, $normalize)['valid'];
    }

    /**
     * @param string $raw
     * @param string $regex
     * @param callable(string):string $normalize
     * @return array{valid: string[], invalid: string[]}
     */
    protected static function parseStrict(string $raw, string $regex, callable $normalize): array
    {
        $raw = (string)$raw;
        if (trim($raw) === '') {
            return ['valid' => [], 'invalid' => []];
        }

        $valid = [];
        $invalid = [];
        $seen = [];

        foreach (explode(',', $raw) as $token) {
            $token = trim((string)$token);
            if ($token === '') {
                continue;
            }

            if (!preg_match($regex, $token)) {
                $invalid[] = $token;
                continue;
            }

            $id = (string)$normalize($token);
            $dedupKey = strtolower($id);
            if (isset($seen[$dedupKey])) {
                continue;
            }

            $seen[$dedupKey] = true;
            $valid[] = $id;
        }

        return ['valid' => $valid, 'invalid' => $invalid];
    }
}

