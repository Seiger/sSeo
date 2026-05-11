<?php namespace Seiger\sSeo\Livewire;

use EvoUI\Support\RichTextEditor;
use Livewire\Component;

class RobotsEditor extends Component
{
    public array $items = [];
    public bool $saved = false;
    public bool $dirty = false;

    public function mount(): void
    {
        $this->items = $this->loadItems();
    }

    public function save(): void
    {
        $this->resetErrorBag();
        $this->saved = false;

        foreach ($this->items as $index => $item) {
            $content = (string) ($item['content'] ?? '');

            if (trim($content) === '') {
                $this->addError('items.' . $index . '.content', __('sSeo::global.robots_text_empty'));
                return;
            }
        }

        foreach ($this->items as $index => $item) {
            $path = (string) ($item['target'] ?? '');

            if (!$this->writeFile($path, (string) ($item['content'] ?? ''))) {
                $this->addError('items.' . $index . '.content', __('sSeo::global.not_writable', ['file' => $path]));
                return;
            }
        }

        $this->saved = true;
        $this->dirty = false;

        if (function_exists('evo')) {
            try {
                evo()->clearCache('full');
            } catch (\Throwable) {
                // The editor still saves in light CLI/demo contexts where evo() is not booted.
            }
        }

        $this->dispatch('evo-ui:form.saved', preset: 'sseo.robots');
        $this->dispatch('sseo:robots.saved');
    }

    public function updatedItems(mixed $value = null, ?string $key = null): void
    {
        $this->saved = false;
        $this->dirty = true;
    }

    public function render()
    {
        return view('sSeo::livewire.robots-editor', [
            'editorHtml' => $this->editorHtml(),
            'legacyCodeMirrorBaseUrl' => $this->legacyCodeMirrorBaseUrl(),
        ]);
    }

    protected function loadItems(): array
    {
        $sites = $this->multisiteSites();

        if ($sites === []) {
            return [$this->item('robots', $this->evoConfig('site_name', 'Current website'), $this->basePath('robots.txt'))];
        }

        return array_map(function (array $site): array {
            $key = (string) $site['key'];
            $target = $this->storagePath($key . DIRECTORY_SEPARATOR . 'robots.txt');
            $fallback = is_file($target) ? $target : $this->basePath('robots.txt');
            $label = trim((string) ($site['site_name'] ?? '')) ?: $key;

            return $this->item($key . '_robots', $label . ' (' . $key . ')', $target, $fallback);
        }, $sites);
    }

    protected function item(string $key, string $label, string $target, ?string $source = null): array
    {
        $source = $source ?: $target;

        return [
            'key' => $key,
            'label' => $label,
            'target' => $target,
            'editor_id' => 'sseo_robots_' . preg_replace('/[^A-Za-z0-9_]/', '_', $key),
            'content' => $this->readFile($source),
        ];
    }

    protected function editorHtml(): string
    {
        $ids = collect($this->items)
            ->pluck('editor_id')
            ->filter()
            ->values()
            ->all();

        if ($ids === [] || !function_exists('evo')) {
            return '';
        }

        try {
            $html = RichTextEditor::html(
                ids: $ids,
                height: '560px',
                editor: 'Codemirror',
                options: [],
                contentType: 'htmlmixed',
            );

            return trim($html) !== '' ? $html : $this->legacyCodeMirrorHtml($ids);
        } catch (\Throwable) {
            return $this->legacyCodeMirrorHtml($ids);
        }
    }

    protected function legacyCodeMirrorHtml(array $ids): string
    {
        $baseUrl = $this->legacyCodeMirrorBaseUrl();
        $elements = json_encode(array_values($ids), JSON_UNESCAPED_SLASHES);

        if ($elements === false) {
            return '';
        }

        return <<<HTML
<link rel="stylesheet" href="{$baseUrl}cm/lib/codemirror.css">
<link rel="stylesheet" href="{$baseUrl}cm/addon.css">
<link rel="stylesheet" href="{$baseUrl}cm/theme/default.css">
<link rel="stylesheet" href="{$baseUrl}cm/theme/one-dark.css">
<script src="{$baseUrl}cm/lib/codemirror-compressed.js"></script>
<script src="{$baseUrl}cm/mode/xml-compressed.js"></script>
<script src="{$baseUrl}cm/mode/javascript-compressed.js"></script>
<script src="{$baseUrl}cm/mode/css-compressed.js"></script>
<script src="{$baseUrl}cm/mode/htmlmixed-compressed.js"></script>
<script src="{$baseUrl}cm/addon-compressed.js"></script>
<script>
    (() => {
        const elements = {$elements};
        const init = () => {
            if (!window.CodeMirror) {
                return;
            }

            window.myCodeMirrors = window.myCodeMirrors || {};

            elements.forEach((id) => {
                const textarea = document.getElementById(id) || document.getElementsByName(id)[0];

                if (!textarea || window.myCodeMirrors[id]) {
                    return;
                }

                window.myCodeMirrors[id] = window.CodeMirror.fromTextArea(textarea, {
                    mode: 'htmlmixed',
                    theme: document.documentElement.dataset.themeMode === 'dark' ? 'one-dark' : 'default',
                    lineNumbers: true,
                    lineWrapping: true,
                    matchBrackets: true,
                    indentUnit: 4,
                    tabSize: 4,
                    viewportMargin: Infinity,
                });

                window.myCodeMirrors[id].setSize('100%', '560px');
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init, { once: true });
        } else {
            init();
        }

        document.addEventListener('livewire:navigated', init);
    })();
</script>
HTML;
    }

    protected function legacyCodeMirrorBaseUrl(): string
    {
        return rtrim((defined('EVO_SITE_URL') ? EVO_SITE_URL : '/'), '/') . '/assets/plugins/codemirror/';
    }

    protected function readFile(string $path): string
    {
        if (is_file($path)) {
            return (string) file_get_contents($path);
        }

        $sample = $this->basePath('sample-robots.txt');

        return is_file($sample) ? (string) file_get_contents($sample) : '';
    }

    protected function writeFile(string $path, string $content): bool
    {
        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, $this->folderPermissions(), true) && !is_dir($dir)) {
            return false;
        }

        if (!is_writable($dir) || (is_file($path) && !is_writable($path))) {
            return false;
        }

        return file_put_contents($path, $content) !== false;
    }

    protected function multisiteSites(): array
    {
        if (!function_exists('evo')) {
            return [];
        }

        try {
            if (!evo()->getConfig('check_sMultisite', false)) {
                return [];
            }
        } catch (\Throwable) {
            return [];
        }

        $model = \Seiger\sMultisite\Models\sMultisite::class;

        if (!class_exists($model)) {
            return [];
        }

        $out = [];

        foreach ($model::all() as $site) {
            $key = trim((string) ($site->key ?? ''));

            if ($key === '') {
                continue;
            }

            $out[] = [
                'key' => $key,
                'site_name' => (string) ($site->site_name ?? ''),
            ];
        }

        return $out;
    }

    protected function basePath(string $path = ''): string
    {
        return (defined('EVO_BASE_PATH') ? EVO_BASE_PATH : getcwd() . DIRECTORY_SEPARATOR) . $path;
    }

    protected function storagePath(string $path = ''): string
    {
        return (defined('EVO_STORAGE_PATH') ? EVO_STORAGE_PATH : $this->basePath('storage' . DIRECTORY_SEPARATOR)) . $path;
    }

    protected function folderPermissions(): int
    {
        return octdec((string) $this->evoConfig('new_folder_permissions', '0777'));
    }

    protected function evoConfig(string $key, mixed $default = null): mixed
    {
        if (!function_exists('evo')) {
            return $default;
        }

        try {
            return evo()->getConfig($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}
