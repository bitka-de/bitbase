@extends('layouts.admin')

@section('meta_title', 'Seite bearbeiten | ' . config('app.name'))
@section('meta_description', 'Bestehende CMS-Seite im Adminbereich bearbeiten.')
@section('canonical_url', route('admin.pages.edit', $page))
@section('admin_title', 'Seite bearbeiten')
@section('admin_subtitle', 'Inhalt und Metadaten der Seite aktualisieren')

@section('content')
    <section class="admin-section" aria-label="Seite bearbeiten">
        <div class="admin-section-head">
            <h2 class="admin-section-title">{{ $page->title }}</h2>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary">Zurueck zur Liste</a>
        </div>

        <form method="POST" action="{{ route('admin.pages.update', $page) }}">
            @csrf
            @method('PUT')
            @include('pages.admin.pages._form', ['page' => $page])
        </form>
    </section>
@endsection