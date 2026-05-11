<?php namespace Seiger\sSeo\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Seiger\sSeo\Models\sRedirect;

class RedirectsTableData
{
    public function __construct(
        protected array $context = [],
        protected array $state = [],
        protected array $config = [],
    ) {
    }

    public function total(): int
    {
        return (clone $this->redirectsQuery())->toBase()->getCountForPagination();
    }

    public function rows(int $page, int $perPage): array
    {
        return $this->redirectRows(
            $this->redirectsQuery()
                ->forPage(max(1, $page), max(1, $perPage))
                ->get()
        );
    }

    public function filterGroups(): array
    {
        return [];
    }

    public function modalDefaults(): array
    {
        return [
            'old_url' => '',
            'new_url' => '',
            'type' => '301',
            'site_key' => 'all',
        ];
    }

    public function modalData(int $redirectId): array
    {
        $redirect = sRedirect::query()->find($redirectId);

        if (!$redirect) {
            return $this->modalDefaults();
        }

        return [
            'old_url' => (string) $redirect->old_url,
            'new_url' => (string) $redirect->new_url,
            'type' => (string) $redirect->type,
            'site_key' => (string) $redirect->site_key,
        ];
    }

    public function saveModal(array $data, ?int $redirectId = null, string $mode = 'create'): int
    {
        $redirect = $redirectId ? sRedirect::query()->find($redirectId) : new sRedirect();

        if (!$redirect) {
            $redirect = new sRedirect();
        }

        $siteKey = trim((string) data_get($data, 'site_key', 'all')) ?: 'all';
        $oldUrl = ltrim(trim((string) data_get($data, 'old_url', '')), '/');
        $newUrl = trim((string) data_get($data, 'new_url', ''));
        $type = (int) data_get($data, 'type', 301);

        $duplicate = sRedirect::query()
            ->where('old_url', $oldUrl)
            ->whereIn('site_key', [$siteKey, 'all'])
            ->when($redirect->exists, fn (Builder $query) => $query->whereKeyNot((int) $redirect->id))
            ->exists();

        if ($duplicate) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'modalData.old_url' => __('sSeo::global.redirect_exists', ['uri' => $oldUrl]),
            ]);
        }

        $redirect->site_key = $siteKey;
        $redirect->old_url = $oldUrl;
        $redirect->new_url = $newUrl;
        $redirect->type = in_array($type, [301, 302, 307], true) ? $type : 301;
        $redirect->save();

        evo()->clearCache('full');

        return (int) $redirect->id;
    }

    public function deleteName(int $redirectId): string
    {
        return (string) sRedirect::query()->whereKey($redirectId)->value('old_url');
    }

    public function deleteRow(int $redirectId): ?string
    {
        sRedirect::query()->whereKey($redirectId)->delete();
        evo()->clearCache('full');

        return null;
    }

    public function modalOptionsForField(string|array $field): array
    {
        $name = is_array($field) ? (string) ($field['name'] ?? '') : $field;

        if ($name !== 'site_key') {
            return [];
        }

        return collect([['value' => 'all', 'label' => 'All']])
            ->merge($this->availableSites())
            ->values()
            ->all();
    }

    protected function redirectsQuery(): Builder
    {
        $query = sRedirect::query();
        $search = trim((string) $this->state('search', ''));

        if ($search !== '') {
            $like = '%' . addcslashes(mb_strtolower($search), '\\%_') . '%';
            $query->where(function (Builder $where) use ($query, $like, $search) {
                foreach (['old_url', 'new_url', 'site_key', 'type'] as $column) {
                    $where->orWhereRaw('LOWER(' . $query->getGrammar()->wrap($column) . ') LIKE ?', [$like]);
                }

                if (ctype_digit($search)) {
                    $where->orWhere('id', (int) $search);
                }
            });
        }

        if ($this->applySort($query)) {
            return $query->orderBy('id');
        }

        return $query->orderBy('old_url')->orderBy('id');
    }

    protected function redirectRows(Collection $redirects): array
    {
        return $redirects
            ->map(fn (sRedirect $redirect): array => [
                'id' => (int) $redirect->id,
                'wire_key' => 'sseo-redirect-row-' . $redirect->id,
                'old_url' => (string) $redirect->old_url,
                'new_url' => (string) $redirect->new_url,
                'type' => [
                    'label' => (string) $redirect->type,
                    'tone' => (int) $redirect->type === 301 ? 'success' : 'info',
                ],
                'site_key' => [
                    'label' => (string) $redirect->site_key,
                    'tone' => (string) $redirect->site_key === 'all' ? 'neutral' : 'primary',
                ],
                'updated_at' => optional($redirect->updated_at)->format('Y-m-d H:i'),
            ])
            ->values()
            ->all();
    }

    protected function applySort(Builder $query): bool
    {
        $key = (string) $this->state('sort', $this->config['default_sort'] ?? '');

        if ($key === '') {
            return false;
        }

        $column = collect($this->config['columns'] ?? [])
            ->first(fn ($column) => ($column['key'] ?? null) === $key && ($column['sortable'] ?? false));

        if (!is_array($column)) {
            return false;
        }

        $field = (string) ($column['sort_field'] ?? '');

        if ($field === '') {
            return false;
        }

        $direction = strtolower((string) $this->state('direction', $this->config['default_direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($field, $direction);

        return true;
    }

    protected function availableSites(): array
    {
        if (!class_exists(\Seiger\sMultisite\Models\sMultisite::class) || !evo()->getConfig('check_sMultisite', false)) {
            return [];
        }

        return \Seiger\sMultisite\Models\sMultisite::query()
            ->orderBy('site_name')
            ->get()
            ->map(fn ($site): array => [
                'value' => (string) $site->key,
                'label' => (string) ($site->site_name ?: $site->key),
            ])
            ->all();
    }

    protected function state(string $key, mixed $default = null): mixed
    {
        return data_get($this->state, $key, $default);
    }
}
