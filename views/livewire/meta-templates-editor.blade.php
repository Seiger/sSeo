<section class="evo-ui-form-surface evo-ui-form-surface--config" data-sseo-meta-templates-editor>
    <div class="evo-ui-form-heading">
        <div class="evo-ui-form-heading__main">
            <div class="evo-ui-form-heading__icon">
                <x-evo::icon name="file-text" />
            </div>
            <div>
                <h2>@lang('sSeo::global.templates')</h2>
                <p>@lang('sSeo::global.templates_help')</p>
            </div>
        </div>

        <div class="evo-ui-form-toolbar" aria-label="@lang('evo::global.form_actions')">
            @if($saved)
                <span class="evo-ui-form-status evo-ui-form-status--saved">
                    <x-evo::icon name="check" />
                    <span>@lang('sSeo::global.success_updated')</span>
                </span>
            @endif
            <button type="button" class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled" wire:click="save">
                <x-evo::icon name="check" />
                <span>@lang('evo::global.action_save')</span>
            </button>
        </div>
    </div>

    <div class="evo-ui-form">
        @foreach($sections as $sectionIndex => $section)
            <section class="evo-ui-form-section evo-ui-form-section--span-12" wire:key="sseo-template-section-{{ $section['key'] }}">
                <header class="evo-ui-form-section__header">
                    <div>
                        <h3>
                            <x-evo::icon :name="$section['icon'] ?? 'file-text'" />
                            <span>@lang($section['label'])</span>
                        </h3>
                        <p>{{ trim(__('sSeo::global.meta_placeholders', ['more' => $section['placeholder_more'] ?? ''])) }}.</p>
                    </div>
                </header>

                <div class="evo-ui-form-grid">
                    @foreach($section['fields'] as $fieldIndex => $field)
                        @php
                            $model = 'sections.' . $sectionIndex . '.fields.' . $fieldIndex . '.value';
                            $lang = (string) ($field['lang'] ?? 'base');
                        @endphp
                        <label class="evo-ui-field evo-ui-field--full" wire:key="sseo-template-field-{{ $field['key'] }}">
                            <span class="evo-ui-field__label">
                                <span>
                                    @lang($field['label'])
                                    @if($lang !== 'base')
                                        <code>{{ strtoupper($lang) }}</code>
                                    @endif
                                </span>
                                <span
                                    class="evo-ui-field__help"
                                    title="@lang($field['help'])"
                                    aria-label="@lang($field['help'])"
                                    data-tooltip="@lang($field['help'])"
                                    data-evo-tooltip="@lang($field['help'])"
                                    tabindex="0"
                                >?</span>
                            </span>
                            <textarea
                                class="evo-ui-input evo-ui-textarea evo-ui-textarea--code"
                                rows="3"
                                wire:model.blur="{{ $model }}"
                            ></textarea>
                            @if(($field['hint'] ?? '') !== '')
                                <span class="evo-ui-field__hint">
                                    @lang('sSeo::global.template_example') <code>{{ $field['hint'] }}</code>
                                </span>
                            @endif
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</section>
