@extends('layouts.admin')

@section('meta_title', 'Seite erstellen | ' . config('app.name'))
@section('meta_description', 'Neue CMS-Seite im Adminbereich erstellen.')
@section('canonical_url', route('admin.pages.create'))
@section('admin_title', 'Neue Seite')
@section('admin_subtitle', 'Lege eine neue CMS-Seite an')

@section('content')
    <section class="admin-section" aria-label="Seite erstellen">
        <div class="admin-section-head">
            <h2 class="admin-section-title">Neue Seite erstellen</h2>
        </div>

        <form method="POST" action="{{ route('admin.pages.store') }}">
            @csrf
            @include('pages.admin.pages._form')
        </form>
    </section>
@endsection