@php($isNew = \Carbon\Carbon::parse($item->created_at)->gt(now()->subDay()))
<tr id="{{$item->id}}"
    @class([
        'transition-colors',
        'duration-150',
        'hover:bg-sky-50',
        'darkness:hover:bg-[#1a2f44]',
        'bg-yellow-50' => $isNew,
        'darkness:bg-yellow-950' => $isNew
])>
    <td class="px-4 py-2">{{$item->old_url}}</td>
    <td class="px-4 py-2">{{$item->new_url}}</td>
    <td class="px-4 py-2">
        @switch($item->type)
            @case(301)301 - Permanent @break
            @case(302)302 - Temporary @break
            @case(307)307 - Temporary (Keep Method) @break
            @default Unknown @break
        @endswitch
    </td>
    @if (evo()->getConfig('check_sMultisite', false))
        @foreach($availableSites as $site)
            @if($item->site_key == $site->key)<td class="px-4 py-2">{{$site->site_name}}</td>@endif
        @endforeach
    @endif
    <td class="px-4 py-2 text-center">
        <button class="text-rose-600 hover:text-rose-800" onclick="deleteRedirect({{$item->id}}, '{{$item->old_url}}');">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>
    </td>
</tr>