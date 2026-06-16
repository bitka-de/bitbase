@extends('layouts.default')

@section('content')
    <article class="container-page surface" style="padding: 1rem;">
        <x-breadcrumbs :page="$page" />

        <header class="stack-sm" style="margin-bottom: 1rem;">
            <h1>{{ $page->h1 ?: $page->title }}</h1>

            @if (! empty($page->excerpt))
                <p class="home-lead">{{ $page->excerpt }}</p>
            @endif
        </header>

        <section>
            {!! $page->content !!}
        </section>
    </article>
@endsection
