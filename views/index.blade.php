<!DOCTYPE html>
<html lang="{{ManagerTheme::getLang()}}" dir="{{ManagerTheme::getTextDir()}}">
<head>
    <title>{{$tabName}} @lang('sSeo::global.title') - Evolution CMS</title>
    <base href="{{EVO_MANAGER_URL}}">
    <meta http-equiv="Content-Type" content="text/html; charset={{ManagerTheme::getCharset()}}"/>
    <meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width"/>
    <meta name="theme-color" content="#0b1a2f"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link rel="icon" type="image/svg+xml" href="{{asset('site/sseo.svg')}}" />
    <style>[x-cloak]{display:none!important}</style>
    <link rel="stylesheet" href="{{asset('site/sseo.min.css')}}?{{evo()->getConfig('sSeoVer')}}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@latest/build/css/alertify.min.css"/>
    @if(class_exists(Tracy\Debugger::class) && config('tracy.active')){!!Tracy\Debugger::renderLoader()!!}@endif
    {!!ManagerTheme::getMainFrameHeaderHTMLBlock()!!}
    <script defer src="https://unpkg.com/alpinejs@latest"></script>
    <script defer src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alertifyjs@latest/build/alertify.min.js"></script>
    <script>
        if (!evo){var evo = {};}
        if (!evo.config){evo.config = {};}
        var actions,actionStay = [],dontShowWorker = false,documentDirty = false,timerForUnload,managerPath = '';
        evo.lang = {!!json_encode(Illuminate\Support\Arr::only(
            ManagerTheme::getLexicon(),
            ['saving', 'error_internet_connection', 'warning_not_saved']
        ))!!};
        evo.style = {!!json_encode(Illuminate\Support\Arr::only(
            ManagerTheme::getStyle(),
            ['icon_file', 'icon_pencil', 'icon_reply', 'icon_plus']
        ))!!};
        evo.MODX_MANAGER_URL = '{{EVO_MANAGER_URL}}';
        evo.config.which_browser = '{{evo()->getConfig('which_browser')}}';
    </script>
    <script src="media/script/main.js"></script>
    <script src="{{asset('site/sseo.js')}}?{{evo()->getConfig('sSeoVer')}}"></script>
    @stack('scripts.top')
    {!!EvolutionCMS()->getRegisteredClientStartupScripts()!!}
</head>
<body class="{{ManagerTheme::getTextDir()}} {{ManagerTheme::getThemeStyle()}}" data-evocp="color">
<h1 style="display:none"><i class="@lang('sSeo::global.icon')"></i> {{$tabName}} @lang('sSeo::global.title')</h1>
<div x-data="sSeo.sPinner('sSidebarPinned')" class="s-document">
    @include('sSeo::partials.menu')
    <main :class="open?'ml-60':'ml-16'" class="flex-1 min-h-screen transition-all duration-300">
        <header class="s-header">
            <div class="flex items-center gap-2">{!!$tabIcon!!} <h2 class="s-header-title">{{$tabName}}</h2></div>
            <div class="flex items-center gap-3">@section('header')@show</div>
        </header>
        @section('content')@show
    </main>
</div>
<div x-data="{open:false}" @mouseenter="open=true" @mouseleave="open=false" :class="open ? 's-brand s-brand--open' : 's-brand'">
    <div class="s-brand-logo">
        <img src="{{asset('site/seigerit.svg')}}" alt="Seiger IT">
    </div>
    <template x-if="open">
        <div x-transition.opacity class="s-brand-text">
            <a href="https://seiger.github.io/sSeo" target="_blank" class="s-brand-link">sSeo</a>
            &nbsp;|&nbsp;
            <a href="https://seigerit.com" target="_blank" class="s-brand-link">Seiger IT</a>
        </div>
    </template>
</div>
<script src="{{asset('site/seigerit.tooltip.js')}}" defer></script>
@push('scripts.bot')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            @if(session('success'))
            alertify.success("{{session('success')}}", 10);
            @endif

            @if(session('error'))
            alertify.error("{{session('error')}}", 10);
            @endif
        });
    </script>
@endpush
@stack('scripts.bot')
@include('manager::partials.debug')
{!!evo()->getRegisteredClientScripts()!!}
</body>
</html>
