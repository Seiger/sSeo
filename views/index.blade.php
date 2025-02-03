@php($name = explode('.', Route::currentRouteName())[1] ?? 'index')
@extends('manager::template.page')
@section('content')
    <h1><i class="@lang('sSeo::global.icon')" data-tooltip="@lang('sSeo::global.description')"></i>@lang('sSeo::global.title')</h1>
    <div class="sectionBody">
        <div class="tab-pane" id="resourcesPane">
            <script>tpResources = new WebFXTabPane(document.getElementById('resourcesPane'), false);</script>
            @if(config('seiger.settings.sSeo.redirects_enabled', 0) == 1)
                <div class="tab-page redirectsTab" id="redirectsTab">
                    <h2 class="tab">
                        <a href="{{sSeo::route('sSeo.redirects')}}">
                            <span><i class="@lang('sSeo::global.redirects_icon')" data-tooltip="@lang('sSeo::global.redirects_help')"></i> @lang('sSeo::global.redirects')</span>
                        </a>
                    </h2>
                    <script>tpResources.addTabPage(document.getElementById('redirectsTab'));</script>
                    <div class="container container-body">
                        @if($name == 'redirects')
                            @include('sSeo::redirectsTab')
                            <script>tpResources.setSelectedTab('redirectsTab');</script>
                        @endif
                    </div>
                </div>
            @endif
            <div class="tab-page configureTab" id="configureTab">
                <h2 class="tab">
                    <a href="{{sSeo::route('sSeo.index')}}">
                        <span><i class="@lang('sSeo::global.configure_icon')" data-tooltip="@lang('sSeo::global.configure_help')"></i> @lang('sSeo::global.configure')</span>
                    </a>
                </h2>
                <script>tpResources.addTabPage(document.getElementById('configureTab'));</script>
                <div class="container container-body">
                    @if($name == 'index')
                        @include('sSeo::configureTab')
                        <script>tpResources.setSelectedTab('configureTab');</script>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts.bot')
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>
    <script>function saveForm(selector){$(selector).submit()}</script>
    <style>
        #copyright{position:fixed;bottom:0;right:0;}
        #copyright img{width:35px;}
    </style>
    <div id="copyright"><a href="https://seigerit.com/" target="_blank"><img src="{{evo()->getConfig('site_url', '/')}}assets/site/seigerit-blue.svg"/></a></div>
@endpush
