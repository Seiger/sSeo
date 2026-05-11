<section
    class="evo-ui-form-surface evo-ui-form-surface--config evo-ui-form-surface--density-compact evo-ui-form-surface--layout-code-editor"
    data-sseo-robots-editor
    data-code-mirror-base="{{ $legacyCodeMirrorBaseUrl ?? '/assets/plugins/codemirror/' }}"
    data-evo-form
    data-evo-form-dirty="{{ $dirty ? 'true' : 'false' }}"
    data-evo-form-saved="{{ $saved ? 'true' : 'false' }}"
    x-data="{
        localDirty: @js($dirty),
        showSavedToast: false,
        savedToastTimer: null,
        codeMirrorBaseUrl: null,
        markDirty() {
            this.localDirty = true;
            this.$root.setAttribute('data-evo-form-dirty', 'true');
            this.$root.setAttribute('data-evo-form-saved', 'false');
        },
        markSaved(event) {
            if (event.detail?.preset && event.detail.preset !== 'sseo.robots') {
                return;
            }

            this.localDirty = false;
            this.$root.setAttribute('data-evo-form-dirty', 'false');
            this.$root.setAttribute('data-evo-form-saved', 'true');
            this.showSavedToast = true;
            clearTimeout(this.savedToastTimer);
            this.savedToastTimer = setTimeout(() => {
                this.showSavedToast = false;
            }, 2400);
        },
        loadAsset(tag, key, url) {
            return new Promise((resolve, reject) => {
                const existing = document.querySelector(`[data-sseo-codemirror-asset='${key}']`);

                if (existing) {
                    if (existing.dataset.loaded === '1') {
                        resolve();
                        return;
                    }

                    existing.addEventListener('load', resolve, { once: true });
                    existing.addEventListener('error', reject, { once: true });
                    return;
                }

                const asset = document.createElement(tag);
                asset.dataset.sseoCodemirrorAsset = key;
                asset.addEventListener('load', () => {
                    asset.dataset.loaded = '1';
                    resolve();
                }, { once: true });
                asset.addEventListener('error', reject, { once: true });

                if (tag === 'link') {
                    asset.rel = 'stylesheet';
                    asset.href = url;
                    document.head.appendChild(asset);
                    asset.dataset.loaded = '1';
                    resolve();
                    return;
                }

                asset.src = url;
                document.head.appendChild(asset);
            });
        },
        loadCodeMirrorAssets() {
            const base = this.codeMirrorBaseUrl || this.$root.dataset.codeMirrorBase || '/assets/plugins/codemirror/';

            return Promise.all([
                this.loadAsset('link', 'codemirror-css', `${base}cm/lib/codemirror.css`),
                this.loadAsset('link', 'codemirror-addon-css', `${base}cm/addon.css`),
                this.loadAsset('link', 'codemirror-default-css', `${base}cm/theme/default.css`),
                this.loadAsset('link', 'codemirror-one-dark-css', `${base}cm/theme/one-dark.css`),
            ]).then(() => this.loadAsset('script', 'codemirror-core', `${base}cm/lib/codemirror-compressed.js`))
                .then(() => this.loadAsset('script', 'codemirror-xml', `${base}cm/mode/xml-compressed.js`))
                .then(() => this.loadAsset('script', 'codemirror-javascript', `${base}cm/mode/javascript-compressed.js`))
                .then(() => this.loadAsset('script', 'codemirror-css-mode', `${base}cm/mode/css-compressed.js`))
                .then(() => this.loadAsset('script', 'codemirror-htmlmixed', `${base}cm/mode/htmlmixed-compressed.js`))
                .then(() => this.loadAsset('script', 'codemirror-addon', `${base}cm/addon-compressed.js`));
        },
        initEditors() {
            const mount = () => {
                if (!window.CodeMirror) {
                    return;
                }

                window.myCodeMirrors = window.myCodeMirrors || {};

                this.$root.querySelectorAll('[data-sseo-robots-code]').forEach((field) => {
                    const key = field.dataset.sseoRobotsEditorKey || field.name || field.id;

                    if (!key) {
                        return;
                    }

                    const existing = window.myCodeMirrors[key];
                    const existingField = existing && typeof existing.getTextArea === 'function'
                        ? existing.getTextArea()
                        : null;

                    if (existing && existingField && this.$root.contains(existingField)) {
                        return;
                    }

                    if (existing) {
                        delete window.myCodeMirrors[key];
                    }

                    window.myCodeMirrors[key] = window.CodeMirror.fromTextArea(field, {
                        mode: 'htmlmixed',
                        theme: document.documentElement.dataset.themeMode === 'dark' ? 'one-dark' : 'default',
                        lineNumbers: true,
                        lineWrapping: true,
                        matchBrackets: true,
                        indentUnit: 4,
                        tabSize: 4,
                        viewportMargin: Infinity,
                    });

                    window.myCodeMirrors[key].setSize('100%', '560px');
                    window.myCodeMirrors[key].on('change', () => {
                        window.myCodeMirrors[key].save();
                        field.dispatchEvent(new Event('input', { bubbles: true }));
                        this.markDirty();
                    });
                });
            };

            if (window.CodeMirror) {
                mount();
                return;
            }

            this.loadCodeMirrorAssets().then(mount).catch(() => {});
        },
        syncEditors() {
            if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
                window.tinymce.triggerSave();
            }

            if (window.myCodeMirrors) {
                Object.values(window.myCodeMirrors).forEach((editor) => {
                    if (editor && typeof editor.save === 'function') {
                        editor.save();
                    }
                });
            }

            this.$root.querySelectorAll('[data-sseo-robots-code]').forEach((field) => {
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });
        },
        save() {
            this.syncEditors();
            this.$nextTick(() => $wire.save());
        }
    }"
    x-init="$nextTick(() => initEditors())"
    x-on:livewire:navigated.window="$nextTick(() => initEditors())"
    x-on:evo-ui:form.saved.window="markSaved($event)"
>
    @php
        $firstItem = $items[0] ?? [];
        $robotsTitle = __('sSeo::global.robots_for', ['name' => $firstItem['label'] ?? $firstItem['key'] ?? __('sSeo::global.robots')]);
    @endphp

    <div class="evo-ui-form-heading">
        <h2>
            <x-evo::icon name="file-terminal" />
            <span>{{ $robotsTitle }}</span>
        </h2>
        <div class="evo-ui-form-toolbar" aria-label="@lang('evo::global.form_actions')">
            <button
                type="button"
                class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled"
                x-on:click="save"
                x-bind:disabled="!localDirty"
                x-bind:class="{ 'is-disabled': !localDirty }"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <x-evo::icon name="check" />
                <span>@lang('evo::global.action_save')</span>
            </button>
        </div>
    </div>

    <div
        class="evo-ui-save-toast evo-ui-save-toast--success"
        role="status"
        aria-live="polite"
        x-cloak
        x-show="showSavedToast"
        x-transition.opacity.duration.150ms
    >
        <span class="evo-ui-save-toast__content">
            <x-evo::icon name="circle-check" />
            <span>@lang('evo::global.form_saved')</span>
        </span>
    </div>

    <div class="evo-ui-form">
        @foreach($items as $index => $item)
            @php($model = 'items.' . $index . '.content')
            @php($editorId = $item['editor_id'] ?? ('sseo_robots_' . $index))

            <x-evo::card
                class="evo-ui-form-section evo-ui-form-section--span-12"
                :label="count($items) > 1 ? __('sSeo::global.robots_for', ['name' => $item['label'] ?? $item['key'] ?? '']) : null"
                icon="file-terminal"
                wire:key="sseo-robots-{{ $item['key'] ?? $index }}"
            >
                <div class="evo-ui-form-grid">
                    <label class="evo-ui-field evo-ui-field--full evo-ui-field--no-label evo-ui-code-editor-field {{ $errors->first($model) ? 'has-error' : '' }}">
                        <span class="evo-ui-sr-only">@lang('sSeo::global.robots')</span>
                        <textarea
                            id="{{ $editorId }}"
                            name="{{ $editorId }}"
                            class="evo-ui-input evo-ui-textarea evo-ui-textarea--code"
                            rows="24"
                            spellcheck="false"
                            data-sseo-robots-code
                            data-sseo-robots-editor-key="{{ $editorId }}"
                            wire:model.blur="{{ $model }}"
                            x-on:input="markDirty()"
                            x-on:change="markDirty()"
                        >{{ $item['content'] ?? '' }}</textarea>
                        <span class="evo-ui-field__hint">{{ $item['target'] ?? '' }}</span>
                        @if($errors->first($model))
                            <span class="evo-ui-field__error">{{ $errors->first($model) }}</span>
                        @endif
                    </label>
                </div>
            </x-evo::card>
        @endforeach
    </div>

    @if($editorHtml)
        {!! $editorHtml !!}
    @endif

</section>
