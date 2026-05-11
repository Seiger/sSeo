<?php namespace Seiger\sSeo\Tables;

use Illuminate\Support\Collection;

class ActivityTableData
{
    public function __construct(
        protected array $context = [],
        protected array $state = [],
        protected array $config = [],
    ) {
    }

    public function total(): int
    {
        return $this->activityRows()->count();
    }

    public function rows(int $page, int $perPage): array
    {
        return $this->activityRows()
            ->forPage(max(1, $page), max(1, $perPage))
            ->values()
            ->all();
    }

    public function filterGroups(): array
    {
        return [];
    }

    protected function activityRows(): Collection
    {
        $rows = collect((array) data_get($this->context, 'activity', []))
            ->values()
            ->map(fn (array $item, int $index): array => [
                'id' => $index + 1,
                'wire_key' => 'sseo-activity-row-' . ($index + 1),
                'label' => (string) ($item['label'] ?? ''),
                'summary' => (string) ($item['summary'] ?? ''),
                'meta' => (string) ($item['meta'] ?? ''),
                'timestamp' => (int) ($item['timestamp'] ?? 0),
            ]);

        $search = mb_strtolower(trim((string) data_get($this->state, 'search', '')));

        if ($search !== '') {
            $rows = $rows->filter(function (array $row) use ($search): bool {
                $haystack = mb_strtolower(trim($row['label'] . ' ' . $row['summary'] . ' ' . $row['meta']));

                return str_contains($haystack, $search);
            });
        }

        [$field, $direction] = $this->sort();

        return $rows
            ->sortBy(fn (array $row) => $row[$field] ?? '', SORT_REGULAR, $direction === 'desc')
            ->values();
    }

    protected function sort(): array
    {
        $key = (string) data_get($this->state, 'sort', $this->config['default_sort'] ?? 'meta');
        $column = collect($this->config['columns'] ?? [])
            ->first(fn ($column) => ($column['key'] ?? null) === $key && ($column['sortable'] ?? false));
        $field = is_array($column) ? (string) ($column['sort_field'] ?? $key) : 'timestamp';
        $direction = strtolower((string) data_get($this->state, 'direction', $this->config['default_direction'] ?? 'desc'));

        return [$field ?: 'timestamp', $direction === 'asc' ? 'asc' : 'desc'];
    }
}
