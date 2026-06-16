@extends('layouts.admin')

@section('meta_title', 'Seiten verwalten | ' . config('app.name'))
@section('meta_description', 'CRUD-Verwaltung fuer CMS-Seiten im Adminbereich.')
@section('canonical_url', route('admin.pages.index'))
@section('admin_title', 'Seiten')
@section('admin_subtitle', 'CMS-Seiten erstellen, bearbeiten und loeschen')

@section('content')
    <section class="admin-section" aria-label="Seitenliste">
        @php
            $statusValues = $pages->getCollection()->map(static fn ($page) => $page->status?->value ?? 'draft');
            $publishedCount = $statusValues->filter(static fn ($status) => $status === 'published')->count();
            $draftCount = $statusValues->filter(static fn ($status) => $status === 'draft')->count();
        @endphp

        <div class="admin-section-head">
            <div>
                <h2 class="admin-section-title">Alle Seiten</h2>
                <p class="help-text">Redaktion, SEO und Status auf einen Blick.</p>
            </div>
            <a href="{{ route('admin.pages.create') }}" class="cms-action-icon-btn is-primary" aria-label="Neue Seite erstellen" title="Neue Seite erstellen">
                <x-heroicon-o-plus class="cms-action-icon" aria-hidden="true" />
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        <div class="cms-list-head">
            <div class="cms-list-kpi">
                <span class="cms-list-kpi-icon" aria-hidden="true">
                    <x-heroicon-o-list-bullet class="cms-kpi-icon" aria-hidden="true" />
                </span>
                <div>
                    <strong>{{ $pages->total() }}</strong>
                    <span>Seiten gesamt</span>
                </div>
            </div>
            <div class="cms-list-kpi is-live">
                <span class="cms-list-kpi-icon" aria-hidden="true">
                    <x-heroicon-o-check-circle class="cms-kpi-icon" aria-hidden="true" />
                </span>
                <div>
                    <strong>{{ $publishedCount }}</strong>
                    <span>Published</span>
                </div>
            </div>
            <div class="cms-list-kpi is-draft">
                <span class="cms-list-kpi-icon" aria-hidden="true">
                    <x-heroicon-o-document-text class="cms-kpi-icon" aria-hidden="true" />
                </span>
                <div>
                    <strong>{{ $draftCount }}</strong>
                    <span>Draft</span>
                </div>
            </div>
        </div>

        <div class="cms-list cms-list-premium" style="margin-top: 0.6rem;">
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            <span class="cms-th-label">
                                <x-heroicon-o-document-text class="cms-th-icon" aria-hidden="true" />
                                Titel
                            </span>
                        </th>
                        <th>
                            <span class="cms-th-label">
                                <x-heroicon-o-link class="cms-th-icon" aria-hidden="true" />
                                URL
                            </span>
                        </th>
                        <th>
                            <span class="cms-th-label">
                                <x-heroicon-o-check-badge class="cms-th-icon" aria-hidden="true" />
                                Status
                            </span>
                        </th>
                        <th>
                            <span class="cms-th-label">
                                <x-heroicon-o-calendar-days class="cms-th-icon" aria-hidden="true" />
                                Aktualisiert
                            </span>
                        </th>
                        <th>
                            <span class="cms-th-label">
                                <x-heroicon-o-cog-6-tooth class="cms-th-icon" aria-hidden="true" />
                                Aktionen
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        @php
                            $status = $page->status?->value ?? 'draft';
                            $layout = (string) ($page->template ?: 'default');
                            $layoutInitial = strtoupper(substr($layout, 0, 1));
                        @endphp
                        <tr>
                            <td>
                                <div class="cms-row-title-wrap">
                                    <span class="cms-row-glyph" aria-hidden="true">{{ $layoutInitial }}</span>
                                    <div>
                                        <div class="cms-row-title">{{ $page->title }}</div>
                                        <div class="cms-row-meta">{{ strtoupper($page->locale) }} · Layout: {{ ucfirst($layout) }}</div>
                                        <div class="cms-row-submeta">Von {{ $page->author?->name ?? 'System' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="cms-row-link">
                                    <code>/{{ $page->slug_path }}</code>
                                </div>
                            </td>
                            <td>
                                @if ($status === 'published')
                                    <span class="admin-status is-live">Published</span>
                                @elseif ($status === 'scheduled')
                                    <span class="admin-status is-active">Scheduled</span>
                                @elseif ($status === 'archived')
                                    <span class="admin-status is-draft">Archived</span>
                                @else
                                    <span class="admin-status is-draft">Draft</span>
                                @endif
                            </td>
                            <td>{{ $page->updated_at?->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="cms-actions-row">
                                    <a href="{{ route('pages.show', ['slugPath' => $page->slug_path]) }}" target="_blank" rel="noopener" class="cms-action-icon-btn" aria-label="Seite oeffnen" title="Oeffnen">
                                        <x-heroicon-o-arrow-top-right-on-square class="cms-action-icon" aria-hidden="true" />
                                    </a>

                                    <a href="{{ route('admin.pages.edit', $page) }}" class="cms-action-icon-btn" aria-label="Seite bearbeiten" title="Bearbeiten">
                                        <x-heroicon-o-pencil-square class="cms-action-icon" aria-hidden="true" />
                                    </a>

                                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Seite wirklich loeschen?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cms-action-icon-btn is-danger" aria-label="Seite loeschen" title="Loeschen">
                                            <x-heroicon-o-trash class="cms-action-icon" aria-hidden="true" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="cms-empty-state">
                                    <strong>Noch keine Seiten vorhanden.</strong>
                                    <p class="help-text">Lege jetzt deine erste CMS-Seite mit einer Vorlage an.</p>
                                    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">Erste Seite anlegen</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pages->hasPages())
            <div style="margin-top: 0.8rem;">
                {{ $pages->links() }}
            </div>
        @endif
    </section>
@endsection