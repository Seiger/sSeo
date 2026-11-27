<?php namespace Seiger\sSeo\Support;

use Throwable;

/**
 * Lets other packages add their own block under the SEO fields of a resource
 * (keyword research, suggested titles, checks) without forking the form.
 *
 * The form fires `sSeoFieldsRender` with the resource, its language, the domain being
 * edited and the values currently in the fields; every listener (`Event::listen()`) may
 * return an HTML string, shown under the meta description. Listeners run on the server
 * inside the manager, like `OnDocFormRender`, and escape what they output.
 *
 * Each listener is called on its own: one that throws is skipped and logged, and the
 * blocks of the others are still shown, so one broken package cannot hide the rest or
 * the SEO fields.
 */
class FieldExtensions
{
    public const EVENT = 'sSeoFieldsRender';

    /** Fields a listener receives; the rest of the row is internal. */
    public const FIELDS = ['meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'robots'];

    /**
     * @param array<string, mixed> $vars the view's variables
     * @param (callable(): iterable<callable(array): mixed>)|null $listeners the listeners, each taking the
     *        event's parameters; those registered for `evolution.sSeoFieldsRender` by default
     */
    public static function render(array $vars, ?callable $listeners = null): string
    {
        $context = self::context($vars);

        if ($context['resource_id'] <= 0) {
            return '';
        }

        try {
            $callbacks = $listeners !== null ? $listeners() : self::registered();
        } catch (Throwable $e) {
            self::log($e);

            return '';
        }

        $results = [];

        foreach ($callbacks as $callback) {
            try {
                $results[] = $callback($context);
            } catch (Throwable $e) {
                self::log($e);
            }
        }

        return self::join($results);
    }

    /**
     * The values of the row shown in the form, under the identity of the resource being
     * edited. The row may be a fallback (another domain's, or none), so the resource and
     * the domain are never taken from it.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function identify(array $row, int $resourceId, string $resourceType, string $domainKey): array
    {
        return array_replace($row, ['resource_id' => $resourceId, 'resource_type' => $resourceType, 'domain_key' => $domainKey]);
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

    /** The listeners' strings in order; nulls, blanks and anything that is not a string give nothing. */
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

    /**
     * The `Event::listen('evolution.sSeoFieldsRender', ...)` listeners, each callable alone.
     * The dispatcher would stop at the first exception, so it is not used to call them.
     *
     * @return array<int, callable(array): mixed>
     */
    private static function registered(): array
    {
        $event = 'evolution.' . self::EVENT;

        return array_map(
            static fn (callable $listener) => static fn (array $params) => $listener($event, [$params]),
            evo()['events']->getListeners($event)
        );
    }

    private static function log(Throwable $e): void
    {
        if (!function_exists('evo')) {
            return;
        }

        try {
            evo()->logEvent(0, 3, self::EVENT . ': ' . $e->getMessage(), 'sSeo');
        } catch (Throwable) {
            // Logging must not break the form either.
        }
    }
}
