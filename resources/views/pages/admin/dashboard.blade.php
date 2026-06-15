@extends('layouts.admin')

@section('meta_title', 'Admin Dashboard | ' . config('app.name'))
@section('meta_description', 'Einfaches Admin Dashboard.')
@section('canonical_url', route('admin.dashboard'))
@section('admin_title', 'Dashboard')
@section('admin_subtitle', 'Teamstatus, Inhalte und Aktionen auf einen Blick')

@section('content')
    <section class="admin-section admin-dashboard-intro" aria-label="Dashboard Intro">
        <p class="admin-dashboard-kicker">Ubersicht</p>
        <h2 class="admin-section-title">Guten Tag, {{ auth()->user()->name }}</h2>
        <p class="admin-dashboard-lead">Alle relevanten Signale aus Redaktion und System in einer kompakten Ansicht.</p>
    </section>

    <section class="admin-section" aria-label="Kennzahlen">
        <div class="admin-section-head">
            <h2 class="admin-section-title">Kennzahlen</h2>
            <a href="#" class="btn btn-secondary">Report exportieren</a>
        </div>

        <div class="admin-metrics">
            <div class="admin-metric is-accent">
                <span>Offene Aufgaben</span>
                <strong>12</strong>
                <small class="admin-kpi-trend is-up">+2 seit gestern</small>
            </div>
            <div class="admin-metric">
                <span>Neue Nutzer</span>
                <strong>38</strong>
                <small class="admin-kpi-trend is-up">Diese Woche</small>
            </div>
            <div class="admin-metric">
                <span>Veroffentlichte Inhalte</span>
                <strong>146</strong>
                <small>Gesamt</small>
            </div>
            <div class="admin-metric is-stable">
                <span>Systemstatus</span>
                <strong>Stabil</strong>
                <small class="admin-kpi-trend is-stable">Keine Warnungen</small>
            </div>
        </div>
    </section>

    <section class="admin-section" aria-label="Letzte Aktivitäten">
        <div class="admin-section-head">
            <h2 class="admin-section-title">Letzte Aktivitaten</h2>
            <a href="#" class="soft-link">Alle anzeigen</a>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Typ</th>
                        <th>Titel</th>
                        <th>Status</th>
                        <th>Datum</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Seite</td>
                        <td>Startseite aktualisiert</td>
                        <td><span class="admin-status is-live">Live</span></td>
                        <td>Heute</td>
                    </tr>
                    <tr>
                        <td>Beitrag</td>
                        <td>Release Notes Q2</td>
                        <td><span class="admin-status is-draft">Entwurf</span></td>
                        <td>Gestern</td>
                    </tr>
                    <tr>
                        <td>Benutzer</td>
                        <td>Neuer Redakteur</td>
                        <td><span class="admin-status is-active">Aktiv</span></td>
                        <td>14.06.2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-section" aria-label="Schnellaktionen">
        <h2 class="admin-section-title">Schnellaktionen</h2>

        <div class="admin-actions-row">
            <a href="#" class="admin-action-item">
                <x-heroicon-o-plus-circle class="admin-action-icon" aria-hidden="true" />
                <span>Neuen Beitrag erstellen</span>
            </a>

            <a href="#" class="admin-action-item is-soft">
                <x-heroicon-o-user-plus class="admin-action-icon" aria-hidden="true" />
                <span>Benutzer einladen</span>
            </a>

            <a href="#" class="admin-action-item is-soft">
                <x-heroicon-o-arrow-path class="admin-action-icon" aria-hidden="true" />
                <span>Cache leeren</span>
            </a>

            <a href="#" class="admin-action-item">
                <x-heroicon-o-cog-6-tooth class="admin-action-icon" aria-hidden="true" />
                <span>Einstellungen bearbeiten</span>
            </a>
        </div>
    </section>
@endsection