<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login') | {{ config('app.name') }}</title>
    @vite(['resources/css/auth.css', 'resources/js/app.js'])
</head>
<body>
    <main class="auth-shell">
        <section class="surface auth-panel {{ $errors->any() ? 'is-shaking' : '' }}">
            @yield('content')
        </section>
    </main>
</body>
</html>