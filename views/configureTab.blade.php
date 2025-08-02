@extends('sSeo::index')
@section('header')
    <button class="s-btn s-btn--success" onclick="submitForm('#form');">
        <i data-lucide="save" class="w-4 h-4"></i>@lang('global.save')
    </button>
@endsection
@section('content')
    <form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.uconfigure')}}" onsubmit="documentDirty=false;">@csrf
        @if(!is_writable(EVO_CORE_PATH . 'custom/config/seiger/settings/sSeo.php'))
            <div class="s-alert s-alert--danger">
                <i data-lucide="alert-triangle" class="s-alert--icon-danger"></i>
                <div>
                    <strong class="font-semibold">@lang('sSeo::global.warning')</strong><br>
                    @lang('sSeo::global.not_writable', ['file' => EVO_CORE_PATH . 'custom/config/seiger/settings/sSeo.php'])
                </div>
            </div>
        @endif
        <div class="max-w-7xl mx-auto py-3 px-6" x-data="sSeo.sPinner('config_base')">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden darkness:bg-[#122739] darkness:border-slate-700">
                <span @click="togglePin" class="s-meta-block-btn">
                    <div class="flex items-center gap-2">
                        <svg data-lucide="globe" class="w-5 h-5 text-sky-500"></svg>
                        <span class="font-semibold text-base text-slate-700 darkness:text-slate-200">@lang('sSeo::global.general')</span>
                    </div>
                    <svg :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform text-slate-500"></svg>
                </span>
                <div x-ref="content" x-bind:style="open ? 'min-height:' + $refs.content.scrollHeight + 'px' : 'max-height: 0px'" class="s-meta-block-content">
                    <div class="p-6 space-y-6">
                        <div>
                            <div class="flex items-center gap-3">
                                <label class="text-sm font-medium text-slate-700 darkness:text-slate-300">@lang('global.server_protocol_title')</label>
                                @if(evo()->getConfig('server_protocol', 'http') == 'https')
                                    <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-green-500 text-white rounded-full">https</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-gray-500 text-white rounded-full">http</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">@lang('sSeo::global.protocol_help')</p>
                        </div>
                        <div class="grid grid-cols-12 gap-x-2 gap-y-4 items-start">
                            <label for="manage_www" class="col-span-12 sm:col-span-2 text-sm font-medium text-slate-700 darkness:text-slate-300 pt-2 pr-2">
                                @lang('sSeo::global.manage_www')
                            </label>
                            <div class="col-span-12 sm:col-span-10">
                                <select name="manage_www" id="manage_www" class="w-full rounded-md border border-slate-300 darkness:border-slate-600 bg-white darkness:bg-slate-800 text-slate-800 darkness:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="documentDirty=true;">
                                    <option value="0" @if(config('seiger.settings.sSeo.manage_www', 0) == 0) selected @endif>@lang('sSeo::global.ignore')</option>
                                    <option value="1" @if(config('seiger.settings.sSeo.manage_www', 0) == 1) selected @endif>@lang('sSeo::global.without_www')</option>
                                    <option value="2" @if(config('seiger.settings.sSeo.manage_www', 0) == 2) selected @endif>@lang('sSeo::global.using_www')</option>
                                </select>
                                <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">@lang('sSeo::global.manage_www_help')</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto py-3 px-6" x-data="sSeo.sPinner('indexing')">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden darkness:bg-[#122739] darkness:border-slate-700">
                <span @click="togglePin" class="s-meta-block-btn">
                    <div class="flex items-center gap-2">
                        <svg data-lucide="compass" class="w-5 h-5 text-sky-500"></svg>
                        <span class="font-semibold text-base text-slate-700 darkness:text-slate-200">@lang('sSeo::global.indexing')</span>
                    </div>
                    <svg :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform text-slate-500"></svg>
                </span>
                <div x-ref="content" x-bind:style="open ? 'min-height:' + $refs.content.scrollHeight + 'px' : 'max-height: 0px'" class="s-meta-block-content">
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-12 gap-x-2 gap-y-4 items-start">
                            <label for="manage_www" class="col-span-12 sm:col-span-2 text-sm font-medium text-slate-700 darkness:text-slate-300 pt-2 pr-2">
                                @lang('sSeo::global.paginates_get')
                            </label>
                            <div class="col-span-12 sm:col-span-10">
                                <input type="text" id="paginates_get" name="paginates_get" value="{{config('seiger.settings.sSeo.paginates_get', 'page')}}" class="w-full rounded-md border border-slate-300 darkness:border-slate-600 bg-white darkness:bg-slate-800 text-slate-800 darkness:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="documentDirty=true;">
                                <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">@lang('sSeo::global.paginates_get_help')</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-x-2 gap-y-4 items-start">
                            <label for="noindex_get" class="col-span-12 sm:col-span-2 text-sm font-medium text-slate-700 darkness:text-slate-300 pt-2 pr-2">
                                @lang('sSeo::global.noindex_get')
                            </label>
                            <div class="col-span-12 sm:col-span-10">
                                <input type="text" id="noindex_get" name="noindex_get" value="{{implode(',', config('seiger.settings.sSeo.noindex_get', []))}}" class="w-full rounded-md border border-slate-300 darkness:border-slate-600 bg-white darkness:bg-slate-800 text-slate-800 darkness:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="documentDirty=true;">
                                <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">@lang('sSeo::global.noindex_get_help')</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto py-3 px-6" x-data="sSeo.sPinner('functionality')">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden darkness:bg-[#122739] darkness:border-slate-700">
                <span @click="togglePin" class="s-meta-block-btn">
                    <div class="flex items-center gap-2">
                        <svg data-lucide="zap" class="w-5 h-5 text-sky-500"></svg>
                        <span class="font-semibold text-base text-slate-700 darkness:text-slate-200">@lang('sSeo::global.functionality')</span>
                    </div>
                    <svg :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform text-slate-500"></svg>
                </span>
                <div x-ref="content" x-bind:style="open ? 'min-height:' + $refs.content.scrollHeight + 'px' : 'max-height: 0px'" class="s-meta-block-content">
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-12 gap-x-2 gap-y-4 items-start">
                            <label for="redirects_enabled" class="col-span-12 sm:col-span-2 text-sm font-medium text-slate-700 darkness:text-slate-300 pt-2 pr-2">
                                @lang('sSeo::global.redirects_enabled')
                            </label>
                            <div class="col-span-12 sm:col-span-10">
                                <label class="inline-flex items-center me-5 cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" data-target="redirects_enabled" {{config('seiger.settings.sSeo.redirects_enabled', 0) == 1 ? 'checked' : '' }}>
                                    <div class="s-toggle-slider"></div>
                                    <input type="hidden" id="redirects_enabled" name="redirects_enabled" value="{{(int)config('seiger.settings.sSeo.redirects_enabled', 0)}}">
                                </label>
                                <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">
                                    @lang('sSeo::global.redirects_enabled_help')
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-x-2 gap-y-4 items-start">
                            <label for="generate_sitemap" class="col-span-12 sm:col-span-2 text-sm font-medium text-slate-700 darkness:text-slate-300 pt-2 pr-2">
                                @lang('sSeo::global.generate_sitemap')
                            </label>
                            <div class="col-span-12 sm:col-span-10">
                                <label class="inline-flex items-center me-5 cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" data-target="generate_sitemap" {{config('seiger.settings.sSeo.generate_sitemap', 0) == 1 ? 'checked' : '' }}>
                                    <div class="s-toggle-slider"></div>
                                    <input type="hidden" id="generate_sitemap" name="generate_sitemap" value="{{(int)config('seiger.settings.sSeo.generate_sitemap', 0)}}">
                                </label>
                                <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">
                                    @lang('sSeo::global.generate_sitemap_help')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
