<aside :class="open ? 'w-60' : 'w-16'" class="s-nav" @mouseenter="handleEnter" @mouseleave="handleLeave">
    <div class="s-nav-header">
        <a href="{{sSeo::route('sSeo.dashboard')}}" class="flex items-center gap-1 text-xl font-bold" x-show="open" x-cloak>sSeo
            @if(evo()->getConfig('sseo_pro', false))<span class="s-pro-badge">Pro</span>@endif
        </a>
        <svg x-show="!open" x-cloak class="w-8 h-8 pointer-events-none filter drop-shadow-[0_0_6px_#3b82f6]" fill="none" viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg">
            <path d="m18.75 18.75c0-5.1855-4.1895-9.375-9.375-9.375-5.1856 0-9.375 4.1895-9.375 9.375v98.438c0 12.949 10.488 23.437 23.438 23.437h117.19c5.186 0 9.375-4.189 9.375-9.375s-4.189-9.375-9.375-9.375h-117.19c-2.5781 0-4.6875-2.109-4.6875-4.687v-98.438zm119.12 25.371c3.662-3.6621 3.662-9.6094 0-13.272s-9.609-3.6621-13.271 0l-30.85 30.879-16.816-16.816c-3.6621-3.6621-9.6094-3.6621-13.272 0l-32.812 32.812c-3.6621 3.6621-3.6621 9.6094 0 13.272s9.6094 3.6621 13.272 0l26.191-26.162 16.816 16.816c3.6621 3.6621 9.6094 3.6621 13.271 0l37.5-37.5-0.029-0.0293z" fill="#0B78FF"/>
        </svg>
    </div>
    <nav class="s-nav-menu">
        <a href="{{sSeo::route('sSeo.dashboard')}}" @class(['s-nav-menu-item', 's-nav-menu-item--active' => 'sSeo.dashboard' == Route::currentRouteName()])>
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span x-show="open">@lang('sSeo::global.dashboard')</span>
        </a>
        @if(config('seiger.settings.sSeo.redirects_enabled', 0) == 1)
            <a href="{{sSeo::route('sSeo.redirects')}}" @class(['s-nav-menu-item', 's-nav-menu-item--active' => 'sSeo.redirects' == Route::currentRouteName()])>
                <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                <span x-show="open">@lang('sSeo::global.redirects')</span>
            </a>
        @endif
        @if(evo()->getConfig('sseo_pro', false))
            <a href="{{sSeo::route('sSeo.templates')}}" @class(['s-nav-menu-item', 's-nav-menu-item--active' => 'sSeo.templates' == Route::currentRouteName()])>
                <i data-lucide="file-text" class="w-5 h-5"></i>
                <span x-show="open">@lang('sSeo::global.templates')</span>
            </a>
        @endif
        <a href="{{sSeo::route('sSeo.robots')}}" @class(['s-nav-menu-item', 's-nav-menu-item--active' => 'sSeo.robots' == Route::currentRouteName()])>
            <i data-lucide="file-terminal" class="w-5 h-5"></i>
            <span x-show="open">@lang('sSeo::global.robots')</span>
        </a>
        {{--<a href="#" class="s-nav-menu-item"><i data-lucide="network" class="w-5 h-5"></i><span x-show="open">Sitemap</span></a>
        <a href="#" class="s-nav-menu-item"><i data-lucide="bar-chart-3" class="w-5 h-5"></i><span x-show="open">Insights</span></a>--}}
        <a href="{{sSeo::route('sSeo.configure')}}" @class(['s-nav-menu-item', 's-nav-menu-item--active' => 'sSeo.configure' == Route::currentRouteName()])>
            <i data-lucide="settings" class="w-5 h-5"></i>
            <span x-show="open">@lang('sSeo::global.configure')</span>
        </a>
    </nav>
    <span @click="togglePin" role="button" tabindex="0" class="s-pin-btn" :class="open ? 'left-24' : 'left-4'" title="Pin sidebar / Unpin sidebar">
        <i :data-lucide="pinned ? 'pin-off' : 'pin'" class="w-4 h-4 pointer-events-none"></i>
    </span>
</aside>