@if(!is_writable(EVO_CORE_PATH . 'custom/config/seiger/settings/sSeo.php'))<div class="alert alert-danger" role="alert">@lang('sSeo::global.not_writable')</div>@endif
<p class="text-danger text-monospace text-center">@lang('sSeo::global.message_for_large_number_redirects')</p>
<form id="form-redirects" name="form-redirects" method="post" enctype="multipart/form-data" action="{{sSeo::route('sSeo.update-redirects')}}" onsubmit="documentDirty=false;">
    <div class="row form-row form-element-input">
        <div class="col-12">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>@lang('sSeo::global.old_url')</th>
                    <th>@lang('sSeo::global.new_url')</th>
                    <th>@lang('sSeo::global.redirect_type')</th>
                    @if (evo()->getConfig('check_sMultisite', false))<th>@lang('sSeo::global.site_key')</th>@endif
                    <th>@lang('global.onlineusers_action')</th>
                </tr>
                </thead>
                <tbody id="redirects-table-body">
                @foreach($redirects as $index => $redirect)
                    <tr>
                        <td>
                            <input type="text" name="redirects[{{$index}}][old]" value="{{$redirect->old_url}}" class="form-control old-url" onchange="documentDirty=true;">
                        </td>
                        <td>
                            <input type="text" name="redirects[{{$index}}][new]" value="{{$redirect->new_url}}" class="form-control new-url" onchange="documentDirty=true;">
                        </td>
                        <td>
                            <select name="redirects[{{$index}}][type]" class="form-control" onchange="documentDirty=true;">
                                <option value="301" @if($redirect->type == 301) selected @endif>301 - Permanent</option>
                                <option value="302" @if($redirect->type == 302) selected @endif>302 - Temporary</option>
                                <option value="307" @if($redirect->type == 307) selected @endif>307 - Temporary (Keep Method)</option>
                            </select>
                        </td>
                        @if (evo()->getConfig('check_sMultisite', false))
                            <td>
                                <select name="redirects[{{$index}}][site_key]" class="form-control" onchange="documentDirty=true;">
                                    @foreach($availableSites as $site)
                                        <option value="{{$site->key}}" @if($redirect->site_key == $site->key) selected @endif>
                                            {{$site->site_name}}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        @endif
                        <td>
                            <button type="button" class="btn btn-danger" onclick="removeRedirectRow(this);">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="split my-3"></div>
</form>

@push('scripts.bot')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            @if(session('success'))
                alertify.success("{{ session('success') }}");
            @endif

            @if(session('error'))
                alertify.error("{{ session('error') }}");
            @endif
        });

        function openAddRedirectModal() {
            let siteSelectHtml = "";
            @if (evo()->getConfig('check_sMultisite', false))
                siteSelectHtml = `
                    <label>@lang('sSeo::global.site_key')</label>
                    <select id="new_site_key" class="form-control">
                        @foreach($availableSites as $site)
                    <option value="{{$site->key}}">{{$site->site_name}}</option>
                        @endforeach
                    </select>
                    <br>
                `;
            @endif

            alertify.confirm()
            .set({
                title: "<b>@lang('sSeo::global.add_redirect')</b>",
                message: `
                    <label>@lang('sSeo::global.old_url')</label>
                    <input id="new_old_url" type="text" class="form-control" />
                    <br>
                    <label>@lang('sSeo::global.new_url')</label>
                    <input id="new_new_url" type="text" class="form-control" />
                    <br>
                    <label>@lang('sSeo::global.redirect_type')</label>
                    <select id="new_redirect_type" class="form-control">
                        <option value="301">301 - Permanent</option>
                        <option value="302">302 - Temporary</option>
                        <option value="307">307 - Temporary (Keep Method)</option>
                    </select>
                    ${siteSelectHtml}
                `,
                onok: function () {
                    let oldUrl = document.getElementById("new_old_url").value.trim();
                    let newUrl = document.getElementById("new_new_url").value.trim();
                    let type = document.getElementById("new_redirect_type").value;
                    let siteKey = "";
                    @if (evo()->getConfig('check_sMultisite', false))
                        siteKey = document.getElementById("new_site_key").value;
                    @endif

                    if (!oldUrl || !newUrl) {
                        alertify.error("@lang('sSeo::global.error_empty_fields')");
                        return false;
                    }

                    let existingUrls = Array.from(document.querySelectorAll("#redirects-table-body input[name*='[old]']")).map(el => el.value.trim());
                    if (existingUrls.includes(oldUrl)) {
                        alertify.error("@lang('sSeo::global.error_duplicate_redirect')");
                        return false;
                    }

                    addRedirectRow(oldUrl, newUrl, type, siteKey);
                    alertify.success("@lang('sSeo::global.redirect_added')");
                },
                oncancel: function () {
                    alertify.error("@lang('sSeo::global.action_cancelled')");
                }
            })
            .set('labels', {ok: "@lang('global.save')", cancel: "@lang('global.cancel')"})
            .set('closable', false)
            .set('transition', 'zoom')
            .set('defaultFocus', 'cancel')
            .show();
            document.querySelector('.ajs-ok').classList.add('btn','btn-primary');
        }

        function addRedirectRow(oldUrl, newUrl, type, siteKey) {
            let index = document.querySelectorAll('#redirects-table-body tr').length;
            let siteField = "";

            @if (evo()->getConfig('check_sMultisite', false))
                siteField = `
                    <td>
                        <input type="hidden" name="redirects[${index}][site_key]" value="${siteKey}">
                        ${siteKey}
                    </td>
                `;
            @endif

            let row = `
                <tr>
                    <td><input type="text" name="redirects[${index}][old]" value="${oldUrl}" class="form-control" onchange="documentDirty=true;"></td>
                    <td><input type="text" name="redirects[${index}][new]" value="${newUrl}" class="form-control" onchange="documentDirty=true;"></td>
                    <td>
                        <select name="redirects[${index}][type]" class="form-control" onchange="documentDirty=true;">
                            <option value="301" ${type === '301' ? 'selected' : ''}>301 - Permanent</option>
                            <option value="302" ${type === '302' ? 'selected' : ''}>302 - Temporary</option>
                            <option value="307" ${type === '307' ? 'selected' : ''}>307 - Temporary (Keep Method)</option>
                        </select>
                    </td>
                    ${siteField}
                    <td><button type="button" class="btn btn-danger" onclick="removeRedirectRow(this);"><i class="fa fa-trash"></i></button></td>
                </tr>
            `;
            document.getElementById('redirects-table-body').insertAdjacentHTML('afterbegin', row);
        }

        function removeRedirectRow(button) {
            button.closest('tr').remove();
            documentDirty = true;
        }
    </script>

    <div id="actions">
        <div class="btn-group">
            <button id="Button2" class="btn btn-primary" title="@lang('sSeo::global.add_redirect_help')" onclick="openAddRedirectModal();">
                <i class="fa fa-plus"></i> <span>@lang('sSeo::global.add_redirect')</span>
            </button>
            <button id="Button1" class="btn btn-success" onclick="saveForm('#form-redirects');">
                <i class="fa fa-save"></i> <span>@lang('global.save')</span>
            </button>
        </div>
    </div>
@endpush
