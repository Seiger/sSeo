{!!'<'.'?xml version="1.0" encoding="UTF-8"?>'!!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @php
        $escapePages = []; // The ID of the pages to exclude from the result
        $escapePageChilds = []; // The ID of the parent pages whose child resources should be excluded from the result
        $pages = EvolutionCMS\Models\SiteContent::wherePublished(1)
            ->whereDeleted(0)
            ->whereSearchable(1)
            ->whereNotIn('id', $escapePages)
            ->whereNotIn('parent', $escapePageChilds)
            ->get();
    @endphp
    @foreach($pages as $page)
        <url>
            <loc>{{url($page->id, '', '', 'full')}}</loc>
            <lastmod>{{Carbon\Carbon::parse($page->editedon)->toIso8601String()}}</lastmod>
            <changefreq>always</changefreq>
            @if($page->id == evo()->getConfig('site_start', 1))
                <priority>1.0</priority>
            @elseif($page->parent == 0)
                <priority>0.8</priority>
            @else
                <priority>0.6</priority>
            @endif
        </url>
    @endforeach
</urlset>
