<?php namespace Seiger\sSeo\Livewire;

use Livewire\Component;

class ModulePanel extends Component
{
    public array $rawTabs = [];
    public array $context = [];
    public string $activeTab = 'dashboard';

    public function mount(array $tabs = [], string $activeTab = 'dashboard', array $context = []): void
    {
        $this->rawTabs = $tabs;
        $this->context = $context;
        $this->activeTab = $this->normalizeTab($activeTab);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $this->normalizeTab($tab);
    }

    public function render()
    {
        return view('sSeo::livewire.module-panel', [
            'tabs' => $this->navigationTabs(),
            'activeTab' => $this->activeTab,
            'preset' => $this->preset(),
            'title' => $this->title(),
            'context' => $this->context,
        ]);
    }

    protected function normalizeTab(string $tab): string
    {
        $tab = trim($tab);

        if ($tab === 'analytics') {
            $tab = 'configure';
        }

        $allowed = collect($this->rawTabs)
            ->pluck('key')
            ->filter(fn ($key) => is_string($key) && $key !== '')
            ->values()
            ->all();

        return in_array($tab, $allowed, true) ? $tab : ($allowed[0] ?? 'dashboard');
    }

    protected function navigationTabs(): array
    {
        return collect($this->rawTabs)
            ->map(function (array $tab) {
                $key = (string) ($tab['key'] ?? '');
                $tab['active'] = $key === $this->activeTab;
                $tab['type'] = 'wire';
                $tab['method'] = 'switchTab';
                $tab['argument'] = $key;
                unset($tab['href'], $tab['data']);

                return $tab;
            })
            ->values()
            ->all();
    }

    protected function title(): string
    {
        $tab = collect($this->rawTabs)->first(fn (array $item): bool => ($item['key'] ?? '') === $this->activeTab);

        return (string) ($tab['label'] ?? __('sSeo::global.title'));
    }

    protected function preset(): string
    {
        return match ($this->activeTab) {
            'configure' => 'sseo.settings',
            'redirects' => 'sseo.redirects',
            default => '',
        };
    }
}
