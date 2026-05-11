<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MetaTemplatesEditorContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_templates_tab_is_pro_gated_and_uses_module_shell_route(): void
    {
        $tabs = include $this->root . '/config/module/tabs.php';
        $templates = collect($tabs)->firstWhere('key', 'templates');
        $controller = $this->read('src/Controllers/sSeoController.php');

        $this->assertSame('sseo_pro', $templates['cms_setting'] ?? null);
        $this->assertStringContainsString("\$cmsSetting = (string) (\$tab['cms_setting'] ?? '');", $controller);
        $this->assertStringContainsString("return \$this->moduleTabRedirect('templates');", $controller);
    }

    public function test_meta_templates_editor_preserves_system_settings_storage_contract(): void
    {
        $component = $this->read('src/Livewire/MetaTemplatesEditor.php');
        $provider = $this->read('src/sSeoServiceProvider.php');
        $panel = $this->read('views/livewire/module-panel.blade.php');

        $this->assertStringContainsString('class MetaTemplatesEditor extends Component', $component);
        $this->assertStringContainsString("DB::table('system_settings')->updateOrInsert", $component);
        $this->assertStringContainsString("str_starts_with(\$key, 'sseo_')", $component);
        $this->assertStringContainsString('removeSanitizeSeed($value)', $component);
        $this->assertStringContainsString("\$key = 'sseo_' . \$name . '_' . \$type . '_' . \$lang;", $component);
        $this->assertStringContainsString("'hint' => \$default", $component);
        $this->assertStringContainsString("\$sections[] = \$this->resourceSection('product'", $component);
        $this->assertStringContainsString("Livewire::component('sseo.meta-templates-editor'", $provider);
        $this->assertStringContainsString('<livewire:sseo.meta-templates-editor', $panel);
    }

    public function test_meta_templates_view_uses_evo_ui_form_surface(): void
    {
        $view = $this->read('views/livewire/meta-templates-editor.blade.php');

        $this->assertStringContainsString('data-sseo-meta-templates-editor', $view);
        $this->assertStringContainsString('evo-ui-form-surface', $view);
        $this->assertStringContainsString('evo-ui-form-section', $view);
        $this->assertStringContainsString('wire:click="save"', $view);
        $this->assertStringContainsString('wire:model.blur="{{ $model }}"', $view);
        $this->assertStringContainsString('evo-ui-textarea--code', $view);
        $this->assertStringContainsString('evo-ui-field__hint', $view);
        $this->assertStringContainsString('sSeo::global.template_example', $view);
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }
}
