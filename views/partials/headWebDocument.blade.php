<!-- Primary Meta Tags -->
    <title>@if(isset($title) && trim($title)){{$title}}@else{{evo()->documentName}}@endif</title>
@if(isset($description) && trim($description))
    <meta name="description" content="{{$description}}"/>
@endif
@if(isset($robots) && is_array($robots) && $robots['show'] == true)
    <meta name="robots" content="{{$robots['value']}}"/>
@endif
