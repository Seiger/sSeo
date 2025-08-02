@extends('sSeo::index')
@section('header')
    {{--<button class="s-btn s-btn--primary"><i data-lucide="refresh-ccw" class="w-4 h-4"></i>Generate</button>
    <button class="s-btn s-btn--success"><i data-lucide="save" class="w-4 h-4"></i>Save</button>
    <button class="s-btn s-btn--danger"><i data-lucide="trash-2" class="w-4 h-4"></i>Clear Cache</button>
    <div class="relative group">
        <input type="text" placeholder="Search…" class="s-input-search" />
        <i data-lucide="search" class="absolute left-2 top-1.5 w-4 h-4 text-slate-500 darkness:text-slate-400"></i>
    </div>--}}
@endsection
@section('content')
    <section class="grid gap-6 p-6 grid-cols-1 xs:grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
        {{-- Active Pages --}}
        {{--<div class="s-widget">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <i data-lucide="activity" class="w-5 h-5 text-blue-600 darkness:text-white/80"></i>
                    <h2 class="s-widget-name">Active Pages</h2>
                </div>
                <span class="text-xs font-medium text-emerald-600 darkness:text-emerald-300">+5%</span>
            </div>
            <div class="text-4xl font-semibold text-slate-800 darkness:text-white">2 340</div>
        </div>--}}
        {{-- Pages in Sitemap --}}
        @if(config('seiger.settings.sSeo.redirects_enabled', 0) == 1)
            <div class="s-widget">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="list" class="w-5 h-5 text-slate-600 darkness:text-white/80"></i>
                    <h2 class="s-widget-name">@lang('sSeo::global.pages_in_sitemap')</h2>
                </div>
                <div class="text-3xl font-semibold text-slate-800 mb-1 darkness:text-white">
                    {{number_format(intval($pagesInSitemap['pages'] ?? 0), 0, '.', ' ')}}
                </div>
                <span class="text-xs text-slate-500 darkness:text-white/90">
                    @lang('sSeo::global.last_generated'):
                    <b>{{
                            trim($pagesInSitemap['time'] ?? '') ?
                            Carbon\Carbon::parse($pagesInSitemap['time'])->locale('uk')->isoFormat('D MMM Y') :
                            __('sSeo::global.unknown')
                    }}</b>
                </span>
            </div>
        @endif
        {{-- Crawled Today --}}
        {{--<div class="s-widget">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <i data-lucide="compass" class="w-5 h-5 text-sky-600 darkness:text-white/80"></i>
                    <h2 class="s-widget-name">Crawled Today</h2>
                </div>
                <span class="text-xs font-medium text-emerald-600 darkness:text-emerald-300">+8%</span>
            </div>
            <div class="text-4xl font-semibold text-slate-800 darkness:text-white">347</div>
        </div>
        <!-- Redirect Errors -->
        <div class="s-widget">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <i data-lucide="rotate-ccw" class="w-5 h-5 text-rose-500 darkness:text-white/80"></i>
                    <h2 class="s-widget-name">Redirect Errors</h2>
                </div>
                <span class="text-xs font-medium text-red-600 darkness:text-red-300">+5%</span>
            </div>
            <div class="text-4xl font-semibold text-slate-800 darkness:text-white">24</div>
        </div>
        <!-- Broken Links -->
        <div class="s-widget">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <i data-lucide="link-2-off" class="w-5 h-5 text-rose-500 darkness:text-white/80"></i>
                    <h2 class="s-widget-name">Broken Links</h2>
                </div>
                <span class="text-xs font-medium text-emerald-600 darkness:text-emerald-300">+14%</span>
            </div>
            <div class="text-4xl font-semibold text-slate-800 darkness:text-white">18</div>
        </div>
        <!-- Average Response Time -->
        <div class="s-widget">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <i data-lucide="clock" class="w-5 h-5 text-blue-600 darkness:text-white/80"></i>
                    <h2 class="s-widget-name">Avg Response</h2>
                </div>
                <span class="text-xs font-medium text-emerald-600 darkness:text-emerald-300">+9%</span>
            </div>
            <div class="text-4xl font-semibold text-slate-800 darkness:text-white">0.8 s</div>
        </div>
        <!-- Index Coverage -->
        <div class="s-widget">
            <div class="flex items-center gap-2 mb-3">
                <i data-lucide="search-check" class="w-5 h-5 text-blue-600 darkness:text-white/80"></i>
                <h2 class="s-widget-name">Index Coverage</h2>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2 mb-2">
                <div class="bg-emerald-600 h-2 rounded-full" style="width: 80%"></div>
            </div>
            <span class="text-xs text-slate-600 darkness:text-white/90">1 216 / 1 520 indexed (80%)</span>
        </div>
        <!-- Orphaned Pages -->
        <div class="s-widget">
            <div class="flex items-center gap-2 mb-3">
                <i data-lucide="unlink" class="w-5 h-5 text-blue-600 darkness:text-white/80"></i>
                <h2 class="s-widget-name">Orphaned Pages</h2>
            </div>
            <div class="text-3xl font-semibold text-orange-500 mb-1 darkness:text-white">37</div>
            <span class="mt-auto self-start text-xs text-blue-600 hover:underline">View list</span>
        </div>
        <!-- Missing Meta Description -->
        <div class="s-widget">
            <div class="flex items-center gap-2 mb-3">
                <i data-lucide="alert-octagon" class="w-5 h-5 text-blue-600 darkness:text-white/80"></i>
                <h2 class="s-widget-name">Missing Meta Description</h2>
            </div>
            <div class="text-3xl font-semibold text-red-600 mb-1 darkness:text-white">12</div>
            <span class="text-xs text-slate-600 darkness:text-white/90">pages without description</span>
        </div>
        <!-- Duplicate Titles -->
        <div class="s-widget">
            <div class="flex items-center gap-2 mb-3">
                <i data-lucide="copy" class="w-5 h-5 text-blue-600 darkness:text-white/80"></i>
                <h2 class="s-widget-name">Duplicate Titles</h2>
            </div>
            <div class="text-3xl font-semibold text-orange-500 mb-1 darkness:text-white">9</div>
            <span class="text-xs text-slate-600 darkness:text-white/90">potential duplicates</span>
        </div>
        <!-- Long Titles -->
        <div class="s-widget">
            <div class="flex items-center gap-2 mb-3">
                <i data-lucide="type" class="w-5 h-5 text-blue-600 darkness:text-white/80"></i>
                <h2 class="s-widget-name">Long Titles</h2>
            </div>
            <div class="text-3xl font-semibold text-orange-500 mb-1 darkness:text-white">14</div>
            <span class="text-xs text-slate-600 darkness:text-white/90">> 60 characters</span>
        </div>--}}
    </section>
    {{-- Recent Activity --}}
    <section class="px-6 pb-12">
        <div class="rounded-2xl bg-white/70 ring-1 ring-blue-200 p-6 flex flex-col gap-2 darkness:bg-[#0f2645] darkness:bg-opacity-60 darkness:ring-[#113c6e]">
            <div class="flex items-center gap-2 text-slate-800 font-medium text-lg darkness:text-slate-100">
                <i data-lucide="activity" class="w-5 h-5 text-blue-500 darkness:text-sky-400"></i>Recent Activity
            </div>
            <p class="text-slate-600 text-sm darkness:text-slate-100">No recent activity yet.</p>
        </div>
    </section>
@endsection