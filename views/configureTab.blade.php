@if(!is_writable(EVO_CORE_PATH . 'custom/config/seiger/settings/sSeo.php'))<div class="alert alert-danger" role="alert">@lang('sSeo::global.not_writable')</div>@endif
<form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.update-configure')}}" onsubmit="documentDirty=false;">
    <div class="row form-row form-element-input">
        <label class="control-label col-5 col-md-3 col-lg-2">
            <span>@lang('global.server_protocol_title')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.protocol_help')"></i>
        </label>
        <div class="col-7 col-md-9 col-lg-10">
            @if(evo()->getConfig('server_protocol', 'http') == 'https')
                <h4><span class="badge badge-success">{{evo()->getConfig('server_protocol', 'http')}}</span></h4>
            @else
                <h4><span class="badge badge-secondary">{{evo()->getConfig('server_protocol', 'http')}}</span></h4>
            @endif
        </div>
    </div>
    <div class="row form-row form-element-input">
        <label class="control-label col-5 col-md-3 col-lg-2">
            <span>@lang('sSeo::global.manage_www')</span>
        </label>
        <div class="col-7 col-md-9 col-lg-10">
            <select name="manage_www" id="manage_www" class="form-control" onchange="documentDirty=true;">
                <option value="0" @if(config('seiger.settings.sSeo.manage_www', 0) == 0) selected @endif>@lang('sSeo::global.ignore')</option>
                <option value="1" @if(config('seiger.settings.sSeo.manage_www', 0) == 1) selected @endif>@lang('sSeo::global.without_www')</option>
                <option value="2" @if(config('seiger.settings.sSeo.manage_www', 0) == 2) selected @endif>@lang('sSeo::global.using_www')</option>
            </select>
        </div>
    </div>
    <div class="row form-row form-element-input">
        <label class="control-label col-5 col-md-3 col-lg-2">
            <span>@lang('sSeo::global.paginates_get')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.paginates_get_help')"></i>
        </label>
        <div class="col-7 col-md-9 col-lg-10">
            <input id="paginates_get" name="paginates_get" value="{{config('seiger.settings.sSeo.paginates_get', 'page')}}" type="text" class="form-control" onchange="documentDirty=true;">
        </div>
    </div>
    <div class="row form-row form-element-input">
        <label class="control-label col-5 col-md-3 col-lg-2">
            <span>@lang('sSeo::global.noindex_get')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.noindex_get_help')"></i>
        </label>
        <div class="col-7 col-md-9 col-lg-10">
            <input id="noindex_get" name="noindex_get" value="{{implode(',', config('seiger.settings.sSeo.noindex_get', []))}}" type="text" class="form-control" onchange="documentDirty=true;">
        </div>
    </div>
    <div class="row form-row form-element-input">
        <label class="control-label col-5 col-md-3 col-lg-2">
            <span>@lang('sSeo::global.redirects_enabled')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.redirects_enabled_help')"></i>
        </label>
        <div class="col-7 col-md-9 col-lg-10">
            <input type="checkbox" name="redirects_enabled" id="redirects_enabled" value="1" @if(config('seiger.settings.sSeo.redirects_enabled', 0) == 1) checked @endif onchange="documentDirty=true;">
        </div>
    </div>
    <div class="row form-row form-element-input">
        <label class="control-label col-5 col-md-3 col-lg-2">
            <span>@lang('sSeo::global.generate_sitemap')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.generate_sitemap_help')"></i>
        </label>
        <div class="col-7 col-md-9 col-lg-10">
            <input type="checkbox" name="generate_sitemap" id="generate_sitemap" value="1" @if(config('seiger.settings.sSeo.generate_sitemap', 0) == 1) checked @endif onchange="documentDirty=true;">
        </div>
    </div>
    <div class="split my-3"></div>
</form>

@push('scripts.bot')
    <div id="actions">
        <div class="btn-group">
            <a id="Button1" class="btn btn-success" href="javascript:void(0);" onclick="saveForm('#form');">
                <i class="fa fa-save"></i><span>@lang('global.save')</span>
            </a>
        </div>
    </div>
@endpush
