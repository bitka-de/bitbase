@extends('layouts.admin')

@section('meta_title', 'Komponente erstellen | ' . config('app.name'))
@section('meta_description', 'Neue Inhaltskomponente im Adminbereich erstellen.')
@section('canonical_url', route('admin.components.create'))
@section('admin_title', 'Neue Komponente')
@section('admin_subtitle', 'Wiederverwendbaren HTML Baustein anlegen')

@section('content')
    <section class="admin-section" aria-label="Komponente erstellen">
        <div class="admin-section-head">
            <h2 class="admin-section-title">Neue Komponente erstellen</h2>
        </div>

        <form method="POST" action="{{ route('admin.components.store') }}">
            @csrf
            @include('pages.admin.components._form')
        </form>
    </section>
@endsection