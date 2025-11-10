@extends('sSeo::index')
@section('header')
    <button class="s-btn s-btn--success" onclick="submitForm('#form');">
        <i data-lucide="save" class="w-4 h-4"></i>@lang('global.save')
    </button>
@endsection
@section('content')
    <form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.urobots')}}" onsubmit="documentDirty=false;">
        @foreach ($robots as $key => $robot)
            @if(!is_writable(trim($robot ?? '') ?: EVO_BASE_PATH))
                <div class="s-alert s-alert--danger">
                    <i data-lucide="alert-triangle" class="s-alert--icon-danger"></i>
                    <div>
                        <strong class="font-semibold">@lang('sSeo::global.warning')</strong><br>
                        @lang('sSeo::global.not_writable', ['file' => $robot])
                    </div>
                </div>
            @endif
            <div class="max-w-7xl mx-auto py-3 px-6" x-data="sSeo.sPinner('{{$key}}')">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden darkness:bg-[#122739] darkness:border-slate-700">
                    <span @click="togglePin" class="s-meta-block-btn">
                        <div class="flex items-center gap-2">
                            <svg data-lucide="file-terminal" class="w-5 h-5 text-sky-500"></svg>
                            <span class="font-semibold text-base text-slate-700 darkness:text-slate-200">@lang('sSeo::global.robots_for', ['name' => $sites[$key]])</span>
                        </div>
                        <svg :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform text-slate-500"></svg>
                    </span>
                    <div x-ref="content" x-bind:style="open ? 'min-height:' + $refs.content.scrollHeight + 'px' : 'max-height: 0px'" class="s-meta-block-content">
                        <div class="p-6 space-y-6">
                            <textarea name="{{$key}}" id="{{$key}}" onchange="documentDirty=true;">{!!trim($robot ?? '') ? file_get_contents($robot) : (file_exists(EVO_BASE_PATH . 'sample-robots.txt') ? file_get_contents(EVO_BASE_PATH . 'sample-robots.txt') : '')!!}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </form>
    {!!$codeEditor!!}
@endsection
