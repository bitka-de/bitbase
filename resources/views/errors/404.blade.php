@extends('layouts.default')

@section('meta_title', '404 | ' . config('app.name'))
@section('meta_description', 'Die angeforderte Seite wurde nicht gefunden.')
@section('meta_robots', 'noindex,nofollow')
@section('og_title', '404 | ' . config('app.name'))
@section('og_description', 'Die angeforderte Seite wurde nicht gefunden.')

@section('content')
    <section class="surface boxed error404-main" aria-labelledby="error404-title">
        <div class="error404-grid">
            <div class="error404-copy stack-md">
                <span class="accent-badge">Nicht gefunden</span>

                <p class="error404-code" aria-hidden="true">404</p>

                <h1 id="error404-title" class="error404-title">
                    Diese Seite gibt es nicht.
                </h1>

                <p class="error404-lead">
                    Der aufgerufene Inhalt wurde verschoben, entfernt oder war nie verfuegbar.
                </p>

                <p class="error404-path">
                    Aufgerufen: /{{ ltrim(request()->path(), '/') }}
                </p>

                <div class="error404-actions">
                    <a href="{{ route('home') }}" class="btn btn-primary">Zur Startseite</a>

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
                <h2 class="error404-help-title">Was jetzt sinnvoll ist</h2>
                <p class="error404-help-text">Pruefe die URL oder nutze einen der direkten Einstiege.</p>

                <div class="error404-help-links stack-sm">
                    <a href="{{ route('home') }}" class="soft-link">Startseite oeffnen</a>

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