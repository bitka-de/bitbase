@extends('layouts.default')

@section('content')
    <article class="container-page" style="max-width: 760px; padding: 1.2rem 1rem; margin: 0 auto;">
        <x-breadcrumbs :page="$page" />

        <header style="margin: 0 0 1.1rem;">
            <h1 style="font-size: clamp(1.6rem, 3vw, 2.2rem); line-height: 1.2; margin: 0;">{{ $page->h1 ?: $page->title }}</h1>

            @if (! empty($page->excerpt))
                <p class="home-lead" style="margin-top: 0.55rem;">{{ $page->excerpt }}</p>
            @endif
        </header>

        <section style="font-size: 1.03rem; line-height: 1.75;">
            {!! $page->content !!}
        </section>
    </article>
@endsection
