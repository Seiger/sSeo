<div class="tab-page resourceTab" id="resourceTab">
    <h2 class="tab"><span><i class="@lang('sSeo::global.icon')"></i> @lang('sSeo::global.title')</span></h2>
    <div class="row form-row form-element-input">
        <label class="control-label col-5 col-md-3 col-lg-1">
            <span>@lang('sSeo::global.meta_title')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.meta_title_help')"></i>
        </label>
        <div class="col-7 col-md-9 col-lg-11">
            <input id="meta_title" name="sseo[meta_title]" value="{{$meta_title ?? ''}}" type="text" class="form-control" onchange="documentDirty=true;">
        </div>
    </div>
    <div class="row form-row form-element-input">
        <label class="control-label col-5 col-md-3 col-lg-1">
            <span>@lang('sSeo::global.meta_description')</span>
            <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.meta_description_help')"></i>
        </label>
        <div class="col-7 col-md-9 col-lg-11">
            <textarea id="meta_description" name="sseo[meta_description]" rows="2" class="form-control" onchange="documentDirty=true;">{{$meta_description ?? ''}}</textarea>
        </div>
    </div>
</div>