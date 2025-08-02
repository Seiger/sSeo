@if ($paginator->hasPages())
    @php
        $parameters = request()->except(['q', 'page']);
        ksort($parameters);
        $parameters = count($parameters) ? '&' . http_build_query($parameters) : '';
    @endphp
    @if (!$paginator->onFirstPage())
        <a href="{{$paginator->url(1)}}{!!$parameters!!}" class="px-3 py-1 rounded hover:bg-slate-200 darkness:hover:bg-slate-700">«</a>
        <a href="{{$paginator->previousPageUrl()}}{!!$parameters!!}" class="px-3 py-1 rounded hover:bg-slate-200 darkness:hover:bg-slate-700">‹</a>
    @endif
    @foreach ($elements as $element)
        @if (is_string($element))
            <a class="px-3 py-1 rounded hover:bg-slate-200 darkness:hover:bg-slate-700">{{$element}}</a>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <a class="px-3 py-1 rounded bg-slate-300 hover:bg-slate-300 darkness:bg-slate-800 darkness:hover:bg-slate-700">{{$page}}</a>
                @else
                    <a href="{{$paginator->url($page)}}{!!$parameters!!}" class="px-3 py-1 rounded hover:bg-slate-200 darkness:hover:bg-slate-700">{{$page}}</a>
                @endif
            @endforeach
        @endif
    @endforeach
    @if ($paginator->hasMorePages())
        <a href="{{$paginator->nextPageUrl()}}{!!$parameters!!}" class="px-3 py-1 rounded hover:bg-slate-200 darkness:hover:bg-slate-700">›</a>
        <a href="{{$paginator->url($paginator->lastPage())}}{!!$parameters!!}" class="px-3 py-1 rounded hover:bg-slate-200 darkness:hover:bg-slate-700">»</a>
    @endif
@endif
