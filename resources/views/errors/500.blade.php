@extends('layouts.default')

@section('meta_title', '500 | ' . config('app.name'))
@section('meta_description', 'Interner Serverfehler. Bitte spaeter erneut versuchen.')
@section('meta_robots', 'noindex,nofollow')
@section('og_title', '500 | ' . config('app.name'))
@section('og_description', 'Interner Serverfehler. Bitte spaeter erneut versuchen.')

@section('content')
    <section class="surface boxed error404-main" aria-labelledby="error500-title">
        <div class="error404-grid">
            <div class="error404-copy stack-md">
                <span class="accent-badge">Serverfehler</span>

                <p class="error404-code" aria-hidden="true">500</p>

                <h1 id="error500-title" class="error404-title">
                    Da ist intern etwas schiefgelaufen.
                </h1>

                <p class="error404-lead">
                    Die Anfrage konnte gerade nicht verarbeitet werden. Bitte versuche es in einem Moment erneut.
                </p>

                <div class="error404-actions">
                    <a href="{{ route('home') }}" class="btn btn-primary">Zur Startseite</a>
                    <a href="{{ url()->current() }}" class="btn btn-secondary">Erneut versuchen</a>

                    @auth
                        @if (auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Zum Adminbereich</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary">Zum Login</a>
                    @endauth
                </div>
            </div>

            <aside class="card error404-help" aria-label="Schnelle Hilfe">
                <h2 class="error404-help-title">Was du tun kannst</h2>
                <p class="error404-help-text">Wenn der Fehler bleibt, pruefe es spaeter erneut oder melde ihn dem Support.</p>

                <div class="error404-help-links stack-sm">
                    <a href="{{ route('home') }}" class="soft-link">Startseite oeffnen</a>
                    <a href="{{ url()->current() }}" class="soft-link">Seite neu laden</a>

                    @auth
                        @if (auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="soft-link">Dashboard oeffnen</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="soft-link">Einloggen</a>
                    @endauth
                </div>
            </aside>
        </div>
    </section>
@endsection