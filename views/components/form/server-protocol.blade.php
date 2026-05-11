@props([
    'controller',
    'field',
    'model' => '',
    'error' => '',
])

@php
    $name = (string) ($field['name'] ?? '_server_protocol');
    $label = __($field['label'] ?? 'global.server_protocol_title');
    $help = $field['help'] ?? null;
    $helpText = $help ? __($help) : '';
    $hintText = !empty($field['hint']) ? __($field['hint']) : '';
    $protocol = (string) data_get($controller->data, $name, 'http');

    if (function_exists('evo')) {
        try {
            $protocol = (string) evo()->getConfig('server_protocol', $protocol);
        } catch (\Throwable) {
            // Unit/demo contexts can expose the helper before the Evolution core is booted.
        }
    }

    $protocol = in_array($protocol, ['http', 'https'], true) ? $protocol : 'http';
@endphp

<label class="evo-ui-field evo-ui-field--static">
    <span class="evo-ui-field__label">
        <span>{{ $label }}</span>
        @if($helpText)
            <span
                class="evo-ui-field__help"
                title="{{ $helpText }}"
                aria-label="{{ $helpText }}"
                data-tooltip="{{ $helpText }}"
                data-evo-tooltip="{{ $helpText }}"
                tabindex="0"
            >?</span>
        @endif
    </span>

    <span class="evo-ui-form-static">
        <x-evo::badge :label="$protocol" :color="$protocol === 'https' ? '#16A34A' : '#64748B'" />
    </span>

    @if($hintText !== '')
        <span class="evo-ui-field__hint">
            @if(!empty($field['hint_html']))
                {!! $hintText !!}
            @else
                {{ $hintText }}
            @endif
        </span>
    @endif
</label>
