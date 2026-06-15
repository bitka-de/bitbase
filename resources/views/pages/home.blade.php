@extends('layouts.default')

@section('meta_title', 'Home | ' . config('app.name'))
@section('meta_description', 'Eine klare und elegante Startseite mit sauberer SEO-Basis und strukturierter Darstellung.')
@section('canonical_url', route('home'))
@section('og_title', 'Home | ' . config('app.name'))
@section('og_description', 'Klare Inhalte, modernes Layout und SEO-ready Meta-Daten auf der Startseite.')

@section('content')
    <section class="surface home-main boxed">
        <header class="home-hero">
            <span class="accent-badge">Clean · Einfach · Elegant</span>

            <h1 class="home-title">
                Willkommen bei {{ config('app.name') }}
            </h1>

            <p class="home-lead">
                Dieses Grundlayout ist so aufgebaut, dass jede Seite SEO-konform erweitert werden kann:
                mit Title, Description, Canonical, Open Graph und Twitter Meta Tags.
            </p>
        </header>

        <section class="home-grid">
            <article class="card">
                <h2 class="home-card-title">Default Layout</h2>
                <p class="home-card-text">
                    Einheitliche Struktur fur alle Seiten, inklusive zentraler Head-Definition.
                </p>
            </article>

            <article class="card">
                <h2 class="home-card-title">SEO Ready</h2>
                <p class="home-card-text">
                    Alle wichtigen Meta-Felder sind als Sections vorbereitet und flexibel uberschreibbar.
                </p>
            </article>

            <article class="card">
                <h2 class="home-card-title">Sauberes Design</h2>
                <p class="home-card-text">
                    Reduzierte Farbwelt, klare Typografie und ruhige Flachen fur einen eleganten Einstieg.
                </p>
            </article>
        </section>
    </section>
@endsection