<title>{{ $meta['title'] }}</title>
<meta name="description" content="{{ $meta['description'] }}">
<meta name="robots" content="{{ $meta['robots'] }}">

@if (! empty($meta['canonical']) && ($meta['robots'] !== 'noindex,nofollow'))
    <link rel="canonical" href="{{ $meta['canonical'] }}">
@endif

<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta property="og:type" content="{{ $meta['og']['type'] }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:title" content="{{ $meta['og']['title'] }}">
<meta property="og:description" content="{{ $meta['og']['description'] }}">
<meta property="og:url" content="{{ $meta['og']['url'] }}">
<meta property="og:image" content="{{ $meta['og']['image'] }}">

<meta name="twitter:card" content="{{ $meta['twitter']['card'] }}">
<meta name="twitter:title" content="{{ $meta['twitter']['title'] }}">
<meta name="twitter:description" content="{{ $meta['twitter']['description'] }}">
<meta name="twitter:image" content="{{ $meta['twitter']['image'] }}">

@foreach ($meta['hreflang'] as $locale => $href)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $href }}">
@endforeach

@if (! empty($meta['hreflang']))
    <link rel="alternate" hreflang="x-default" href="{{ collect($meta['hreflang'])->first() }}">
@endif

@foreach ($schemas as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endforeach
