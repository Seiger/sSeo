<aside :class="open ? 'w-60' : 'w-16'" class="s-nav" @mouseenter="handleEnter" @mouseleave="handleLeave">
    <div class="s-nav-header">
        <a href="{{sSeo::route('sSeo.dashboard')}}" class="flex items-center gap-1 text-xl font-bold" x-show="open" x-cloak>sSeo
            @if(evo()->getConfig('sseo_pro', false))<span class="s-pro-badge">Pro</span>@endif
        </a>
        <img x-show="!open" x-cloak src="{{asset('site/sseo.svg')}}" class="w-8 h-8 pointer-events-none filter drop-shadow-[0_0_6px_#3b82f6]" alt="sSeo">
    </div>
    <nav class="s-nav-menu">
        <a href="{{sSeo::route('sSeo.dashboard')}}" @class(['s-nav-menu-item', 's-nav-menu-item--active' => 'sSeo.dashboard' == Route::currentRouteName()])>
            @svg('tabler-layout-dashboard', 'w-6 h-6')
            <span x-show="open">@lang('sSeo::global.dashboard')</span>
        </a>
        @if(config('seiger.settings.sSeo.redirects_enabled', 0) == 1)
            <a href="{{sSeo::route('sSeo.redirects')}}" @class(['s-nav-menu-item', 's-nav-menu-item--active' => 'sSeo.redirects' == Route::currentRouteName()])>
                @svg('tabler-refresh', 'w-6 h-6')
                <span x-show="open">@lang('sSeo::global.redirects')</span>
            </a>
        @endif
        @if(evo()->getConfig('sseo_pro', false))
            <a href="{{sSeo::route('sSeo.templates')}}" @class(['s-nav-menu-item', 's-nav-menu-item--active' => 'sSeo.templates' == Route::currentRouteName()])>
                @svg('tabler-file-text', 'w-6 h-6')
                <span x-show="open">@lang('sSeo::global.templates')</span>
            </a>
        @endif
        <a href="{{sSeo::route('sSeo.robots')}}" @class(['s-nav-menu-item', 's-nav-menu-item--active' => 'sSeo.robots' == Route::currentRouteName()])>
            @svg('tabler-brand-tabler', 'w-6 h-6')
            <span x-show="open">@lang('sSeo::global.robots')</span>
        </a>
        {{--<a href="#" class="s-nav-menu-item"><i data-lucide="network" class="w-5 h-5"></i><span x-show="open">Sitemap</span></a>
        <a href="#" class="s-nav-menu-item"><i data-lucide="bar-chart-3" class="w-5 h-5"></i><span x-show="open">Insights</span></a>--}}
        <a href="{{sSeo::route('sSeo.configure')}}" @class(['s-nav-menu-item', 's-nav-menu-item--active' => 'sSeo.configure' == Route::currentRouteName()])>
            @svg('tabler-settings', 'w-6 h-6')
            <span x-show="open">@lang('sSeo::global.configure')</span>
        </a>
    </nav>
    <span @click="toggle()" role="button" tabindex="0" class="s-pin-btn" :class="open ? 'left-24' : 'left-4'" title="Toggle sidebar">
        <template x-if="open">
            @svg('tabler-pinned', 'w-4 h-4 pointer-events-none')
        </template>
        <template x-if="!open">
            @svg('tabler-pin', 'w-4 h-4 pointer-events-none')
        </template>
    </span>
</aside>