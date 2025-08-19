<?php namespace Seiger\sSeo\Support;

/**
 * Class MetaBuilder
 *
 * Collects SEO meta data for the current Evolution CMS resource
 * and builds a compact <head> HTML fragment to be injected into
 * the final document output. Focused on performance:
 * - At most ONE optional DB query to sSeoModel
 * - Lightweight string/regex checks within <head>...</head> only
 * - Optional OG/Twitter/hreflang generation controlled by config
 */
class MetaBuilder
{
    /**
     * Build a compact <head> HTML fragment with required tags.
     * Mode 'replace' removes collisions first; 'fill' adds only missing tags.
     *
     * @param array  $meta            Fields of current Document
     * @param string $documentOutput  Full HTML output (used to inspect existing head tags)
     * @return string                 HTML fragment to inject (may be empty)
     */
    public static function buildHeadHtml(array $meta, string $documentOutput): string
    {
        if (empty($meta)) {
            return '';
        }

        $mode = strtolower((string)config('seiger.settings.sSeo.meta_tags_mode', 'replace'));
        if ($mode !== 'fill' && $mode !== 'replace') {
            $mode = 'replace';
        }

        $headOpen = stripos($documentOutput, '<head');
        if ($headOpen === false) {
            return '';
        }
        $headClose = stripos($documentOutput, '</head>', $headOpen);
        if ($headClose === false) {
            return '';
        }
        $headPart = substr($documentOutput, $headOpen, $headClose - $headOpen);

        if ($mode === 'replace') {
            $headPart = self::stripExisting($headPart);
        }

        $has = static fn (string $re): bool => (bool)preg_match($re, $headPart);
        $esc = static fn ($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $lines = [];

        // Title
        if ($mode === 'replace' || !$has('/<title\b[^>]*>.*?<\/title>/is')) {
            if ($meta['title'] !== '') {
                $lines[] = '<title>'.$esc($meta['title']).'</title>';
            }
        }
        // Description
        if ($mode === 'replace' || !$has('/<meta\b[^>]*name=["\']description["\'][^>]*>/i')) {
            if ($meta['description'] !== '') {
                $lines[] = '<meta name="description" content="'.$esc($meta['description']).'">';
            }
        }
        // Keywords (optional)
        if ($mode === 'replace' || !$has('/<meta\b[^>]*name=["\']keywords["\'][^>]*>/i')) {
            if ($meta['keywords'] !== '') {
                $lines[] = '<meta name="keywords" content="'.$esc($meta['keywords']).'">';
            }
        }
        // Robots
        if ($mode === 'replace' || !$has('/<meta\b[^>]*name=["\']robots["\'][^>]*>/i')) {
            if ($meta['robots'] !== '') {
                $lines[] = '<meta name="robots" content="'.$esc($meta['robots']).'">';
            }
        }
        // Canonical
        if ($mode === 'replace' || !$has('/<link\b[^>]*rel=["\']canonical["\'][^>]*>/i')) {
            if ($meta['canonical'] !== '') {
                $lines[] = '<link rel="canonical" href="'.$esc($meta['canonical']).'">';
            }
        }

        // Open Graph
        /*if ((bool)self::cfg('sseo.head_og', true)) {
            $og = $meta['og'] ?? [];
            if (!empty($og['title']))       $lines[] = '<meta property="og:title" content="'.$esc($og['title']).'">';
            if (!empty($og['description'])) $lines[] = '<meta property="og:description" content="'.$esc($og['description']).'">';
            if (!empty($og['url']))         $lines[] = '<meta property="og:url" content="'.$esc($og['url']).'">';
            if (!empty($og['type']))        $lines[] = '<meta property="og:type" content="'.$esc($og['type']).'">';
            if (!empty($og['image']))       $lines[] = '<meta property="og:image" content="'.$esc($og['image']).'">';
        }

        // Twitter
        if ((bool)self::cfg('sseo.head_twitter', true)) {
            $tw = $meta['twitter'] ?? [];
            if (!empty($tw['card']))        $lines[] = '<meta name="twitter:card" content="'.$esc($tw['card']).'">';
            if (!empty($tw['title']))       $lines[] = '<meta name="twitter:title" content="'.$esc($tw['title']).'">';
            if (!empty($tw['description'])) $lines[] = '<meta name="twitter:description" content="'.$esc($tw['description']).'">';
            if (!empty($tw['image']))       $lines[] = '<meta name="twitter:image" content="'.$esc($tw['image']).'">';
        }*/

        // hreflang (if provided)
        /*if (!empty($meta['hreflang']) && \is_array($meta['hreflang'])) {
            foreach ($meta['hreflang'] as $lang => $url) {
                $re = '/<link\b[^>]*rel=["\']alternate["\'][^>]*hreflang=["\']'
                    . \preg_quote((string)$lang, '/')
                    . '["\'][^>]*>/i';
                if ($mode === 'replace' || !$has($re)) {
                    $lines[] = '<link rel="alternate" hreflang="' . $esc($lang) . '" href="' . $esc($url) . '">';
                }
            }
        }*/

        return implode("\n", $lines);
    }

    /**
     * Remove colliding tags from a <head> chunk in 'replace' mode.
     *
     * @param string $headPart
     * @return string
     */
    protected static function stripExisting(string $headPart): string
    {
        $patterns = [
            '/<title\b[^>]*>.*?<\/title>/is',
            '/<meta\b[^>]*name=["\']description["\'][^>]*>/i',
            '/<meta\b[^>]*name=["\']keywords["\'][^>]*>/i',
            '/<meta\b[^>]*name=["\']robots["\'][^>]*>/i',
            '/<link\b[^>]*rel=["\']canonical["\'][^>]*>/i',
            '/<meta\b[^>]*property=["\']og:[^"\']+["\'][^>]*>/i',
            '/<meta\b[^>]*name=["\']twitter:[^"\']+["\'][^>]*>/i',
            '/<link\b[^>]*rel=["\']alternate["\'][^>]*hreflang=["\'][^"\']+["\'][^>]*>/i',
        ];
        return preg_replace($patterns, '', $headPart);
    }

    /**
     * Normalize robots tokens to a canonical, deduplicated form.
     *
     * @param string $v
     * @return string
     */
    protected static function normalizeRobots(string $v): string
    {
        if ($v === '') {
            return '';
        }
        $parts = preg_split('/[,\s]+/', strtolower($v), -1, PREG_SPLIT_NO_EMPTY);
        $uniq = [];
        foreach ($parts as $p) {
            $uniq[$p] = true;
        }
        // Ensure "index|noindex" and "follow|nofollow" pairs are not both missing
        if (isset($uniq['noindex']) && !isset($uniq['follow']) && !isset($uniq['nofollow'])) {
            $uniq['follow'] = true;
        }
        if (isset($uniq['nofollow']) && !isset($uniq['index']) && !isset($uniq['noindex'])) {
            $uniq['index'] = true;
        }
        return implode(',', array_keys($uniq));
    }

    /**
     * Find the first image URL in HTML (scan up to ~64KB for speed).
     *
     * @param string $html
     * @return string
     */
    protected static function firstImageUrl(string $html): string
    {
        if ($html === '') {
            return '';
        }
        $chunk = substr($html, 0, 65536);
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $chunk, $m)) {
            return trim($m[1]);
        }
        return '';
    }
}
