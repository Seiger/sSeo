@extends('sSeo::index')
@section('header')
    <button class="s-btn s-btn--success" onclick="submitForm('#form');">
        <i data-lucide="save" class="w-4 h-4"></i>@lang('global.save')
    </button>
@endsection
@section('content')
    @if(evo()->getConfig('sseo_pro', false))
        <form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.utemplates')}}" onsubmit="documentDirty=false;">
            <div class="max-w-7xl mx-auto py-3 px-6" x-data="sSeo.sPinner('document_base')">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden darkness:bg-[#122739] darkness:border-slate-700">
                    <span @click="toggle()" class="s-meta-block-btn">
                        <div class="flex items-center gap-2">
                            <svg data-lucide="file-text" class="w-5 h-5 text-sky-500"></svg>
                            <span class="font-semibold text-base text-slate-700 darkness:text-slate-200">@lang('sSeo::global.type_a_document')</span>
                        </div>
                        <svg :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform text-slate-500"></svg>
                    </span>
                    <div x-ref="content" x-bind:style="open ? 'min-height:' + $refs.content.scrollHeight + 'px' : 'max-height: 0px'" class="s-meta-block-content">
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    <span class="inline-flex items-center gap-1">
                                        Meta Title
                                        <i data-lucide="help-circle" class="w-4 h-4 text-slate-400" data-tooltip="@lang('sSeo::global.meta_title_help')"></i>
                                    </span>
                                </label>
                                @foreach($langs as $lang)
                                    <div class="flex items-start gap-3">
                                        @if($lang != 'base')
                                            <div class="flex items-center justify-center px-3 py-2 rounded-md bg-slate-200 text-slate-600 text-xs font-bold darkness:bg-slate-800 darkness:text-slate-200 w-12 h-10">
                                                {{strtoupper($lang)}}
                                            </div>
                                        @endif
                                        <textarea name="sseo_meta_title_document_{{$lang}}" id="sseo_meta_title_document_{{$lang}}">{!!evo()->getConfig('sseo_meta_title_document_' . $lang, '[*pagetitle*] - [(site_name)]')!!}</textarea>
                                    </div>
                                @endforeach
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    <span class="inline-flex items-center gap-1">
                                        Meta Description
                                        <i data-lucide="help-circle" class="w-4 h-4 text-slate-400" data-tooltip="@lang('sSeo::global.meta_description_help')"></i>
                                    </span>
                                </label>
                                @foreach($langs as $lang)
                                    <div class="flex items-start gap-3">
                                        @if($lang != 'base')
                                            <div class="flex items-center justify-center px-3 py-2 rounded-md bg-slate-200 text-slate-600 text-xs font-bold darkness:bg-slate-800 darkness:text-slate-200 w-12 h-10">
                                                {{strtoupper($lang)}}
                                            </div>
                                        @endif
                                        <textarea name="sseo_meta_description_document_{{$lang}}" id="sseo_meta_description_document_{{$lang}}">{!!evo()->getConfig('sseo_meta_description_document_' . $lang, '[*pagetitle*] - [(site_name)]')!!}</textarea>
                                    </div>
                                @endforeach
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    <span class="inline-flex items-center gap-1">
                                        Keywords
                                        <i data-lucide="help-circle" class="w-4 h-4 text-slate-400" data-tooltip="@lang('sSeo::global.meta_keywords_help')"></i>
                                    </span>
                                </label>
                                @foreach($langs as $lang)
                                    <div class="flex items-start gap-3">
                                        @if($lang != 'base')
                                            <div class="flex items-center justify-center px-3 py-2 rounded-md bg-slate-200 text-slate-600 text-xs font-bold darkness:bg-slate-800 darkness:text-slate-200 w-12 h-10">
                                                {{strtoupper($lang)}}
                                            </div>
                                        @endif
                                        <textarea name="sseo_meta_keywords_document_{{$lang}}" id="sseo_meta_keywords_document_{{$lang}}">{!!evo()->getConfig('sseo_meta_keywords_document_' . $lang, '[*pagetitle*], [*longtitle*]')!!}</textarea>
                                    </div>
                                @endforeach
                            </div>
                            <hr class="my-4 border-t darkness:border-slate-700">
                            <div class="text-sm text-slate-500 italic placeholders">{{trim(__('sSeo::global.meta_placeholders', ['more' => '']))}}.</div>
                        </div>
                    </div>
                </div>
            </div>
            @if (evo()->getConfig('check_sCommerce', false))
                <div class="max-w-7xl mx-auto py-3 px-6" x-data="sSeo.sPinner('prodcat')">
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden darkness:bg-[#122739] darkness:border-slate-700">
                        <span @click="toggle()" class="s-meta-block-btn">
                            <div class="flex items-center gap-2">
                                <svg data-lucide="store" class="w-5 h-5 text-sky-500"></svg>
                                <span class="font-semibold text-base text-slate-700 darkness:text-slate-200">@lang('sSeo::global.type_a_prodcat')</span>
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform text-slate-500"></svg>
                        </span>
                        <div x-ref="content" x-bind:style="open ? 'min-height:' + $refs.content.scrollHeight + 'px' : 'max-height: 0px'" class="s-meta-block-content">
                            <div class="p-6 space-y-6">
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        <span class="inline-flex items-center gap-1">
                                            Meta Title
                                            <i data-lucide="help-circle" class="w-4 h-4 text-slate-400" data-tooltip="@lang('sSeo::global.meta_title_help')"></i>
                                        </span>
                                    </label>
                                    @foreach($langs as $lang)
                                        <div class="flex items-start gap-3">
                                            @if($lang != 'base')
                                                <div class="flex items-center justify-center px-3 py-2 rounded-md bg-slate-200 text-slate-600 text-xs font-bold darkness:bg-slate-800 darkness:text-slate-200 w-12 h-10">
                                                    {{strtoupper($lang)}}
                                                </div>
                                            @endif
                                            <textarea name="sseo_meta_title_prodcat_{{$lang}}" id="sseo_meta_title_prodcat_{{$lang}}">{!!evo()->getConfig('sseo_meta_title_prodcat_' . $lang, '[*pagetitle*] - [(site_name)]')!!}</textarea>
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        <span class="inline-flex items-center gap-1">
                                            Meta Description
                                            <i data-lucide="help-circle" class="w-4 h-4 text-slate-400" data-tooltip="@lang('sSeo::global.meta_description_help')"></i>
                                        </span>
                                    </label>
                                    @foreach($langs as $lang)
                                        <div class="flex items-start gap-3">
                                            @if($lang != 'base')
                                                <div class="flex items-center justify-center px-3 py-2 rounded-md bg-slate-200 text-slate-600 text-xs font-bold darkness:bg-slate-800 darkness:text-slate-200 w-12 h-10">
                                                    {{strtoupper($lang)}}
                                                </div>
                                            @endif
                                            <textarea name="sseo_meta_description_prodcat_{{$lang}}" id="sseo_meta_description_prodcat_{{$lang}}">{!!evo()->getConfig('sseo_meta_description_prodcat_' . $lang, '[*pagetitle*] - [(site_name)]')!!}</textarea>
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        <span class="inline-flex items-center gap-1">
                                            Keywords
                                            <i data-lucide="help-circle" class="w-4 h-4 text-slate-400" data-tooltip="@lang('sSeo::global.meta_keywords_help')"></i>
                                        </span>
                                    </label>
                                    @foreach($langs as $lang)
                                        <div class="flex items-start gap-3">
                                            @if($lang != 'base')
                                                <div class="flex items-center justify-center px-3 py-2 rounded-md bg-slate-200 text-slate-600 text-xs font-bold darkness:bg-slate-800 darkness:text-slate-200 w-12 h-10">
                                                    {{strtoupper($lang)}}
                                                </div>
                                            @endif
                                            <textarea name="sseo_meta_keywords_prodcat_{{$lang}}" id="sseo_meta_keywords_prodcat_{{$lang}}">{!!evo()->getConfig('sseo_meta_keywords_prodcat_' . $lang, '[*pagetitle*], [*longtitle*]')!!}</textarea>
                                        </div>
                                    @endforeach
                                </div>
                                <hr class="my-4 border-t darkness:border-slate-700">
                                <div class="text-sm text-slate-500 italic placeholders">{{trim(__('sSeo::global.meta_placeholders', ['more' => '']))}}.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="max-w-7xl mx-auto py-3 px-6" x-data="sSeo.sPinner('product')">
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden darkness:bg-[#122739] darkness:border-slate-700">
                        <span @click="toggle()" class="s-meta-block-btn">
                            <div class="flex items-center gap-2">
                                <svg data-lucide="store" class="w-5 h-5 text-sky-500"></svg>
                                <span class="font-semibold text-base text-slate-700 darkness:text-slate-200">@lang('sSeo::global.type_a_product')</span>
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform text-slate-500"></svg>
                        </span>
                        <div x-ref="content" x-bind:style="open ? 'min-height:' + $refs.content.scrollHeight + 'px' : 'max-height: 0px'" class="s-meta-block-content">
                            <div class="p-6 space-y-6">
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        <span class="inline-flex items-center gap-1">
                                            Meta Title
                                            <i data-lucide="help-circle" class="w-4 h-4 text-slate-400" data-tooltip="@lang('sSeo::global.meta_title_help')"></i>
                                        </span>
                                    </label>
                                    @foreach($langs as $lang)
                                        <div class="flex items-start gap-3">
                                            @if($lang != 'base')
                                                <div class="flex items-center justify-center px-3 py-2 rounded-md bg-slate-200 text-slate-600 text-xs font-bold darkness:bg-slate-800 darkness:text-slate-200 w-12 h-10">
                                                    {{strtoupper($lang)}}
                                                </div>
                                            @endif
                                            <textarea name="sseo_meta_title_product_{{$lang}}" id="sseo_meta_title_product_{{$lang}}">{!!evo()->getConfig('sseo_meta_title_product_' . $lang, '[*pagetitle*] - [(site_name)]')!!}</textarea>
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        <span class="inline-flex items-center gap-1">
                                            Meta Description
                                            <i data-lucide="help-circle" class="w-4 h-4 text-slate-400" data-tooltip="@lang('sSeo::global.meta_description_help')"></i>
                                        </span>
                                    </label>
                                    @foreach($langs as $lang)
                                        <div class="flex items-start gap-3">
                                            @if($lang != 'base')
                                                <div class="flex items-center justify-center px-3 py-2 rounded-md bg-slate-200 text-slate-600 text-xs font-bold darkness:bg-slate-800 darkness:text-slate-200 w-12 h-10">
                                                    {{strtoupper($lang)}}
                                                </div>
                                            @endif
                                            <textarea name="sseo_meta_description_product_{{$lang}}" id="sseo_meta_description_product_{{$lang}}">{!!evo()->getConfig('sseo_meta_description_product_' . $lang, '[*pagetitle*] - [(site_name)]')!!}</textarea>
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        <span class="inline-flex items-center gap-1">
                                            Keywords
                                            <i data-lucide="help-circle" class="w-4 h-4 text-slate-400" data-tooltip="@lang('sSeo::global.meta_keywords_help')"></i>
                                        </span>
                                    </label>
                                    @foreach($langs as $lang)
                                        <div class="flex items-start gap-3">
                                            @if($lang != 'base')
                                                <div class="flex items-center justify-center px-3 py-2 rounded-md bg-slate-200 text-slate-600 text-xs font-bold darkness:bg-slate-800 darkness:text-slate-200 w-12 h-10">
                                                    {{strtoupper($lang)}}
                                                </div>
                                            @endif
                                            <textarea name="sseo_meta_keywords_product_{{$lang}}" id="sseo_meta_keywords_product_{{$lang}}">{!!evo()->getConfig('sseo_meta_keywords_product_' . $lang, '[*pagetitle*], [*longtitle*]')!!}</textarea>
                                        </div>
                                    @endforeach
                                </div>
                                <hr class="my-4 border-t darkness:border-slate-700">
                                <div class="text-sm text-slate-500 italic placeholders">{{trim(__('sSeo::global.meta_placeholders', ['more' => ', [*sku*], [*rating*], [*price*]']))}}.</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </form>
        {!!$codeEditor!!}
    @endif
@endsection
