@if(!is_writable(MODX_BASE_PATH . 'robots.txt'))<div class="alert alert-danger" role="alert">@lang('sSeo::global.not_writable_robots')</div>@endif
<form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.update-robots')}}" onsubmit="documentDirty=false;">
    <div class="row form-row form-element-input">
        <div class="col-12">
            <textarea name="robots" id="robots" cols="30" rows="10">{!!$robots!!}</textarea>
        </div>
    </div>
    <div class="split my-3"></div>
</form>

@push('scripts.bot')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            @if(session('success'))
                alertify.success("{{ session('success') }}");
            @endif

            @if(session('error'))
                alertify.error("{{ session('error') }}");
            @endif
        });

        function autoResize(textarea) {
            textarea.style.height = 'auto';

            const newHeight = textarea.scrollHeight + 50;
            textarea.style.height = newHeight + 'px';
        }

        window.onload = function() {
            var textarea = document.getElementById('robots');
            autoResize(textarea);
        };
    </script>

    <div id="actions">
        <div class="btn-group">
            <button id="Button1" class="btn btn-success" onclick="saveForm('#form');">
                <i class="fa fa-save"></i> <span>@lang('global.save')</span>
            </button>
        </div>
    </div>
@endpush
