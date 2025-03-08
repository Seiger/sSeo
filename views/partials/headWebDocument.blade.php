<!-- Primary Meta Tags -->
    <title>@if(isset($title) && trim($title)){{$title}}@else{{evo()->documentName}}@endif</title>
@if(isset($canonical) && is_array($canonical) && $canonical['show'] == true)
    <link rel="canonical" href="{{$canonical['value']}}">
@endif
    <meta name="description" content="{{$description}}"/>
    <meta name="keywords" content="{{$keywords}}"/>
@if(isset($robots) && is_array($robots) && $robots['show'] == true)
    <meta name="robots" content="{{$robots['value']}}"/>
@endif
