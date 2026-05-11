<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FontScaleParityContractTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function test_evo_ui_views_do_not_reintroduce_module_specific_font_scale_overrides(): void
    {
        foreach ($this->evoUiViewFiles() as $file) {
            $contents = (string) file_get_contents($file);
            $relative = str_replace($this->root . '/', '', $file);

            $this->assertDoesNotMatchRegularExpression('/font-size\\s*:/i', $contents, $relative);
            $this->assertDoesNotMatchRegularExpression('/\\btext-\\[(?:[^\\]]+)\\]/', $contents, $relative);
            $this->assertDoesNotMatchRegularExpression('/\\btext-(?:xs|sm|base|lg|xl|2xl|3xl|4xl|5xl)\\b/', $contents, $relative);
        }
    }

    public function test_module_panel_uses_shared_sarticles_tab_structure_for_font_and_height_rhythm(): void
    {
        $panel = $this->read('views/livewire/module-panel.blade.php');

        foreach ([
            'class="evo-ui-tabs evo-ui-tabs--module"',
            'class="evo-ui-nav-tabs evo-ui-tab-labels tabs-lift"',
            'class="tab evo-ui-nav-tab"',
            'class="evo-ui-nav-tab__label"',
            'class="evo-ui-nav-tab__icon"',
            '<div class="tab-content">',
            'class="evo-ui-surface"',
        ] as $sharedClass) {
            $this->assertStringContainsString($sharedClass, $panel);
        }
    }

    /**
     * @return list<string>
     */
    private function evoUiViewFiles(): array
    {
        return [
            $this->root . '/views/module/shell.blade.php',
            $this->root . '/views/module/dashboard.blade.php',
            $this->root . '/views/livewire/module-panel.blade.php',
            $this->root . '/views/livewire/robots-editor.blade.php',
            $this->root . '/views/livewire/meta-templates-editor.blade.php',
            $this->root . '/views/partials/fieldsBlock.blade.php',
        ];
    }

    private function read(string $path): string
    {
        $absolute = $this->root . '/' . $path;

        $this->assertFileExists($absolute);

        return (string) file_get_contents($absolute);
    }
}
