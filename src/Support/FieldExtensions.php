<?php namespace Seiger\sSeo\Support;

use Throwable;

/**
 * Lets other packages add their own block under the SEO fields of a resource
 * (keyword research, suggested titles, checks) without forking the form.
 *
 * The form fires `sSeoFieldsRender` with the resource, its language and domain and
 * the values currently in the fields; every listener may return an HTML string, shown
 * under the meta description. Listeners run on the server inside the manager, like
 * `OnDocFormRender`, and escape what they output. A listener that throws is skipped so
 * the SEO fields always render.
 */
class FieldExtensions
{
    public const EVENT = 'sSeoFieldsRender';

    /** Fields a listener receives; the rest of the row is internal. */
    public const FIELDS = ['meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'robots'];

    /**
     * @param array<string, mixed> $vars the view's variables
     * @param (callable(string, array): mixed)|null $dispatch fires the event; Evolution's `invokeEvent()` by default
     */
    public static function render(array $vars, ?callable $dispatch = null): string
    {
        $context = self::context($vars);

        if ($context['resource_id'] <= 0) {
            return '';
        }

        $dispatch ??= static fn (string $event, array $params) => evo()->invokeEvent($event, $params);

        try {
            return self::join($dispatch(self::EVENT, $context));
        } catch (Throwable $e) {
            if (function_exists('evo')) {
                try {
                    evo()->logEvent(0, 3, self::EVENT . ': ' . $e->getMessage(), 'sSeo');
                } catch (Throwable) {
                }
            }

            return '';
        }
    }

    /**
     * @param array<string, mixed> $vars
     * @return array{resource_id: int, resource_type: string, lang: string, domain_key: string, fields: array<string, string>}
     */
    public static function context(array $vars): array
    {
        $fields = [];

        foreach (self::FIELDS as $field) {
            $fields[$field] = is_scalar($vars[$field] ?? null) ? (string) $vars[$field] : '';
        }

        $type = trim((string) ($vars['resource_type'] ?? ''));
        $lang = trim((string) ($vars['lang'] ?? ''));
        $domain = trim((string) ($vars['domain_key'] ?? ''));

        return [
            'resource_id' => (int) ($vars['resource_id'] ?? 0),
            'resource_type' => $type !== '' ? $type : 'document',
            'lang' => $lang !== '' ? $lang : 'base',
            'domain_key' => $domain !== '' ? $domain : 'default',
            'fields' => $fields,
        ];
    }

    /** The listeners' strings in order; `false` (nobody listens), nulls and blanks give nothing. */
    public static function join(mixed $results): string
    {
        if (is_string($results)) {
            $results = [$results];
        }

        if (!is_array($results)) {
            return '';
        }

        $parts = [];

        foreach ($results as $result) {
            if (is_string($result) && trim($result) !== '') {
                $parts[] = $result;
            }
        }

        return implode("\n", $parts);
    }
}
