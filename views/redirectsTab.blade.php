@extends('sSeo::index')
@section('header')
    <button class="s-btn s-btn--primary" onclick="openAddRedirectModal();" title="@lang('sSeo::global.add_redirect_help')">
        <i data-lucide="plus" class="w-4 h-4"></i>@lang('sSeo::global.add_redirect')
    </button>
    <div class="relative group">
        <input type="text" name="s" value="{{request()->input('s', '')}}" placeholder="Search…" class="s-input-search" />
        <i data-lucide="search" class="js_search absolute left-2 top-1.5 w-4 h-4 text-slate-500 darkness:text-slate-400"></i>
    </div>
@endsection
@section('content')
    <section class="py-3">
        <div class="overflow-x-auto border-t border-b border-slate-200 darkness:border-slate-800">
            <table id="redirectsTable" class="min-w-full text-sm text-left text-slate-700 bg-white darkness:text-slate-200 darkness:bg-[#122739]">
                <thead class="bg-sky-100 text-xs text-slate-700 uppercase tracking-wide darkness:bg-[#132a44] darkness:text-slate-200">
                <tr>
                    <th data-by="old_url" class="px-4 py-3 whitespace-nowrap cursor-pointer hover:text-blue-600">
                        <div class="flex items-center gap-1">@lang('sSeo::global.old_url')
                            <i data-lucide="chevrons-up-down" class="w-4 h-4"></i>
                        </div>
                    </th>
                    <th data-by="new_url" class="px-4 py-3 whitespace-nowrap cursor-pointer hover:text-blue-600">
                        <div class="flex items-center gap-1">@lang('sSeo::global.new_url')
                            <i data-lucide="chevrons-up-down" class="w-4 h-4"></i>
                        </div>
                    </th>
                    <th data-by="type" class="px-4 py-3 whitespace-nowrap cursor-pointer hover:text-blue-600">
                        <div class="flex items-center gap-1">@lang('sSeo::global.redirect_type')
                            <i data-lucide="chevrons-up-down" class="w-4 h-4"></i>
                        </div>
                    </th>
                    @if (evo()->getConfig('check_sMultisite', false))
                        <th data-by="site_key" class="px-4 py-3 whitespace-nowrap cursor-pointer hover:text-blue-600">
                            <div class="flex items-center gap-1">@lang('sSeo::global.site_key')
                                <i data-lucide="chevrons-up-down" class="w-4 h-4"></i>
                            </div>
                        </th>
                    @endif
                    <th class="px-4 py-3 whitespace-nowrap cursor-pointer hover:text-blue-600 text-center">
                        @lang('global.onlineusers_action')
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 darkness:divide-slate-600">
                @foreach($redirects as $redirect)
                    @include('sSeo::partials.redirects.tableRow', ['item' => $redirect])
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between mt-6 px-6">
            <div class="flex space-x-1 text-sm">{!!$redirects?->render()!!}</div>
            @if($redirects?->count() > 10)@include('sSeo::partials.perPageSelector')@endif
        </div>
    </section>
@endsection
@push('scripts.bot')
    <script>
        function openAddRedirectModal() {
            let formHtml = `
                <div class="m-2">
                    <label class="block text-sm font-medium mb-1">@lang('sSeo::global.old_url')
                        <span class="inline-flex items-center justify-center align-middle translate-y-[-2px] text-slate-400">
                            <i data-lucide="help-circle" data-tooltip="@lang('sSeo::global.old_url_help')" class="w-4 h-4 inline"></i>
                        </span>
                    </label>
                    <input name="old_url" type="text" class="w-full border rounded px-3 py-2 text-sm darkness:bg-slate-800" placeholder="old-url"/>
                </div>
                <div class="m-2">
                    <label class="block text-sm font-medium mb-1">@lang('sSeo::global.new_url')
                        <span class="inline-flex items-center justify-center align-middle translate-y-[-2px] text-slate-400 darkness:bg-slate-800">
                            <i data-lucide="help-circle" data-tooltip="@lang('sSeo::global.new_url_help')" class="w-4 h-4 inline"></i>
                        </span>
                    </label>
                    <input name="new_url" type="text" class="w-full border rounded px-3 py-2 text-sm darkness:bg-slate-800" placeholder="new-url"/>
                </div>
                <div class="m-2">
                    <label class="block text-sm font-medium mb-1">@lang('sSeo::global.redirect_type')
                        <span class="inline-flex items-center justify-center align-middle translate-y-[-2px] text-slate-400">
                            <i data-lucide="help-circle" data-tooltip="@lang('sSeo::global.redirect_type_help')" class="w-4 h-4 inline"></i>
                        </span>
                    </label>
                    <select name="redirect_type" class="w-full border rounded px-3 py-2 text-sm darkness:bg-slate-800">
                        <option value="301">301 - Permanent</option>
                        <option value="302">302 - Temporary</option>
                        <option value="307">307 - Temporary (Keep Method)</option>
                    </select>
                </div>
            `;
            @if (evo()->getConfig('check_sMultisite', false))
                formHtml = formHtml + `
                    <div class="m-2">
                        <label class="block text-sm font-medium mb-1">@lang('sSeo::global.site_key')
                            <span class="inline-flex items-center justify-center align-middle translate-y-[-2px] text-slate-400">
                                <i data-lucide="help-circle" class="w-4 h-4 inline"></i>
                            </span>
                        </label>
                        <select name="site_key" class="w-full border rounded px-3 py-2 text-sm darkness:bg-slate-800">
                            @foreach($availableSites ?? [] as $site)
                                <option value="{{$site->key}}">{{$site->site_name}}</option>
                          @endforeach
                        </select>
                    </div>
                `;
            @endif

            alertify.confirm()
                .set({
                    title: "<h3>@lang('sSeo::global.add_redirect')</h3>",
                    message: `
                        <form id="redirectForm">${formHtml}</form>
                        <p class="text-sm text-red-500 darkness:text-red-300 leading-snug mt-4 px-1 text-center max-w-xl mx-auto">
                            @lang('sSeo::global.message_for_large_number_redirects')
                        </p>
                     `,
                    onok: function () {
                        window.parent.document.getElementById('mainloader')?.classList.add('show');
                        let redirectForm = document.getElementById('redirectForm');
                        let formData = new FormData(redirectForm);

                        if (!formData.get('old_url') || !formData.get('new_url')) {
                            alertify.error("@lang('sSeo::global.error_empty_fields')");
                            window.parent.document.getElementById('mainloader')?.classList.remove('show');
                            return false;
                        }

                        (async () => {
                            let response = await window.sSeo.callApi('{!!sSeo::route('sSeo.aredirect')!!}', formData);

                            if (response.success === true) {
                                redirectForm.reset();
                                alertify.closeAll();
                                alertify.success("@lang('sSeo::global.redirect_added')");

                                const tableBody = document.querySelector('#redirectsTable tbody');
                                tableBody.insertAdjacentHTML('afterbegin', response.html);
                                window.sSeo.queueLucide();
                            } else {
                                alertify.error(response.message);
                            }

                            window.parent.document.getElementById('mainloader')?.classList.remove('show');
                        })();
                        return false;
                    },
                    oncancel: function () {
                        alertify.notify("@lang('sSeo::global.action_cancelled')");
                    }
                })
                .set('labels', {ok: "@lang('global.save')", cancel: "@lang('global.cancel')"})
                .set('closable', false)
                .set('transition', 'zoom')
                .set('defaultFocus', 'cancel')
                .set('notifier', 'delay', 5)
                .show();
            window.sSeo.queueLucide();
        }
        function deleteRedirect(id, uri) {
            let message = `
                <p class="text-sm text-red-500 darkness:text-red-300 leading-snug mt-4 px-1 text-center max-w-xl mx-auto">
                    @lang('sSeo::global.you_shure_delete_redirect')
                </p>
            `;
            message = message.replace(':uri', uri);

            alertify.confirm()
                .set({
                    title: "<h3>@lang('global.are_you_sure')</h3>",
                    message: message,
                    onok: function () {
                        window.parent.document.getElementById('mainloader')?.classList.add('show');

                        (async () => {
                            let formData = new FormData();
                            formData.append('id', id);
                            let response = await window.sSeo.callApi('{!!sSeo::route('sSeo.dredirect')!!}', formData, 'DELETE');

                            if (response.success === true) {
                                alertify.closeAll();
                                alertify.success("@lang('sSeo::global.redirect_deleted')");
                                document.getElementById(id)?.remove();
                            } else {
                                alertify.error(response.message);
                            }

                            window.parent.document.getElementById('mainloader')?.classList.remove('show');
                        })();
                        return false;
                    },
                    oncancel: function () {
                        alertify.notify("@lang('sSeo::global.action_cancelled')");
                    }
                })
                .set('labels', {ok: "@lang('global.yes')", cancel: "@lang('global.cancel')"})
                .set('closable', false)
                .set('transition', 'zoom')
                .set('defaultFocus', 'cancel')
                .set('notifier', 'delay', 5)
                .show();
        }
    </script>
@endpush
