{!!'<'.'?xml version="1.0" encoding="UTF-8"?>'!!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@php($escapePages = []) {{-- The ID of the pages to exclude from the result --}}
@php($escapePageChilds = []) {{-- The ID of the parent pages whose child resources should be excluded from the result --}}
@php($pages = EvolutionCMS\Models\SiteContent::wherePublished(1)->whereDeleted(0)->whereSearchable(1)->whereNotIn('id', $escapePages)->whereNotIn('parent', $escapePageChilds)->get())
@php($articles = Seiger\sArticles\Models\sArticle::active()->get())
@foreach(sLang::langFront() as $lang)
    @if ($lang != sLang::langDefault())@php(evo()->setConfig('virtual_dir', $lang.'/'))@endif
    @foreach($pages as $page)
        <url><loc>{{url($page->id, '', '', 'full')}}</loc><lastmod>{{Carbon\Carbon::parse($page->editedon)->toIso8601String()}}</lastmod><changefreq>always</changefreq><priority>0.8</priority></url>
    @endforeach
    @foreach($articles as $article)
        <url><loc>{{$article->link}}</loc><lastmod>{{Carbon\Carbon::parse($article->updated_at)->toIso8601String()}}</lastmod><changefreq>weekly</changefreq><priority>1.0</priority></url>
    @endforeach
    @foreach($books as $book)
        <url><loc>{{$book->link}}</loc><lastmod>{{Carbon\Carbon::parse($book->updated_at)->toIso8601String()}}</lastmod><changefreq>weekly</changefreq><priority>1.0</priority></url>
    @endforeach
@endforeach
</urlset>
