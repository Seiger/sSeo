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
                @include('sSeo::configureTab')
            </div>
        </div>
    </div>
@endsection
@push('scripts.bot')
    <script>function saveForm(selector){$(selector).submit()}</script>
    <style>
        .form-row .row-col {display:flex; flex-wrap:wrap; flex-direction:row; align-content:start; padding-right:0.75rem;}
        #copyright{position:fixed;bottom:0;right:0;background-color:#0057b8;padding:3px 7px;border-radius:5px;}
        #copyright img{width:9em;}
    </style>
    <div id="copyright"><a href="https://seigerit.com/" target="_blank"><img src="{{evo()->getConfig('site_url', '/')}}assets/site/seigerit-yellow.svg"/></a></div>
@endpush
