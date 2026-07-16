@extends('sSeo::index')
@section('header')
    <button class="s-btn s-btn--success" onclick="submitForm('#form');">
        <i data-lucide="save" class="w-4 h-4"></i>@lang('global.save')
    </button>
@endsection
@section('content')
    <form id="form" name="form" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.ullms')}}" onsubmit="documentDirty=false;">
        @foreach ($llms as $key => $llm)
            @php
                $llmPath = trim($llm ?? '');
                $llmWritableTarget = $llmPath && file_exists($llmPath) ? $llmPath : ($llmPath ? dirname($llmPath) : '');
            @endphp
            @if($llmWritableTarget && !is_writable($llmWritableTarget))
                <div class="s-alert s-alert--danger">
                    <i data-lucide="alert-triangle" class="s-alert--icon-danger"></i>
                    <div>
                        <strong class="font-semibold">@lang('sSeo::global.warning')</strong><br>
                        @lang('sSeo::global.not_writable', ['file' => $llmWritableTarget])
                    </div>
                </div>
            @endif
            <div class="max-w-7xl mx-auto py-3 px-6" x-data="sSeo.sPinner('{{$key}}')">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden darkness:bg-[#122739] darkness:border-slate-700">
                    <span @click="toggle()" class="s-meta-block-btn">
                        <div class="flex items-center gap-2">
                            @svg('tabler-robot', 'w-5 h-5 text-sky-500')
                            <span class="font-semibold text-base text-slate-700 darkness:text-slate-200">@lang('sSeo::global.llms_for', ['name' => $sites[$key]])</span>
                        </div>
                        <svg :class="open ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-4 h-4 transition-transform text-slate-500"></svg>
                    </span>
                    <div x-ref="content" x-bind:style="open ? 'min-height:' + $refs.content.scrollHeight + 'px' : 'max-height: 0px'" class="s-meta-block-content">
                        <div class="p-6 space-y-6">
                            <textarea name="{{$key}}" id="{{$key}}" onchange="documentDirty=true;">{!!trim($llm ?? '') ? file_get_contents($llm) : ''!!}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </form>
    {!!$codeEditor!!}
@endsection
