<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RobotsEditorContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_robots_editor_livewire_component_uses_file_backed_contract(): void
    {
        $component = $this->read('src/Livewire/RobotsEditor.php');

        $this->assertStringContainsString('class RobotsEditor extends Component', $component);
        $this->assertStringContainsString('public array $items = [];', $component);
        $this->assertStringContainsString('public bool $dirty = false;', $component);
        $this->assertStringContainsString('public function updatedItems(mixed $value = null, ?string $key = null): void', $component);
        $this->assertStringContainsString('public function save(): void', $component);
        $this->assertStringContainsString("evo()->getConfig('check_sMultisite', false)", $component);
        $this->assertStringContainsString('\\Seiger\\sMultisite\\Models\\sMultisite::class', $component);
        $this->assertStringContainsString("basePath('robots.txt')", $component);
        $this->assertStringContainsString("basePath('sample-robots.txt')", $component);
        $this->assertStringContainsString('EVO_STORAGE_PATH', $component);
        $this->assertStringContainsString('file_put_contents($path, $content)', $component);
        $this->assertStringContainsString("__('sSeo::global.robots_text_empty')", $component);
        $this->assertStringContainsString("dispatch('evo-ui:form.saved', preset: 'sseo.robots')", $component);
        $this->assertStringContainsString('use EvoUI\\Support\\RichTextEditor;', $component);
        $this->assertStringContainsString("'editor_id' => 'sseo_robots_'", $component);
        $this->assertStringContainsString("editor: 'Codemirror'", $component);
        $this->assertStringContainsString('protected function legacyCodeMirrorHtml(array $ids): string', $component);
        $this->assertStringContainsString('/assets/plugins/codemirror/', $component);
        $this->assertStringContainsString('window.CodeMirror.fromTextArea', $component);
        $this->assertStringContainsString('lineNumbers: true', $component);
    }

    public function test_robots_editor_view_uses_evo_ui_form_surface(): void
    {
        $view = $this->read('views/livewire/robots-editor.blade.php');

        $this->assertStringContainsString('data-sseo-robots-editor', $view);
        $this->assertStringContainsString('data-evo-form', $view);
        $this->assertStringContainsString('data-evo-form-dirty="{{ $dirty ? \'true\' : \'false\' }}"', $view);
        $this->assertStringContainsString('localDirty: @js($dirty)', $view);
        $this->assertStringContainsString('markDirty()', $view);
        $this->assertStringContainsString('markSaved(event)', $view);
        $this->assertStringContainsString('evo-ui-save-toast', $view);
        $this->assertStringContainsString('x-bind:disabled="!localDirty"', $view);
        $this->assertStringNotContainsString('evo-ui-form-status--saved', $view);
        $this->assertStringContainsString('evo-ui-form-surface', $view);
        $this->assertStringContainsString('evo-ui-form-surface--layout-code-editor', $view);
        $this->assertStringContainsString('evo-ui-form-section', $view);
        $this->assertStringNotContainsString('sseo-robots-toolbar', $view);
        $this->assertStringContainsString('$robotsTitle = __(\'sSeo::global.robots_for\'', $view);
        $this->assertStringContainsString('<span>{{ $robotsTitle }}</span>', $view);
        $this->assertStringContainsString('x-on:click="save"', $view);
        $this->assertStringContainsString('window.myCodeMirrors', $view);
        $this->assertStringContainsString('wire:model.blur="{{ $model }}"', $view);
        $this->assertStringContainsString('<x-evo::card', $view);
        $this->assertStringContainsString(":label=\"count(\$items) > 1 ? __('sSeo::global.robots_for'", $view);
        $this->assertStringNotContainsString('<h2>@lang(\'sSeo::global.robots\')</h2>', $view);
        $this->assertStringNotContainsString('<p>@lang(\'sSeo::global.robots_help\')</p>', $view);
        $this->assertStringContainsString('name="{{ $editorId }}"', $view);
        $this->assertStringContainsString('data-sseo-robots-code', $view);
        $this->assertStringContainsString('evo-ui-code-editor-field', $view);
        $this->assertStringContainsString('data-sseo-robots-editor-key="{{ $editorId }}"', $view);
        $this->assertStringContainsString("{{ \$item['content'] ?? '' }}</textarea>", $view);
        $this->assertStringContainsString('data-code-mirror-base', $view);
        $this->assertStringContainsString('loadCodeMirrorAssets()', $view);
        $this->assertStringContainsString('window.CodeMirror.fromTextArea', $view);
        $this->assertStringContainsString("window.myCodeMirrors[key].on('change'", $view);
        $this->assertStringContainsString('existing.getTextArea', $view);
        $this->assertStringContainsString('this.$root.contains(existingField)', $view);
        $this->assertStringContainsString('delete window.myCodeMirrors[key]', $view);
        $this->assertStringContainsString('evo-ui-textarea--code', $view);
        $this->assertStringContainsString('{!! $editorHtml !!}', $view);
        $this->assertStringNotContainsString('<style>', $view);
    }

    public function test_robots_card_label_translation_is_plain_text_for_evo_ui(): void
    {
        foreach (['en', 'uk', 'ru'] as $locale) {
            $translations = include $this->root . '/lang/' . $locale . '/global.php';

            $this->assertArrayHasKey('robots_for', $translations);
            $this->assertStringContainsString(':name', $translations['robots_for']);
            $this->assertStringNotContainsString('<b>', $translations['robots_for']);
            $this->assertStringNotContainsString('</b>', $translations['robots_for']);
        }
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }
}
