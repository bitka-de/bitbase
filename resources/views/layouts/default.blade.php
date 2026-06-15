<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('meta_title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', 'Willkommen auf unserer Website.')">
    <meta name="robots" content="@yield('meta_robots', 'index,follow')">

    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="@yield('og_title', $__env->yieldContent('meta_title', config('app.name')))">
    <meta property="og:description" content="@yield('og_description', $__env->yieldContent('meta_description', 'Willkommen auf unserer Website.'))">
    <meta property="og:url" content="@yield('og_url', $__env->yieldContent('canonical_url', url()->current()))">
    <meta property="og:image" content="@yield('og_image', asset('favicon.ico'))">

    <meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">
    <meta name="twitter:title" content="@yield('twitter_title', $__env->yieldContent('og_title', $__env->yieldContent('meta_title', config('app.name'))))">
    <meta name="twitter:description" content="@yield('twitter_description', $__env->yieldContent('og_description', $__env->yieldContent('meta_description', 'Willkommen auf unserer Website.')))">
    <meta name="twitter:image" content="@yield('twitter_image', $__env->yieldContent('og_image', asset('favicon.ico')))">

    @include('partials.seo.json-ld')

    @yield('meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="site-shell">
        @include('partials.layout.header')
        <main class="layout-main" id="main-content">
            @yield('content')
        </main>
        @include('partials.layout.footer')
    </div>
</body>
</html>