@extends('manager::template.page')
@section('content')
    <h1><i class="@lang('sSeo::global.icon')" data-tooltip="@lang('sSeo::global.description')"></i>@lang('sSeo::global.title')</h1>
    <div class="sectionBody">
        <div class="tab-pane" id="resourcesPane">
            <script>tpResources = new WebFXTabPane(document.getElementById('resourcesPane'), false);</script>
            <div class="tab-page configureTab" id="configureTab">
                <h2 class="tab">
                    <a href="{{sSeo::route('sSeo.index')}}">
                        <span><i class="@lang('sSeo::global.configure_icon')" data-tooltip="@lang('sSeo::global.configure_help')"></i> @lang('sSeo::global.configure')</span>
                    </a>
                </h2>
                <script>tpResources.addTabPage(document.getElementById('configureTab'));</script>
                <div class="container container-body">
                    @include('sSeo::configureTab')
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts.bot')
    <script>function saveForm(selector){$(selector).submit()}</script>
    <style>
        #copyright{position:fixed;bottom:0;right:0;}
        #copyright img{width:35px;}
    </style>
    <div id="copyright"><a href="https://seigerit.com/" target="_blank"><img src="{{evo()->getConfig('site_url', '/')}}assets/site/seigerit-blue.svg"/></a></div>
@endpush
