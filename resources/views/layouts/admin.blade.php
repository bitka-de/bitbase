<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('meta_title', 'Admin | ' . config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', 'Adminbereich')">
    <meta name="robots" content="noindex,nofollow">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    @vite(['resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <div class="admin-app">
        <aside class="admin-sidebar" aria-label="Admin Navigation">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">{{ config('app.name') }} Admin</a>

            @include('partials.admin.sidebar-nav')
        </aside>

        <main class="admin-content-wrap">
            @include('partials.admin.header', [
                'title' => trim($__env->yieldContent('admin_title', 'Dashboard')),
                'subtitle' => trim($__env->yieldContent('admin_subtitle', '')),
            ])

            @yield('content')
        </main>
    </div>
</body>
</html>
