<?php namespace Seiger\sSeo\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Seiger\sLang\Facades\sLang;

class MetaTemplatesEditor extends Component
{
    public array $sections = [];
    public bool $saved = false;

    public function mount(): void
    {
        $this->sections = $this->templateSections();
    }

    public function save(): void
    {
        $this->saved = false;

        foreach ($this->sections as $section) {
            foreach ((array) ($section['fields'] ?? []) as $field) {
                $key = (string) ($field['key'] ?? '');

                if (!str_starts_with($key, 'sseo_')) {
                    continue;
                }

                $value = (string) ($field['value'] ?? '');
                if (function_exists('removeSanitizeSeed')) {
                    $value = removeSanitizeSeed($value);
                }

                DB::table('system_settings')->updateOrInsert(
                    ['setting_name' => $key],
                    ['setting_value' => $value]
                );

                if (function_exists('evo')) {
                    evo()->setConfig($key, $value);
                }
            }
        }

        if (function_exists('evo')) {
            evo()->clearCache('full');
        }

        $this->saved = true;
        $this->dispatch('sseo:templates.saved');
    }

    public function render()
    {
        return view('sSeo::livewire.meta-templates-editor');
    }

    protected function templateSections(): array
    {
        $sections = [$this->resourceSection('document', 'sSeo::global.type_a_document', 'file-text')];

        if ($this->evoConfig('check_sCommerce', false)) {
            $sections[] = $this->resourceSection('prodcat', 'sSeo::global.type_a_prodcat', 'store');
            $sections[] = $this->resourceSection('product', 'sSeo::global.type_a_product', 'shopping-bag', $this->productPlaceholders());
        }

        return $sections;
    }

    protected function resourceSection(string $type, string $label, string $icon, string $placeholderMore = ''): array
    {
        return [
            'key' => $type,
            'label' => $label,
            'icon' => $icon,
            'placeholder_more' => $placeholderMore,
            'fields' => $this->templateFields($type),
        ];
    }

    protected function templateFields(string $type): array
    {
        $fields = [];

        foreach ($this->langs() as $lang) {
            $fields[] = $this->field($type, $lang, 'meta_title', 'sSeo::global.meta_title', 'sSeo::global.meta_title_help', '[*pagetitle*] - [(site_name)]');
            $fields[] = $this->field($type, $lang, 'meta_description', 'sSeo::global.meta_description', 'sSeo::global.meta_description_help', '[*pagetitle*] - [(site_name)]');
            $fields[] = $this->field($type, $lang, 'meta_keywords', 'sSeo::global.meta_keywords', 'sSeo::global.meta_keywords_help', '[*pagetitle*], [*longtitle*]');
        }

        return $fields;
    }

    protected function field(string $type, string $lang, string $name, string $label, string $help, string $default): array
    {
        $key = 'sseo_' . $name . '_' . $type . '_' . $lang;

        return [
            'key' => $key,
            'lang' => $lang,
            'label' => $label,
            'help' => $help,
            'hint' => $default,
            'value' => (string) $this->evoConfig($key, $default),
        ];
    }

    protected function langs(): array
    {
        if (!$this->evoConfig('check_sLang', false)) {
            return ['base'];
        }

        try {
            return array_values((array) sLang::langConfig());
        } catch (\Throwable) {
            return ['base'];
        }
    }

    protected function productPlaceholders(): string
    {
        $placeholders = ['[*sku*]', '[*rating*]', '[*price*]'];
        $aliases = config('seiger.settings.sSeo.product_attribute_aliases', []);

        if (is_string($aliases)) {
            $aliases = array_map('trim', explode(',', $aliases));
        }

        foreach ((array) $aliases as $alias) {
            $alias = trim((string) $alias);
            if ($alias !== '') {
                $placeholders[] = '[*' . $alias . '*]';
            }
        }

        return ', ' . implode(', ', array_values(array_unique($placeholders)));
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
