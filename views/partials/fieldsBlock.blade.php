@php
    $fieldLang = $lang ?? 'base';
    $selectedRobots = (string) ($robots ?? '');
    $selectedPriority = (string) ($priority ?? '');
    $selectedChangefreq = (string) ($changefreq ?? ($change_frequency ?? ''));
    $priorityOptions = ['1.0', '0.9', '0.8', '0.7', '0.6', '0.5', '0.4', '0.3', '0.2', '0.1'];
    $changefreqOptions = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
    $style = $_style ?? ($GLOBALS['_style'] ?? []);
    $helpIcon = $style['icon_question_circle'] ?? 'fa fa-question-circle';
@endphp

<div class="row-col col-12" data-sseo-resource-fields>
    <div class="row form-row">
        <div class="col-12">
            <h3>@lang('sSeo::global.meta_tags')</h3>
            <p><em>@lang('sSeo::global.meta_placeholders', ['more' => ''])</em></p>
        </div>
    </div>

    <div class="row form-row form-element-input">
        <div class="col-auto col-title-11">
            <label for="robots_{{ $fieldLang }}" class="warning">@lang('sSeo::global.robots')</label>
            <i class="{{ $helpIcon }}" data-tooltip="@lang('sSeo::global.robots_help')"></i>
        </div>
        <div class="col">
            <select id="robots_{{ $fieldLang }}" name="sseo[{{ $fieldLang }}][robots]" class="form-control" onchange="documentDirty=true;">
                <option value="" @if($selectedRobots === '') selected @endif></option>
                <option value="index,follow" @if($selectedRobots === 'index,follow') selected @endif>index,follow</option>
                <option value="index,nofollow" @if($selectedRobots === 'index,nofollow') selected @endif>index,nofollow</option>
                <option value="noindex,nofollow" @if($selectedRobots === 'noindex,nofollow') selected @endif>noindex,nofollow</option>
            </select>
            <small class="form-text text-muted"><em>@lang('sSeo::global.robots_hint')</em></small>
        </div>
    </div>

    <div class="row form-row form-element-input">
        <div class="col-auto col-title-11">
            <label for="meta_title_{{ $fieldLang }}" class="warning">@lang('sSeo::global.meta_title')</label>
            <i class="{{ $helpIcon }}" data-tooltip="@lang('sSeo::global.meta_title_help')"></i>
        </div>
        <div class="col">
            <input id="meta_title_{{ $fieldLang }}" name="sseo[{{ $fieldLang }}][meta_title]" value="{{ $meta_title ?? '' }}" type="text" class="form-control" placeholder="{{ sSeo::checkMetaTitle() }}" onchange="documentDirty=true;">
            <small class="form-text text-muted"><em>@lang('sSeo::global.meta_title_hint')</em></small>
        </div>
    </div>

    <div class="row form-row form-element-input">
        <div class="col-auto col-title-11">
            <label for="meta_description_{{ $fieldLang }}" class="warning">@lang('sSeo::global.meta_description')</label>
            <i class="{{ $helpIcon }}" data-tooltip="@lang('sSeo::global.meta_description_help')"></i>
        </div>
        <div class="col">
            <textarea id="meta_description_{{ $fieldLang }}" name="sseo[{{ $fieldLang }}][meta_description]" rows="3" class="form-control" placeholder="{{ sSeo::checkMetaDescription() }}" onchange="documentDirty=true;">{{ $meta_description ?? '' }}</textarea>
            <small class="form-text text-muted"><em>@lang('sSeo::global.meta_description_hint')</em></small>
        </div>
    </div>

    <div class="row form-row form-element-input">
        <div class="col-auto col-title-11">
            <label for="meta_keywords_{{ $fieldLang }}" class="warning">@lang('sSeo::global.meta_keywords')</label>
            <i class="{{ $helpIcon }}" data-tooltip="@lang('sSeo::global.meta_keywords_help')"></i>
        </div>
        <div class="col">
            <input id="meta_keywords_{{ $fieldLang }}" name="sseo[{{ $fieldLang }}][meta_keywords]" value="{{ $meta_keywords ?? '' }}" type="text" class="form-control" placeholder="{{ sSeo::checkMetaKeywords() }}" onchange="documentDirty=true;">
            <small class="form-text text-muted"><em>@lang('sSeo::global.meta_keywords_hint')</em></small>
        </div>
    </div>

    <div class="row form-row form-element-input">
        <div class="col-auto col-title-11">
            <label for="canonical_url_{{ $fieldLang }}" class="warning">@lang('sSeo::global.canonical')</label>
            <i class="{{ $helpIcon }}" data-tooltip="@lang('sSeo::global.canonical_help')"></i>
        </div>
        <div class="col">
            <input id="canonical_url_{{ $fieldLang }}" name="sseo[{{ $fieldLang }}][canonical_url]" value="{{ $canonical_url ?? '' }}" type="text" class="form-control" placeholder="{{ sSeo::checkCanonical() }}" onchange="documentDirty=true;">
            <small class="form-text text-muted"><em>@lang('sSeo::global.canonical_hint')</em></small>
        </div>
    </div>

    <hr>

    <div class="row form-row">
        <div class="col-12">
            <h3>@lang('sSeo::global.sitemap_settings')</h3>
            <p><em>@lang('sSeo::global.generate_sitemap_help')</em></p>
        </div>
    </div>

    <div class="row form-row">
        <div class="col-12 col-lg-4">
            <div class="row form-row form-element-input">
                <div class="col-auto col-title-11">
                    <label for="exclude_from_sitemap_{{ $fieldLang }}" class="warning">@lang('sSeo::global.exclude_from_sitemap')</label>
                    <i class="{{ $helpIcon }}" data-tooltip="@lang('sSeo::global.exclude_from_sitemap_help')"></i>
                </div>
                <div class="col">
                    <input type="hidden" name="sseo[{{ $fieldLang }}][exclude_from_sitemap]" value="0">
                    <input type="checkbox" name="sseo[{{ $fieldLang }}][exclude_from_sitemap]" id="exclude_from_sitemap_{{ $fieldLang }}" value="1" @if($exclude_from_sitemap ?? false) checked @endif onchange="documentDirty=true;">
                    <small class="form-text text-muted"><em>@lang('sSeo::global.exclude_from_sitemap_hint')</em></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="row form-row form-element-input">
                <div class="col-auto col-title-11">
                    <label for="priority_{{ $fieldLang }}" class="warning">@lang('sSeo::global.priority')</label>
                    <i class="{{ $helpIcon }}" data-tooltip="@lang('sSeo::global.priority_help')"></i>
                </div>
                <div class="col">
                    <select id="priority_{{ $fieldLang }}" name="sseo[{{ $fieldLang }}][priority]" class="form-control" onchange="documentDirty=true;">
                        @foreach($priorityOptions as $option)
                            <option value="{{ $option }}" @if($selectedPriority === $option || (string) ($priority ?? '') === (string) (float) $option) selected @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted"><em>@lang('sSeo::global.priority_hint')</em></small>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="row form-row form-element-input">
                <div class="col-auto col-title-11">
                    <label for="changefreq_{{ $fieldLang }}" class="warning">@lang('sSeo::global.change_frequency')</label>
                    <i class="{{ $helpIcon }}" data-tooltip="@lang('sSeo::global.change_frequency_help')"></i>
                </div>
                <div class="col">
                    <select name="sseo[{{ $fieldLang }}][changefreq]" id="changefreq_{{ $fieldLang }}" class="form-control" onchange="documentDirty=true;">
                        @foreach($changefreqOptions as $option)
                            <option value="{{ $option }}" @if($selectedChangefreq === $option) selected @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted"><em>@lang('sSeo::global.change_frequency_hint')</em></small>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="sseo[{{ $fieldLang }}][domain_key]" value="{{ evo()->getConfig('site_key', 'default') }}">
