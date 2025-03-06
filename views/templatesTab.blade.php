<form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.update-templates')}}" onsubmit="documentDirty=false;">
    <div class="space-y-4">
        <div class="accordion-item">
            <h2 class="text-lg">
                <span id="buttonMetaTitle" class="w-full flex items-center justify-between p-1 bg-gradient-to-r from-blue-500 to-sky-500 text-white font-normal rounded-t-xl rounded-b-xl hover:bg-gradient-to-r hover:from-blue-500 hover:to-sky-500 transition-all duration-300 cursor-pointer shadow-lg" onclick="toggleAccordion('collapseMetaTitle', 'buttonMetaTitle')">
                    <label class="flex-1 pl-4 flex items-center">
                        <span class="flex items-center h-full">@lang('sSeo::global.meta_title')</span>
                        <i class="fa fa-question-circle ml-2" data-tooltip="@lang('sSeo::global.meta_title_help')"></i>
                    </label>
                    <svg class="w-5 h-5 transform transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </span>
            </h2>
            <div id="collapseMetaTitle" class="accordion-collapse hidden border-2 border-blue-500 p-4">
                <div class="relative mb-4">
                    <label for="sseo_meta_title_document_base" class="block text-sm font-medium text-gray-700">@lang('sSeo::global.meta_title_label', ['type_a' => __('sSeo::global.type_a_document'), 'lang' => ''])</label>
                    <textarea name="sseo_meta_title_document_base" id="sseo_meta_title_document_base" cols="30" rows="10" class="w-full m-0 p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-2 mb-0">{!!evo()->getConfig('sseo_meta_title_document_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                    <p class="text-xs text-gray-500 mt-2">@lang('sSeo::global.meta_title_info', ['more' => '']).</p>
                </div>
                @if (evo()->getConfig('check_sCommerce', false))
                    <div class="relative mb-4">
                        <label for="sseo_meta_title_prodcat_base" class="block text-sm font-medium text-gray-700">@lang('sSeo::global.meta_title_label', ['type_a' => __('sSeo::global.type_a_prodcat'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_title_prodcat_base" id="sseo_meta_title_prodcat_base" cols="30" rows="10" class="w-full m-0 p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-2 mb-0">{!!evo()->getConfig('sseo_meta_title_prodcat_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                        <p class="text-xs text-gray-500 mt-2">@lang('sSeo::global.meta_title_info', ['more' => '']).</p>
                    </div>
                    <div class="relative mb-4">
                        <label for="sseo_meta_title_product_base" class="block text-sm font-medium text-gray-700">@lang('sSeo::global.meta_title_label', ['type_a' => __('sSeo::global.type_a_product'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_title_product_base" id="sseo_meta_title_product_base" cols="30" rows="10" class="w-full m-0 p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-2 mb-0">{!!evo()->getConfig('sseo_meta_title_product_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                        <p class="text-xs text-gray-500 mt-2">@lang('sSeo::global.meta_title_info', ['more' => '[*sku*], [*rating*], [*price*],'])</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="text-lg">
                <span id="buttonMetaDescription" class="w-full flex items-center justify-between p-1 bg-gradient-to-r from-blue-500 to-sky-500 text-white font-normal rounded-t-xl rounded-b-xl hover:bg-gradient-to-r hover:from-blue-500 hover:to-sky-500 transition-all duration-300 cursor-pointer shadow-lg" onclick="toggleAccordion('collapseMetaDescription', 'buttonMetaDescription')">
                    <label class="flex-1 pl-4 flex items-center">
                        <span class="flex items-center h-full">@lang('sSeo::global.meta_description')</span>
                        <i class="fa fa-question-circle ml-2" data-tooltip="@lang('sSeo::global.meta_description_help')"></i>
                    </label>
                    <svg class="w-5 h-5 transform transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </span>
            </h2>
            <div id="collapseMetaDescription" class="accordion-collapse hidden border-2 border-blue-500 p-4">
                <div class="relative mb-4">
                    <label for="sseo_meta_description_document_base" class="block text-sm font-medium text-gray-700">@lang('sSeo::global.meta_description_label', ['type_a' => __('sSeo::global.type_a_document'), 'lang' => ''])</label>
                    <textarea name="sseo_meta_description_document_base" id="sseo_meta_description_document_base" cols="30" rows="10" class="w-full m-0 p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-2 mb-0">{!!evo()->getConfig('sseo_meta_description_document_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                    <p class="text-xs text-gray-500 mt-2">@lang('sSeo::global.meta_title_info', ['more' => '']).</p>
                </div>
                @if (evo()->getConfig('check_sCommerce', false))
                    <div class="relative mb-4">
                        <label for="sseo_meta_title_prodcat_base" class="block text-sm font-medium text-gray-700">@lang('sSeo::global.meta_description_label', ['type_a' => __('sSeo::global.type_a_prodcat'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_description_prodcat_base" id="sseo_meta_description_prodcat_base" cols="30" rows="10" class="w-full m-0 p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-2 mb-0">{!!evo()->getConfig('sseo_meta_description_prodcat_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                        <p class="text-xs text-gray-500 mt-2">@lang('sSeo::global.meta_title_info', ['more' => '']).</p>
                    </div>
                    <div class="relative mb-4">
                        <label for="sseo_meta_description_product_base" class="block text-sm font-medium text-gray-700">@lang('sSeo::global.meta_description_label', ['type_a' => __('sSeo::global.type_a_product'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_description_product_base" id="sseo_meta_description_product_base" cols="30" rows="10" class="w-full m-0 p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-2 mb-0">{!!evo()->getConfig('sseo_meta_description_product_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                        <p class="text-xs text-gray-500 mt-2">@lang('sSeo::global.meta_title_info', ['more' => '[*sku*], [*rating*], [*price*],'])</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="text-lg">
                <span id="buttonMetaKeywords" class="w-full flex items-center justify-between p-1 bg-gradient-to-r from-blue-500 to-sky-500 text-white font-normal rounded-t-xl rounded-b-xl hover:bg-gradient-to-r hover:from-blue-500 hover:to-sky-500 transition-all duration-300 cursor-pointer shadow-lg" onclick="toggleAccordion('collapseMetaKeywords', 'buttonMetaKeywords')">
                    <label class="flex-1 pl-4 flex items-center">
                        <span class="flex items-center h-full">@lang('sSeo::global.meta_keywords')</span>
                        <i class="fa fa-question-circle ml-2" data-tooltip="@lang('sSeo::global.meta_keywords_help')"></i>
                    </label>
                    <svg class="w-5 h-5 transform transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </span>
            </h2>
            <div id="collapseMetaKeywords" class="accordion-collapse hidden border-2 border-blue-500 p-4">
                <div class="relative mb-4">
                    <label for="sseo_meta_keywords_document_base" class="block text-sm font-medium text-gray-700">@lang('sSeo::global.meta_keywords_label', ['type_a' => __('sSeo::global.type_a_document'), 'lang' => ''])</label>
                    <textarea name="sseo_meta_keywords_document_base" id="sseo_meta_keywords_document_base" cols="30" rows="10" class="w-full m-0 p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-2 mb-0">{!!evo()->getConfig('sseo_meta_keywords_document_base', '[*pagetitle*], [*longtitle*]')!!}</textarea>
                    <p class="text-xs text-gray-500 mt-2">@lang('sSeo::global.meta_title_info', ['more' => '']).</p>
                </div>
                @if (evo()->getConfig('check_sCommerce', false))
                    <div class="relative mb-4">
                        <label for="sseo_meta_title_prodcat_base" class="block text-sm font-medium text-gray-700">@lang('sSeo::global.meta_keywords_label', ['type_a' => __('sSeo::global.type_a_prodcat'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_keywords_prodcat_base" id="sseo_meta_keywords_prodcat_base" cols="30" rows="10" class="w-full m-0 p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-2 mb-0">{!!evo()->getConfig('sseo_meta_keywords_prodcat_base', '[*pagetitle*], [*longtitle*]')!!}</textarea>
                        <p class="text-xs text-gray-500 mt-2">@lang('sSeo::global.meta_title_info', ['more' => '']).</p>
                    </div>
                    <div class="relative mb-4">
                        <label for="sseo_meta_description_product_base" class="block text-sm font-medium text-gray-700">@lang('sSeo::global.meta_keywords_label', ['type_a' => __('sSeo::global.type_a_product'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_keywords_product_base" id="sseo_meta_keywords_product_base" cols="30" rows="10" class="w-full m-0 p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 mt-2 mb-0">{!!evo()->getConfig('sseo_meta_keywords_product_base', '[*pagetitle*], [*longtitle*]')!!}</textarea>
                        <p class="text-xs text-gray-500 mt-2">@lang('sSeo::global.meta_title_info', ['more' => '[*sku*], [*rating*], [*price*],'])</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
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
                alertify.success("{{session('success')}}");
            @endif
            @if(session('error'))
                alertify.error("{{session('error')}}");
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
