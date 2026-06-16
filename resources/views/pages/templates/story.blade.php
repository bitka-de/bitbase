@extends('layouts.default')

@section('content')
    <article class="container-page" style="padding: 0.7rem 1rem 1.3rem;">
        <x-breadcrumbs :page="$page" />

        <header class="surface" style="padding: 1.2rem; margin-bottom: 1rem; background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(242, 247, 255, 0.9));">
            <p style="margin: 0 0 0.4rem; font-size: 0.78rem; letter-spacing: 0.08em; text-transform: uppercase; color: #61708a;">Story Layout</p>
            <h1 style="margin: 0; font-size: clamp(1.7rem, 3.5vw, 2.5rem); line-height: 1.14;">{{ $page->h1 ?: $page->title }}</h1>

            @if (! empty($page->excerpt))
                <p class="home-lead" style="margin-top: 0.7rem;">{{ $page->excerpt }}</p>
            @endif
        </header>

        <section class="surface" style="padding: 1rem 1.05rem;">
            {!! $page->content !!}
        </section>
    </article>
@endsection
