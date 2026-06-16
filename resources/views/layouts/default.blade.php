<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo-meta :page="$page ?? null" />

    @yield('meta')

    @vite(['resources/css/public.css', 'resources/js/app.js'])
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