@extends('sSeo::index')
@section('header')
    <button class="s-btn s-btn--success" onclick="submitForm('#form');">
        <i data-lucide="save" class="w-4 h-4"></i>@lang('global.save')
    </button>
@endsection
@section('content')
    <form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.uanalytics')}}" onsubmit="documentDirty=false;">@csrf
        @php($settingsFilePath = (EVO_CORE_PATH . 'custom/config/seiger/settings/sSeo.php'))
        @if((file_exists($settingsFilePath) && !is_writable($settingsFilePath)) || (is_dir(dirname($settingsFilePath)) && !is_writable(dirname($settingsFilePath))))
            <div class="s-alert s-alert--danger">
                <i data-lucide="alert-triangle" class="s-alert--icon-danger"></i>
                <div>
                    <strong class="font-semibold">@lang('sSeo::global.warning')</strong><br>
                    @lang('sSeo::global.not_writable', ['file' => $settingsFilePath])
                </div>
            </div>
        @endif

        @if(evo()->getConfig('check_sMultisite', false) && (($sites ?? collect())->count() > 0))
            @foreach(($sites ?? []) as $site)
                @php($key = (string)($site->key ?? ''))
                @if($key === '') @continue @endif
                <div class="max-w-7xl mx-auto py-3 px-6" x-data="sSeo.sPinner('analytics_{{$key}}')">
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden darkness:bg-[#122739] darkness:border-slate-700">
                        <span @click="togglePin" class="s-meta-block-btn">
                            <div class="flex items-center gap-2">
                                <svg data-lucide="bar-chart-3" class="w-5 h-5 text-sky-500"></svg>
                                <span class="font-semibold text-base text-slate-700 darkness:text-slate-200">{{($site->site_name ?? $key)}} ({{$key}})</span>
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform text-slate-500"></svg>
                        </span>
                        <div x-ref="content" x-bind:style="open ? 'min-height:' + $refs.content.scrollHeight + 'px' : 'max-height: 0px'" class="s-meta-block-content">
                            <div class="p-6 space-y-6">
                                <div class="grid grid-cols-12 gap-x-2 gap-y-4 items-start">
                                    <label for="{{$key}}_gtm_container_id" class="col-span-12 sm:col-span-2 text-sm font-medium text-slate-700 darkness:text-slate-300 pt-2 pr-2">
                                        @lang('sSeo::global.gtm_container_id')
                                    </label>
                                    <div class="col-span-12 sm:col-span-10">
                                        <input type="text" id="{{$key}}_gtm_container_id" name="{{$key}}_gtm_container_id" value="{{old($key.'_gtm_container_id', ($gtmBySite[$key] ?? ''))}}" class="w-full rounded-md border border-slate-300 darkness:border-slate-600 bg-white darkness:bg-slate-800 text-slate-800 darkness:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="documentDirty=true;">
                                        <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">@lang('sSeo::global.gtm_container_id_help')</p>
                                        <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">
                                            @lang('sSeo::global.analytics_active_gtm'): <b>{{implode(', ', ($gtmActiveBySite[$key] ?? [])) ?: __('sSeo::global.none')}}</b>
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-x-2 gap-y-4 items-start">
                                    <label for="{{$key}}_ga4_measurement_id" class="col-span-12 sm:col-span-2 text-sm font-medium text-slate-700 darkness:text-slate-300 pt-2 pr-2">
                                        @lang('sSeo::global.ga4_measurement_id')
                                    </label>
                                    <div class="col-span-12 sm:col-span-10">
                                        <input type="text" id="{{$key}}_ga4_measurement_id" name="{{$key}}_ga4_measurement_id" value="{{old($key.'_ga4_measurement_id', ($ga4BySite[$key] ?? ''))}}" class="w-full rounded-md border border-slate-300 darkness:border-slate-600 bg-white darkness:bg-slate-800 text-slate-800 darkness:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="documentDirty=true;">
                                        <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">@lang('sSeo::global.ga4_measurement_id_help')</p>
                                        <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">
                                            @lang('sSeo::global.analytics_active_ga4'): <b>{{implode(', ', ($ga4ActiveBySite[$key] ?? [])) ?: __('sSeo::global.none')}}</b>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="max-w-7xl mx-auto py-3 px-6" x-data="sSeo.sPinner('analytics_single')">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden darkness:bg-[#122739] darkness:border-slate-700">
                    <span @click="togglePin" class="s-meta-block-btn">
                        <div class="flex items-center gap-2">
                            <svg data-lucide="bar-chart-3" class="w-5 h-5 text-sky-500"></svg>
                            <span class="font-semibold text-base text-slate-700 darkness:text-slate-200">@lang('sSeo::global.analytics')</span>
                        </div>
                        <svg :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform text-slate-500"></svg>
                    </span>
                    <div x-ref="content" x-bind:style="open ? 'min-height:' + $refs.content.scrollHeight + 'px' : 'max-height: 0px'" class="s-meta-block-content">
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-12 gap-x-2 gap-y-4 items-start">
                                <label for="gtm_container_id" class="col-span-12 sm:col-span-2 text-sm font-medium text-slate-700 darkness:text-slate-300 pt-2 pr-2">
                                    @lang('sSeo::global.gtm_container_id')
                                </label>
                                <div class="col-span-12 sm:col-span-10">
                                    <input type="text" id="gtm_container_id" name="gtm_container_id" value="{{old('gtm_container_id', ($gtmBySite['single'] ?? config('seiger.settings.sSeo.gtm_container_id', '')))}}" class="w-full rounded-md border border-slate-300 darkness:border-slate-600 bg-white darkness:bg-slate-800 text-slate-800 darkness:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="documentDirty=true;">
                                    <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">@lang('sSeo::global.gtm_container_id_help')</p>
                                    <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">
                                        @lang('sSeo::global.analytics_active_gtm'): <b>{{implode(', ', ($gtmActiveBySite['single'] ?? [])) ?: __('sSeo::global.none')}}</b>
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-12 gap-x-2 gap-y-4 items-start">
                                <label for="ga4_measurement_id" class="col-span-12 sm:col-span-2 text-sm font-medium text-slate-700 darkness:text-slate-300 pt-2 pr-2">
                                    @lang('sSeo::global.ga4_measurement_id')
                                </label>
                                <div class="col-span-12 sm:col-span-10">
                                    <input type="text" id="ga4_measurement_id" name="ga4_measurement_id" value="{{old('ga4_measurement_id', ($ga4BySite['single'] ?? config('seiger.settings.sSeo.ga4_measurement_id', '')))}}" class="w-full rounded-md border border-slate-300 darkness:border-slate-600 bg-white darkness:bg-slate-800 text-slate-800 darkness:text-white px-3 py-2 focus:ring-2 focus:ring-blue-500" onchange="documentDirty=true;">
                                    <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">@lang('sSeo::global.ga4_measurement_id_help')</p>
                                    <p class="text-xs text-slate-500 darkness:text-slate-400 mt-1">
                                        @lang('sSeo::global.analytics_active_ga4'): <b>{{implode(', ', ($ga4ActiveBySite['single'] ?? [])) ?: __('sSeo::global.none')}}</b>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </form>
@endsection
