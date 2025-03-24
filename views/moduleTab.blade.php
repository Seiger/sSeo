<form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.modulesave')}}" onsubmit="documentDirty=false;">
    <input type="hidden" name="sseo[resource_id]" value="{{(int)$id}}">
    <input type="hidden" name="sseo[resource_type]" value="{{$type}}">
    @include('sSeo::partials.fieldsBlock')
</form>
@push('scripts.bot')
    <div id="actions">
        <div class="btn-group">
            <button id="Button1" class="btn btn-success" onclick="saveForm('#form');">
                <i class="fa fa-save"></i> <span>@lang('global.save')</span>
            </button>
        </div>
    </div>
@endpush
