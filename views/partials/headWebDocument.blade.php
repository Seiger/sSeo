<!-- Primary Meta Tags -->
    @if(isset($robots) && is_array($robots) && $robots['show'] == true)<meta name="robots" content="{{$robots['value']}}"/>@endif
