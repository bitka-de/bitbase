<header class="layout-header">
    <div class="surface layout-bar boxed">
        <a href="{{ route('home') }}" class="brand-mark" aria-label="{{ config('app.name') }} Startseite">
            <span class="brand-dot" aria-hidden="true"></span>
            <span>{{ config('app.name') }}</span>
        </a>

        <nav class="layout-nav" aria-label="Hauptnavigation">
            <a href="{{ route('home') }}" class="soft-link">Home</a>
            <a href="{{ route('stylebook') }}" class="soft-link">Stylebook</a>
            <a href="#" class="soft-link">Leistungen</a>
            <a href="#" class="soft-link">Kontakt</a>

            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="soft-link">Admin</a>
                @endif

                <span class="muted">{{ auth()->user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
            @endauth
        </nav>
    </div>
</header>