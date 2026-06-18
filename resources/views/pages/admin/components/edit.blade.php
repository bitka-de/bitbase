@extends('layouts.admin')

@section('meta_title', 'Komponente bearbeiten | ' . config('app.name'))
@section('meta_description', 'Bestehende Inhaltskomponente im Adminbereich bearbeiten.')
@section('canonical_url', route('admin.components.edit', $component))
@section('admin_title', 'Komponente bearbeiten')
@section('admin_subtitle', 'Wiederverwendbaren HTML Baustein aktualisieren')

@section('content')
    <section class="admin-section" aria-label="Komponente bearbeiten">
        <div class="admin-section-head">
            <h2 class="admin-section-title">{{ $component->title }}</h2>
            <a href="{{ route('admin.components.index') }}" class="btn btn-secondary">Zurueck zur Liste</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.components.update', $component) }}">
            @csrf
            @method('PUT')
            @include('pages.admin.components._form', ['component' => $component])
        </form>
    </section>
@endsection