<form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.update-configure')}}" onsubmit="documentDirty=false;">
    <div class="row form-row">
        <div class="row-col col-12">
            <div class="row form-row">
                <div class="col-auto">
                    <label for="parent" class="warning">@lang('global.server_protocol_title')</label>
                    <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.protocol_help')"></i>
                </div>
                <div class="col">
                    {{evo()->getConfig('server_protocol', 'http')}}
                </div>
            </div>
        </div>
    </div>
    <div class="row form-row">
        <div class="row-col col-12">
            <div class="row form-row">
                <div class="col-auto">
                    <label for="rating_on" class="warning">@lang('sSeo::global.manage_www')</label>
                </div>
                <div class="col">
                    <select name="manage_www" id="manage_www" onchange="documentDirty=true;">
                        <option value="0" @if(config('seiger.settings.sSeo.manage_www', 0) == 0) selected @endif>Не враховувати</option>
                        <option value="1" @if(config('seiger.settings.sSeo.manage_www', 0) == 1) selected @endif>Без WWW</option>
                        <option value="2" @if(config('seiger.settings.sSeo.manage_www', 0) == 2) selected @endif>З WWW</option>
                    </select>
                </div>
            </div>
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
