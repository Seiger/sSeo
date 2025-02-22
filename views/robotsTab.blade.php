<form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.update-robots')}}" onsubmit="documentDirty=false;">
    @if(is_array($robots))
        <div class="space-y-4">
            @foreach ($sites as $site)
                <div class="accordion-item">
                    <h2 class="text-lg">
                        <span id="button{{$site->id}}" class="w-full flex items-center justify-between p-1 bg-gradient-to-r from-blue-500 to-sky-500 text-white font-normal rounded-t-xl rounded-b-xl hover:bg-gradient-to-r hover:from-blue-500 hover:to-sky-500 transition-all duration-300 cursor-pointer shadow-lg" onclick="toggleAccordion('collapse{{$site->id}}', 'button{{$site->id}}')">
                            <span class="flex-1 pl-4">{{$site->site_name}}</span>
                            <svg class="w-5 h-5 transform transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </span>
                    </h2>
                    <div id="collapse{{$site->id}}" class="accordion-collapse hidden">
                        @if(!is_writable(trim($robots[$site->key . '_robots'] ?? '') ?: MODX_BASE_PATH))
                            <div class="alert alert-danger text-red-500 mt-0 mb-0 rounded-none">
                                @lang('sSeo::global.not_writable', ['file' => trim($site->key . '_robots' ?? '') ?: MODX_BASE_PATH])
                            </div>
                        @endif
                        <textarea name="{{$site->key}}_robots" id="{{$site->key}}_robots" cols="30" rows="10" class="w-full m-0 p-0 border-t-0 rounded-t-none rounded-b-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-0 mb-0" onchange="documentDirty=true;">{!!trim($robots[$site->key . '_robots'] ?? '') ? file_get_contents($robots[$site->key . '_robots']) : ''!!}</textarea>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        @if(!is_writable(trim($robots ?? '') ?: MODX_BASE_PATH))
            <div class="alert alert-danger text-red-500 mt-0 mb-0 rounded-t-md rounded-b-none">@lang('sSeo::global.not_writable', ['file' => trim($robots ?? '') ?: MODX_BASE_PATH])</div>
        @endif
        <textarea name="robots" id="robots" cols="30" rows="10" class="w-full m-0 p-0 border-t-0 rounded-t-md rounded-b-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-0 mb-0" onchange="documentDirty=true;">{!!trim($robots ?? '') ? file_get_contents($robots) : ''!!}</textarea>
    @endif
</form>
<div class="split my-3"></div>
{!!$codeEditor!!}

@push('scripts.top')
    <script src="https://cdn.tailwindcss.com"></script>
@endpush

@push('scripts.bot')
    <script>
        function toggleAccordion(id, buttonId) {
            let content = document.getElementById(id);
            let button = document.getElementById(buttonId);

            content.classList.toggle("hidden");

            let svgIcon = content.previousElementSibling.querySelector('svg');
            svgIcon.classList.toggle('rotate-180');

            if (!content.classList.contains("hidden")) {
                button.classList.remove("rounded-b-xl");
                button.classList.add("rounded-b-none");
                @foreach($editor as $d)myCodeMirrors.{{$d}}.refresh();@endforeach
            } else {
                button.classList.remove("rounded-b-none");
                button.classList.add("rounded-b-xl");
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            @if(session('success'))
            alertify.success("{{ session('success') }}");
            @endif
            @if(session('error'))
            alertify.error("{{ session('error') }}");
            @endif
        });
    </script>

    <div id="actions">
        <div class="btn-group">
            <button id="Button1" class="btn btn-success" onclick="saveForm('#form');">
                <i class="fa fa-save"></i> <span>@lang('global.save')</span>
            </button>
        </div>
    </div>
@endpush
