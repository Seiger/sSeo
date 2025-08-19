<div class="form-block">
    <h3><b>@lang('sSeo::global.meta_tags')</b></h3>
    <div class="row form-row form-element-input d-flex flex-column flex-md-row">
        <label class="control-label col-12 col-md-2 col-xl-1">
            <span>@lang('sSeo::global.robots')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.robots_help')"></i>
        </label>
        <div class="col-12 col-md-10 col-xl-11">
            <select id="robots" name="sseo[robots]" class="form-control" onchange="documentDirty=true;">
                <option value="" @if(($robots ?? '') == '') selected @endif></option>
                <option value="index,follow" @if(($robots ?? '') == 'index,follow') selected @endif>index,follow</option>
                <option value="index,nofollow" @if(($robots ?? '') == 'index,nofollow') selected @endif>index,nofollow</option>
                <option value="noindex,nofollow" @if(($robots ?? '') == 'noindex,nofollow') selected @endif>noindex,nofollow</option>
            </select>
        </div>
    </div>
    <div class="row form-row form-element-input d-flex flex-column flex-md-row">
        <label class="control-label col-12 col-md-2 col-xl-1">
            <span>@lang('sSeo::global.meta_title')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.meta_title_help')"></i>
        </label>
        <div class="col-12 col-md-10 col-xl-11">
            <input id="meta_title" name="sseo[meta_title]" value="{{$meta_title ?? ''}}" type="text" class="form-control" placeholder="{{sSeo::checkMetaTitle()}}" onchange="documentDirty=true;">
        </div>
    </div>
    <div class="row form-row form-element-input d-flex flex-column flex-md-row">
        <label class="control-label col-12 col-md-2 col-xl-1">
            <span>@lang('sSeo::global.meta_description')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.meta_description_help')"></i>
        </label>
        <div class="col-12 col-md-10 col-xl-11">
            <textarea id="meta_description" name="sseo[meta_description]" rows="2" class="form-control" placeholder="{{sSeo::checkMetaDescription()}}" onchange="documentDirty=true;">{{$meta_description ?? ''}}</textarea>
        </div>
    </div>
</div>
<div class="row form-row form-element-input d-flex flex-column flex-md-row">
    <label class="control-label col-12 col-md-2 col-xl-1">
        <span>@lang('sSeo::global.meta_keywords')</span>
        <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.meta_keywords_help')"></i>
    </label>
    <div class="col-12 col-md-10 col-xl-11">
        <input id="meta_keywords" name="sseo[meta_keywords]" value="{{$meta_keywords ?? ''}}" type="text" class="form-control" placeholder="{{sSeo::checkMetaKeywords()}}" onchange="documentDirty=true;">
    </div>
</div>
<div class="row form-row form-element-input d-flex flex-column flex-md-row">
    <label class="control-label col-12 col-md-2 col-xl-1">
        <span>@lang('sSeo::global.canonical')</span>
        <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.canonical_help')"></i>
    </label>
    <div class="col-12 col-md-10 col-xl-11">
        <input id="meta_keywords" name="sseo[canonical_url]" value="{{$canonical_url ?? ''}}" type="text" class="form-control" placeholder="{{sSeo::checkCanonical()}}" onchange="documentDirty=true;">
    </div>
</div>
<div class="split my-3"></div>
<div class="form-block">
    <h3><b>@lang('sSeo::global.sitemap_settings')</b></h3>
    <div class="row form-row form-element-input d-flex flex-column flex-md-row align-items-center">
        <label class="control-label col-12 col-md-3 col-lg-2">
            <span>@lang('sSeo::global.exclude_from_sitemap')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.exclude_from_sitemap_help')"></i>
        </label>
        <div class="col-12 col-md-3 col-lg-2">
            <input type="hidden" name="sseo[exclude_from_sitemap]" value="0">
            <input type="checkbox" name="sseo[exclude_from_sitemap]" id="exclude_from_sitemap" value="1" @if($exclude_from_sitemap ?? false) checked @endif onchange="documentDirty=true;">
        </div>
        <label class="control-label col-12 col-md-3 col-lg-2">
            <span>@lang('sSeo::global.priority')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.priority_help')"></i>
        </label>
        <div class="col-12 col-md-3 col-lg-2 col-xl-1">
            <select id="priority" name="sseo[priority]" class="form-control" onchange="documentDirty=true;">
                <option value="1.0" @if(($priority ?? '') == 1.0) selected @endif>1.0</option>
                <option value="0.9" @if(($priority ?? '') == 0.9) selected @endif>0.9</option>
                <option value="0.8" @if(($priority ?? '') == 0.8) selected @endif>0.8</option>
                <option value="0.7" @if(($priority ?? '') == 0.7) selected @endif>0.7</option>
                <option value="0.6" @if(($priority ?? '') == 0.6) selected @endif>0.6</option>
                <option value="0.5" @if(($priority ?? '') == 0.5) selected @endif>0.5</option>
                <option value="0.4" @if(($priority ?? '') == 0.4) selected @endif>0.4</option>
                <option value="0.3" @if(($priority ?? '') == 0.3) selected @endif>0.3</option>
                <option value="0.2" @if(($priority ?? '') == 0.2) selected @endif>0.2</option>
                <option value="0.1" @if(($priority ?? '') == 0.1) selected @endif>0.1</option>
            </select>
        </div>
        <label class="control-label col-12 col-md-3 col-lg-2">
            <span>@lang('sSeo::global.change_frequency')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.change_frequency_help')"></i>
        </label>
        <div class="col-12 col-md-3 col-lg-2 col-xl-1">
            <select name="sseo[changefreq]" id="changefreq" class="form-control" onchange="documentDirty=true;">
                <option value="always" @if(($change_frequency ?? '') == 'always') selected @endif>always</option>
                <option value="hourly" @if(($change_frequency ?? '') == 'hourly') selected @endif>hourly</option>
                <option value="daily" @if(($change_frequency ?? '') == 'daily') selected @endif>daily</option>
                <option value="weekly" @if(($change_frequency ?? '') == 'weekly') selected @endif>weekly</option>
                <option value="monthly" @if(($change_frequency ?? '') == 'monthly') selected @endif>monthly</option>
                <option value="yearly" @if(($change_frequency ?? '') == 'yearly') selected @endif>yearly</option>
                <option value="never" @if(($change_frequency ?? '') == 'never') selected @endif>never</option>
            </select>
        </div>
    </div>
</div>
<div class="split my-3"></div>
<input type="hidden" name="sseo[domain_key]" value="{{evo()->getConfig('site_key', 'default')}}">