<form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.update-templates')}}" onsubmit="documentDirty=false;">
    <div class="tw:space-y-4">
        <div class="accordion-item">
            <h2 class="tw:text-lg">
                <span id="buttonMetaTitle" class="tw:w-full tw:flex tw:items-center tw:justify-between tw:p-1 tw:bg-gradient-to-r tw:from-blue-500 tw:to-sky-500 tw:text-white tw:text-base tw:font-normal tw:rounded-t-xl tw:rounded-b-xl tw:hover:from-blue-400 tw:hover:to-sky-400 tw:transition-all tw:duration-300 tw:cursor-pointer tw:shadow-md tw:shadow-gray-300/30" onclick="toggleAccordion('collapseMetaTitle', 'buttonMetaTitle')">
                    <label class="tw:inline-flex tw:items-center tw:gap-2 tw:pl-4 tw:whitespace-nowrap">
                        <span>@lang('sSeo::global.meta_title')</span>
                        <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.meta_title_help')"></i>
                    </label>
                    <svg class="tw:w-5 tw:h-5 tw:transform tw:transition-transform tw:duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </span>
            </h2>
            <div id="collapseMetaTitle" class="accordion-collapse tw:hidden tw:border-2 tw:border-blue-500 tw:p-4 tw:-mt-2">
                <div class="tw:relative tw:mb-4">
                    <label for="sseo_meta_title_document_base" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700">@lang('sSeo::global.meta_title_label', ['type_a' => __('sSeo::global.type_a_document'), 'lang' => ''])</label>
                    <textarea name="sseo_meta_title_document_base" id="sseo_meta_title_document_base" cols="30" rows="10" class="tw:w-full tw:m-0 tw:p-2 tw:border tw:rounded-md tw:shadow-sm tw:focus:ring-2 tw:focus:ring-blue-500 tw:mt-2 tw:mb-0">{!!evo()->getConfig('sseo_meta_title_document_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                    <p class="tw:text-xs tw:text-gray-500 tw:mt-2">@lang('sSeo::global.meta_title_info', ['more' => ''])</p>
                </div>
                @if (evo()->getConfig('check_sCommerce', false))
                    <div class="tw:relative tw:mb-4">
                        <label for="sseo_meta_title_prodcat_base" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700">@lang('sSeo::global.meta_title_label', ['type_a' => __('sSeo::global.type_a_prodcat'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_title_prodcat_base" id="sseo_meta_title_prodcat_base" cols="30" rows="10" class="tw:w-full tw:m-0 tw:p-2 tw:border tw:rounded-md tw:shadow-sm tw:focus:ring-2 tw:focus:ring-blue-500 tw:mt-2 tw:mb-0">{!!evo()->getConfig('sseo_meta_title_prodcat_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                        <p class="tw:text-xs tw:text-gray-500 tw:mt-2">@lang('sSeo::global.meta_title_info', ['more' => ''])</p>
                    </div>
                    <div class="tw:relative tw:mb-4">
                        <label for="sseo_meta_title_product_base" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700">@lang('sSeo::global.meta_title_label', ['type_a' => __('sSeo::global.type_a_product'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_title_product_base" id="sseo_meta_title_product_base" cols="30" rows="10" class="tw:w-full tw:m-0 tw:p-2 tw:border tw:rounded-md tw:shadow-sm tw:focus:ring-2 tw:focus:ring-blue-500 tw:mt-2 tw:mb-0">{!!evo()->getConfig('sseo_meta_title_product_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                        <p class="tw:text-xs tw:text-gray-500 tw:mt-2">@lang('sSeo::global.meta_title_info', ['more' => '[*sku*], [*rating*], [*price*],'])</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="tw:text-lg">
                <span id="buttonMetaDescription" class="tw:w-full tw:flex tw:items-center tw:justify-between tw:p-1 tw:bg-gradient-to-r tw:from-blue-500 tw:to-sky-500 tw:text-white tw:text-base tw:font-normal tw:rounded-t-xl tw:rounded-b-xl tw:hover:from-blue-400 tw:hover:to-sky-400 tw:transition-all tw:duration-300 tw:cursor-pointer tw:shadow-md tw:shadow-gray-300/30" onclick="toggleAccordion('collapseMetaDescription', 'buttonMetaDescription')">
                    <label class="tw:inline-flex tw:items-center tw:gap-2 tw:pl-4 tw:whitespace-nowrap">
                        <span>@lang('sSeo::global.meta_description')</span>
                        <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.meta_description_help')"></i>
                    </label>
                    <svg class="tw:w-5 tw:h-5 tw:transform tw:transition-transform tw:duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </span>
            </h2>
            <div id="collapseMetaDescription" class="accordion-collapse tw:hidden tw:border-2 tw:border-blue-500 tw:p-4 tw:-mt-2">
                <div class="tw:relative tw:mb-4">
                    <label for="sseo_meta_description_document_base" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700">@lang('sSeo::global.meta_description_label', ['type_a' => __('sSeo::global.type_a_document'), 'lang' => ''])</label>
                    <textarea name="sseo_meta_description_document_base" id="sseo_meta_description_document_base" cols="30" rows="10" class="tw:w-full tw:m-0 tw:p-2 tw:border tw:rounded-md tw:shadow-sm tw:focus:ring-2 tw:focus:ring-blue-500 tw:mt-2 tw:mb-0">{!!evo()->getConfig('sseo_meta_description_document_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                    <p class="tw:text-xs tw:text-gray-500 tw:mt-2">@lang('sSeo::global.meta_title_info', ['more' => ''])</p>
                </div>
                @if (evo()->getConfig('check_sCommerce', false))
                    <div class="tw:relative tw:mb-4">
                        <label for="sseo_meta_title_prodcat_base" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700">@lang('sSeo::global.meta_description_label', ['type_a' => __('sSeo::global.type_a_prodcat'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_description_prodcat_base" id="sseo_meta_description_prodcat_base" cols="30" rows="10" class="tw:w-full tw:m-0 tw:p-2 tw:border tw:rounded-md tw:shadow-sm tw:focus:ring-2 tw:focus:ring-blue-500 tw:mt-2 tw:mb-0">{!!evo()->getConfig('sseo_meta_description_prodcat_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                        <p class="tw:text-xs tw:text-gray-500 tw:mt-2">@lang('sSeo::global.meta_title_info', ['more' => ''])</p>
                    </div>
                    <div class="tw:relative tw:mb-4">
                        <label for="sseo_meta_description_product_base" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700">@lang('sSeo::global.meta_description_label', ['type_a' => __('sSeo::global.type_a_product'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_description_product_base" id="sseo_meta_description_product_base" cols="30" rows="10" class="tw:w-full tw:m-0 tw:p-2 tw:border tw:rounded-md tw:shadow-sm tw:focus:ring-2 tw:focus:ring-blue-500 tw:mt-2 tw:mb-0">{!!evo()->getConfig('sseo_meta_description_product_base', '[*pagetitle*] - [(site_name)]')!!}</textarea>
                        <p class="tw:text-xs tw:text-gray-500 tw:mt-2">@lang('sSeo::global.meta_title_info', ['more' => '[*sku*], [*rating*], [*price*],'])</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="tw:text-lg">
                <span id="buttonMetaKeywords" class="tw:w-full tw:flex tw:items-center tw:justify-between tw:p-1 tw:bg-gradient-to-r tw:from-blue-500 tw:to-sky-500 tw:text-white tw:text-base tw:font-normal tw:rounded-t-xl tw:rounded-b-xl tw:hover:from-blue-400 tw:hover:to-sky-400 tw:transition-all tw:duration-300 tw:cursor-pointer tw:shadow-md tw:shadow-gray-300/30" onclick="toggleAccordion('collapseMetaKeywords', 'buttonMetaKeywords')">
                    <label class="tw:inline-flex tw:items-center tw:gap-2 tw:pl-4 tw:whitespace-nowrap">
                        <span>@lang('sSeo::global.meta_keywords')</span>
                        <i class="fa fa-question-circle" data-tooltip="@lang('sSeo::global.meta_keywords_help')"></i>
                    </label>
                    <svg class="tw:w-5 tw:h-5 tw:transform tw:transition-transform tw:duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </span>
            </h2>
            <div id="collapseMetaKeywords" class="accordion-collapse tw:hidden tw:border-2 tw:border-blue-500 tw:p-4 tw:-mt-2">
                <div class="tw:relative tw:mb-4">
                    <label for="sseo_meta_keywords_document_base" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700">@lang('sSeo::global.meta_keywords_label', ['type_a' => __('sSeo::global.type_a_document'), 'lang' => ''])</label>
                    <textarea name="sseo_meta_keywords_document_base" id="sseo_meta_keywords_document_base" cols="30" rows="10" class="tw:w-full tw:m-0 tw:p-2 tw:border tw:rounded-md tw:shadow-sm tw:focus:ring-2 tw:focus:ring-blue-500 tw:mt-2 tw:mb-0">{!!evo()->getConfig('sseo_meta_keywords_document_base', '[*pagetitle*], [*longtitle*]')!!}</textarea>
                    <p class="tw:text-xs tw:text-gray-500 tw:mt-2">@lang('sSeo::global.meta_title_info', ['more' => ''])</p>
                </div>
                @if (evo()->getConfig('check_sCommerce', false))
                    <div class="tw:relative tw:mb-4">
                        <label for="sseo_meta_title_prodcat_base" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700">@lang('sSeo::global.meta_keywords_label', ['type_a' => __('sSeo::global.type_a_prodcat'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_keywords_prodcat_base" id="sseo_meta_keywords_prodcat_base" cols="30" rows="10" class="tw:w-full tw:m-0 tw:p-2 tw:border tw:rounded-md tw:shadow-sm tw:focus:ring-2 tw:focus:ring-blue-500 tw:mt-2 tw:mb-0">{!!evo()->getConfig('sseo_meta_keywords_prodcat_base', '[*pagetitle*], [*longtitle*]')!!}</textarea>
                        <p class="tw:text-xs tw:text-gray-500 tw:mt-2">@lang('sSeo::global.meta_title_info', ['more' => ''])</p>
                    </div>
                    <div class="tw:relative tw:mb-4">
                        <label for="sseo_meta_description_product_base" class="tw:block tw:text-sm tw:font-medium tw:text-gray-700">@lang('sSeo::global.meta_keywords_label', ['type_a' => __('sSeo::global.type_a_product'), 'lang' => ''])</label>
                        <textarea name="sseo_meta_keywords_product_base" id="sseo_meta_keywords_product_base" cols="30" rows="10" class="tw:w-full tw:m-0 tw:p-2 tw:border tw:rounded-md tw:shadow-sm tw:focus:ring-2 tw:focus:ring-blue-500 tw:mt-2 tw:mb-0">{!!evo()->getConfig('sseo_meta_keywords_product_base', '[*pagetitle*], [*longtitle*]')!!}</textarea>
                        <p class="tw:text-xs tw:text-gray-500 tw:mt-2">@lang('sSeo::global.meta_title_info', ['more' => '[*sku*], [*rating*], [*price*],'])</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</form>
<div class="split tw:my-3"></div>
{!!$codeEditor!!}

@push('scripts.top')
    <link rel="stylesheet" href="/core/vendor/seiger/sseo/css/tailwind.min.css">
@endpush

@push('scripts.bot')
    <script>
        function toggleAccordion(id, buttonId) {
            let content = document.getElementById(id);
            let button = document.getElementById(buttonId);

            content.classList.toggle("tw:hidden");

            let svgIcon = content.previousElementSibling.querySelector('svg');
            svgIcon.classList.toggle('rotate-180');

            if (!content.classList.contains("tw:hidden")) {
                button.classList.remove("tw:rounded-b-xl");
                button.classList.add("tw:rounded-b-none");
                @foreach($editor as $d)myCodeMirrors.{{$d}}.refresh();@endforeach
            } else {
                button.classList.remove("tw:rounded-b-none");
                button.classList.add("tw:rounded-b-xl");
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
