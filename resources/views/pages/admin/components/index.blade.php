@extends('layouts.admin')

@section('meta_title', 'Komponenten verwalten | ' . config('app.name'))
@section('meta_description', 'Wiederverwendbare Inhaltskomponenten im Adminbereich verwalten.')
@section('canonical_url', route('admin.components.index'))
@section('admin_title', 'Komponenten')
@section('admin_subtitle', 'Bausteine fuer den Preview Editor erstellen und pflegen')

@php
$componentPreviewPayload = $components->getCollection()
    ->values()
    ->map(function ($component) {
        return [
            'id' => $component->id,
            'name' => $component->name,
            'title' => $component->title,
            'description' => $component->description,
            'content' => $component->content,
            'css' => $component->css,
            'js' => $component->js,
        ];
    })
    ->all();
@endphp

@section('content')
<div class="admin-components-shell">
    <!-- Header Section -->
    <div class="admin-components-header">
        <div class="admin-components-header-top">
            <div class="admin-components-header-identity">
                <div class="admin-components-header-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="3" y="3" width="8" height="8" rx="1.5" />
                        <rect x="13" y="3" width="8" height="8" rx="1.5" />
                        <rect x="3" y="13" width="8" height="8" rx="1.5" />
                        <rect x="13" y="13" width="8" height="8" rx="1.5" />
                    </svg>
                </div>
                <div>
                    <h1 class="admin-components-title">Komponenten</h1>
                    <p class="admin-components-subtitle">{{ count($components) }} Bausteine &middot; <code>/name</code> im Editor einfügbar</p>
                </div>
            </div>

            <div class="admin-components-header-actions">
                <div class="admin-tc" role="toolbar" aria-label="Komponenten Transfer">
                    {{-- Export: Alle JSON --}}
                    <a href="{{ route('admin.components.export') }}" class="admin-tc-btn" title="Alle Komponenten als JSON exportieren" aria-label="Alle als JSON">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3v12" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M7 10l5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M5 21h14" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="admin-tc-label">JSON</span>
                    </a>

                    {{-- Export: ZIP --}}
                    <form method="POST" action="{{ route('admin.components.export-zip') }}" id="components-export-zip-form" class="admin-tc-form">
                        @csrf
                        <div id="components-export-zip-inputs"></div>
                        <div class="admin-tc-zip-wrap">
                            <button type="button" class="admin-tc-btn" id="components-zip-trigger" title="Als ZIP herunterladen" aria-label="Als ZIP herunterladen" aria-haspopup="true" aria-expanded="false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 8h14M5 8a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2M5 8V6a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M10 12v4m4-4v4" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="admin-tc-label">ZIP</span>
                            </button>
                            <div class="admin-tc-popover" id="components-zip-popover" hidden role="menu">
                                <button type="button" class="admin-tc-popover-opt" id="components-zip-all" role="menuitem">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M15 3h6m0 0v6m0-6L9 15" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Alle herunterladen
                                </button>
                                <div class="admin-tc-popover-divider"></div>
                                <button type="button" class="admin-tc-popover-opt" id="components-zip-pick" role="menuitem">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 11l3 3L22 4" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Auswahl treffen
                                </button>
                            </div>
                        </div>
                    </form>

                    <span class="admin-tc-sep" aria-hidden="true"></span>

                    {{-- Import --}}
                    <form method="POST" action="{{ route('admin.components.import') }}" enctype="multipart/form-data" id="components-import-form" class="admin-tc-form">
                        @csrf
                        <input type="file" name="components_files[]" id="components-import-file" class="admin-tc-file" accept="application/json,.json,.zip,application/zip,application/x-zip-compressed" multiple required>
                        <button type="button" class="admin-tc-btn" id="components-import-trigger" title="JSON / ZIP Dateien zum Import waehlen" aria-label="Dateien waehlen">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 21V9" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M7 14l5-5 5 5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M5 3h14" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="admin-tc-badge" id="components-import-meta" aria-live="polite" hidden></span>
                        </button>
                        <button type="submit" class="admin-tc-btn admin-tc-btn-confirm" id="components-import-submit" disabled title="Import starten" aria-label="Import starten">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12l5 5L20 7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </form>
                </div>

                <a href="{{ route('admin.components.create') }}" class="admin-components-btn-new">
                    <svg class="admin-components-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Neue Komponente
                </a>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="admin-components-search">
            <svg class="admin-components-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-width="2" stroke-linecap="round" d="m21 21-4.3-4.3M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" />
            </svg>

            <input
                type="text"
                placeholder="Komponenten durchsuchen..."
                class="admin-components-search-input"
                id="component-search"
            />

            <button type="button" class="admin-components-search-clear" onclick="document.getElementById('component-search').value = ''; filterComponents();">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </button>
        </div>

        @if (($availableTags ?? collect())->isNotEmpty())
            <div class="admin-components-tag-filters" id="component-tag-filters">
                <button type="button" class="admin-components-tag-filter is-active" data-filter-tag="all">Alle</button>
                @foreach ($availableTags as $tag)
                    <button type="button" class="admin-components-tag-filter" data-filter-tag="{{ $tag }}">#{{ $tag }}</button>
                @endforeach
            </div>
        @endif
    </div>

    @if (session('success'))
        <div class="admin-components-alert" role="status">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error') || $errors->has('components_files') || $errors->has('components_files.*'))
        <div class="admin-components-alert" role="alert" style="background: rgba(239, 68, 68, 0.12); border-color: rgba(239, 68, 68, 0.25); color: #991b1b;">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 9v4m0 4h.01M10.29 3.86l-8.2 14.2A2 2 0 0 0 3.8 21h16.4a2 2 0 0 0 1.71-2.94l-8.2-14.2a2 2 0 0 0-3.42 0Z" />
            </svg>
            {{ session('error') ?: ($errors->first('components_files') ?: $errors->first('components_files.*')) }}
        </div>
    @endif

    <script type="application/json" id="component-preview-payload">@json($componentPreviewPayload)</script>

    <!-- Selection Action Bar -->
    <div class="admin-select-bar" id="components-select-bar" hidden aria-live="polite">
        <div class="admin-select-bar-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span id="components-select-count">0 ausgewählt</span>
        </div>
        <div class="admin-select-bar-actions">
            <button type="button" class="admin-select-bar-cancel" id="components-select-cancel">Abbrechen</button>
            <button type="button" class="admin-select-bar-download" id="components-select-download" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v12" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M7 10l5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M5 21h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Als ZIP laden
            </button>
        </div>
    </div>

    <!-- Components Grid -->
    <div class="admin-components-container">
        @forelse ($components as $component)
            <div class="admin-component-card" data-component-name="{{ $component->name }}" data-component-title="{{ $component->title }}" data-component-tags="{{ collect($component->tags ?? [])->implode(' ') }}">
                <div class="admin-component-card-inner">
                    <!-- Avatar -->
                    <div class="admin-component-avatar-wrap">
                        <label class="admin-component-select-control" title="Fuer ZIP Export auswaehlen">
                            <input type="checkbox" class="admin-component-select" value="{{ $component->id }}">
                            <span></span>
                        </label>
                        <div class="admin-component-avatar">
                            {{ strtoupper(substr($component->name, 0, 2)) }}
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="admin-component-content">
                        <div class="admin-component-header-row">
                            <div>
                                <h3 class="admin-component-title">{{ $component->title }}</h3>
                                <p class="admin-component-name">/{{ $component->name }}</p>
                            </div>
                            <div class="admin-component-meta">
                                <span class="admin-component-date">{{ $component->updated_at?->format('d.m.Y') }}</span>
                            </div>
                        </div>

                        @if ($component->description)
                            <p class="admin-component-description">{{ $component->description }}</p>
                        @endif

                        @if (!empty($component->tags))
                            <div class="admin-component-tags">
                                @foreach ($component->tags as $tag)
                                    <span class="admin-component-tag">#{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="admin-component-footer">
                            <div class="admin-component-actions">
                                <button type="button" class="admin-component-action" data-preview-component-id="{{ $component->id }}" title="Vorschau">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    Preview
                                </button>

                                <a href="{{ route('admin.components.edit', $component) }}" class="admin-component-action admin-component-action-edit" title="Bearbeiten">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    Bearbeiten
                                </a>

                                <form method="POST" action="{{ route('admin.components.destroy', $component) }}" class="admin-component-form-delete" onsubmit="return confirm('Komponente wirklich löschen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-component-action admin-component-action-delete" title="Löschen">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-linecap="round" stroke-linejoin="round" />
                                            <line x1="10" y1="11" x2="10" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                                            <line x1="14" y1="11" x2="14" y2="17" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Löschen
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="admin-components-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <path d="M9 11h6M9 15h6" stroke-linecap="round" />
                </svg>
                <h3>Noch keine Komponenten</h3>
                <p>Erstelle deine erste wiederverwendbare Komponente</p>
                <a href="{{ route('admin.components.create') }}" class="admin-components-btn-primary">Komponente erstellen</a>
            </div>
        @endforelse
    </div>

    @if ($components->hasPages())
        <div class="admin-components-pagination">
            {{ $components->links() }}
        </div>
    @endif
</div>

<div class="admin-component-preview-modal" id="component-preview-modal" hidden>
    <div class="admin-component-preview-backdrop" data-preview-close></div>
    <section class="admin-component-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="component-preview-title">
        <header class="admin-component-preview-head">
            <div>
                <h2 class="admin-component-preview-title" id="component-preview-title">Komponenten-Preview</h2>
                <p class="admin-component-preview-subtitle" id="component-preview-subtitle"></p>
            </div>
            <button type="button" class="admin-component-preview-close" data-preview-close aria-label="Preview schliessen">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
                </svg>
            </button>
        </header>
        <div class="admin-component-preview-toolbar">
            <div class="admin-component-preview-tabs" role="tablist" aria-label="Preview Modus">
                <button type="button" class="admin-component-preview-tab is-active" data-preview-mode="render" aria-selected="true">Render</button>
                <button type="button" class="admin-component-preview-tab" data-preview-mode="split" aria-selected="false">Split</button>
                <button type="button" class="admin-component-preview-tab" data-preview-mode="html" aria-selected="false">HTML</button>
                <button type="button" class="admin-component-preview-tab" data-preview-mode="css" aria-selected="false">CSS</button>
                <button type="button" class="admin-component-preview-tab" data-preview-mode="js" aria-selected="false">JS</button>
            </div>

            <div class="admin-component-preview-actions">
                <button type="button" class="admin-component-preview-nav" id="component-preview-prev" aria-label="Vorherige Komponente">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <button type="button" class="admin-component-preview-nav" id="component-preview-next" aria-label="Naechste Komponente">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <button type="button" class="admin-component-preview-copy" id="component-preview-copy">Copy</button>
                <div class="admin-component-preview-device" role="tablist" aria-label="Device Ansicht">
                    <button type="button" class="admin-component-preview-device-btn is-active" data-preview-device="desktop" aria-selected="true">Desktop</button>
                    <button type="button" class="admin-component-preview-device-btn" data-preview-device="mobile" aria-selected="false">Mobile</button>
                </div>
            </div>
        </div>
        <div class="admin-component-preview-body">
            <div class="admin-component-preview-render-wrap" id="component-preview-render-wrap" data-preview-device="desktop">
                <iframe id="component-preview-frame" title="Komponenten Vorschau" class="admin-component-preview-frame"></iframe>
            </div>
            <pre id="component-preview-code" class="admin-component-preview-code" hidden></pre>
        </div>
    </section>
</div>

<style>
.admin-components-shell {
    min-height: 100%;
    padding: 1.5rem 1rem;
}

.admin-components-header {
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--admin-line);
}

.admin-components-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    margin-bottom: 1.1rem;
}

.admin-components-header-identity {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.admin-components-header-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 0.65rem;
    background: linear-gradient(145deg, rgba(95,134,255,0.14) 0%, rgba(95,134,255,0.06) 100%);
    border: 1px solid rgba(95,134,255,0.2);
    color: var(--admin-accent, #5f86ff);
    flex-shrink: 0;
}

.admin-components-header-icon svg {
    width: 1.1rem;
    height: 1.1rem;
}

.admin-components-header-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

/* ── Transfer Icon-Bar ────────────────────────── */
.admin-tc-zip-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
    z-index: 100;
}

.admin-tc-popover {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 50%;
    transform: translateX(50%);
    z-index: 200;
    min-width: 13rem;
    background: #fff;
    border: 1px solid var(--admin-line);
    border-radius: 0.75rem;
    box-shadow: 0 8px 28px rgba(15, 23, 42, 0.13), 0 2px 6px rgba(15, 23, 42, 0.07);
    padding: 0.3rem;
    animation: tcPopIn 140ms cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes tcPopIn {
    from { opacity: 0; transform: scale(0.92) translateY(-4px); }
    to   { opacity: 1; transform: scale(1)   translateY(0); }
}

.admin-tc-popover-opt {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    width: 100%;
    padding: 0.55rem 0.75rem;
    border: none;
    background: transparent;
    border-radius: 0.5rem;
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--admin-ink);
    cursor: pointer;
    text-align: left;
    transition: background 120ms ease, color 120ms ease;
}

.admin-tc-popover-opt:hover {
    background: rgba(95, 134, 255, 0.08);
    color: var(--admin-accent, #5f86ff);
}

.admin-tc-popover-opt svg {
    width: 0.95rem;
    height: 0.95rem;
    flex-shrink: 0;
    opacity: 0.7;
}

.admin-tc-popover-divider {
    height: 1px;
    background: var(--admin-line);
    margin: 0.25rem 0.3rem;
}

/* ── Selection Mode ─────────────────────────────── */
.admin-component-select-control {
    display: none;
}

.is-select-mode .admin-component-avatar-wrap {
    position: relative;
}

.is-select-mode .admin-component-select-control {
    display: flex;
    position: absolute;
    top: 0.45rem;
    left: 0.45rem;
    z-index: 2;
    cursor: pointer;
}

.admin-component-select-control input {
    width: 1.05rem;
    height: 1.05rem;
    accent-color: var(--admin-accent, #5f86ff);
    cursor: pointer;
}

.is-select-mode .admin-component-card {
    cursor: pointer;
}

.is-select-mode .admin-component-card.is-checked {
    border-color: var(--admin-accent, #5f86ff);
    background: linear-gradient(135deg, rgba(95,134,255,0.07), rgba(95,134,255,0.02));
    box-shadow: 0 0 0 2px rgba(95,134,255,0.18);
}

/* ── Selection Action Bar ─────────────────────── */
.admin-select-bar {
    position: fixed;
    bottom: 1.5rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 300;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.5rem 0.5rem 0.5rem 1rem;
    background: var(--admin-ink, #0f172a);
    color: #fff;
    border-radius: 999px;
    box-shadow: 0 8px 32px rgba(15, 23, 42, 0.3);
    animation: selectBarIn 220ms cubic-bezier(0.34, 1.4, 0.64, 1);
    white-space: nowrap;
}

@keyframes selectBarIn {
    from { opacity: 0; transform: translateX(-50%) translateY(14px) scale(0.94); }
    to   { opacity: 1; transform: translateX(-50%) translateY(0)     scale(1); }
}

.admin-select-bar-info {
    display: flex;
    align-items: center;
    gap: 0.42rem;
    font-size: 0.82rem;
    font-weight: 500;
    opacity: 0.8;
}

.admin-select-bar-info svg {
    width: 0.85rem;
    height: 0.85rem;
    opacity: 0.55;
}

.admin-select-bar-actions {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.admin-select-bar-cancel {
    padding: 0.38rem 0.8rem;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 999px;
    background: transparent;
    color: rgba(255,255,255,0.65);
    font-size: 0.78rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 120ms ease, color 120ms ease;
}

.admin-select-bar-cancel:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.admin-select-bar-download {
    display: inline-flex;
    align-items: center;
    gap: 0.38rem;
    padding: 0.38rem 0.85rem;
    border: none;
    border-radius: 999px;
    background: var(--admin-accent, #5f86ff);
    color: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 120ms ease, box-shadow 120ms ease, opacity 120ms ease;
}

.admin-select-bar-download:hover:not([disabled]) {
    background: #4f76ef;
    box-shadow: 0 4px 14px rgba(95,134,255,0.4);
}

.admin-select-bar-download[disabled] {
    opacity: 0.4;
    cursor: not-allowed;
}

.admin-select-bar-download svg {
    width: 0.8rem;
    height: 0.8rem;
}

.admin-tc {
    display: inline-flex;
    align-items: center;
    gap: 0.12rem;
    padding: 0.22rem;
    border: 1px solid var(--admin-line);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 2px 8px rgba(22, 42, 88, 0.07);
    overflow: visible;
}

.admin-tc-form {
    display: contents;
}

.admin-tc-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.9rem;
    height: 1.9rem;
    border-radius: 999px;
    border: none;
    background: transparent;
    color: var(--admin-muted);
    cursor: pointer;
    transition: background 130ms ease, color 130ms ease, transform 100ms ease;
    text-decoration: none;
    flex-shrink: 0;
}

.admin-tc-btn:hover {
    background: rgba(95, 134, 255, 0.1);
    color: var(--admin-accent, #5f86ff);
    transform: scale(1.08);
}

.admin-tc-btn:active {
    transform: scale(0.96);
}

.admin-tc-btn svg {
    width: 0.88rem;
    height: 0.88rem;
    flex-shrink: 0;
}

.admin-tc-btn .admin-tc-label {
    position: absolute;
    bottom: -1.3rem;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    color: var(--admin-muted);
    pointer-events: none;
    opacity: 0;
    white-space: nowrap;
}

.admin-tc-btn:hover .admin-tc-label {
    opacity: 1;
}

.admin-tc-badge {
    position: absolute;
    top: 0.05rem;
    right: 0.05rem;
    min-width: 1rem;
    height: 1rem;
    padding: 0 0.22rem;
    border-radius: 999px;
    background: var(--admin-accent, #5f86ff);
    color: #fff;
    font-size: 0.58rem;
    font-weight: 700;
    line-height: 1rem;
    text-align: center;
    pointer-events: none;
}

.admin-tc-btn-confirm {
    background: rgba(95, 134, 255, 0.1);
    color: var(--admin-accent, #5f86ff);
}

.admin-tc-btn-confirm:hover {
    background: var(--admin-accent, #5f86ff);
    color: #fff;
}

.admin-tc-sep {
    width: 1px;
    height: 1.1rem;
    background: var(--admin-line);
    flex-shrink: 0;
    margin: 0 0.1rem;
}

.admin-tc-file {
    display: none;
}

#components-export-selected[disabled],
#components-import-submit[disabled] {
    opacity: 0.38;
    cursor: not-allowed;
    transform: none !important;
}

.admin-components-title {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 700;
    letter-spacing: -0.025em;
    color: var(--admin-ink);
    line-height: 1.2;
}

.admin-components-subtitle {
    margin: 0.18rem 0 0;
    font-size: 0.8rem;
    color: var(--admin-muted);
    line-height: 1.4;
}

.admin-components-subtitle code {
    font-size: 0.75rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    background: rgba(95,134,255,0.1);
    border: 1px solid rgba(95,134,255,0.2);
    color: var(--admin-accent, #5f86ff);
    border-radius: 0.3rem;
    padding: 0.05em 0.32em;
}

.admin-components-btn-new {
    display: inline-flex;
    align-items: center;
    gap: 0.42rem;
    padding: 0.5rem 0.9rem;
    background: var(--admin-accent, #5f86ff);
    color: white;
    border: none;
    border-radius: 999px;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 150ms ease, box-shadow 150ms ease, transform 100ms ease;
    white-space: nowrap;
    text-decoration: none;
    letter-spacing: 0.01em;
}

.admin-components-btn-new:hover {
    background: #4f76ef;
    box-shadow: 0 4px 14px rgba(95, 134, 255, 0.35);
    transform: translateY(-1px);
}

.admin-components-btn-new:active {
    transform: translateY(0);
    box-shadow: none;
}

.admin-components-icon {
    width: 0.9rem;
    height: 0.9rem;
}

.admin-components-search {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.52rem 0.8rem;
    border: 1px solid var(--admin-line);
    border-radius: 999px;
    background: var(--admin-surface);
    transition: border-color 150ms ease, box-shadow 150ms ease;
}

.admin-components-search:focus-within {
    border-color: rgba(95, 134, 255, 0.5);
    box-shadow: 0 0 0 3px rgba(95, 134, 255, 0.08);
}

.admin-components-search-icon {
    width: 1rem;
    height: 1rem;
    color: var(--admin-muted);
    flex-shrink: 0;
}

.admin-components-search-input {
    flex: 1;
    min-width: 0;
    border: none;
    background: transparent;
    font: inherit;
    font-size: 0.875rem;
    color: var(--admin-ink);
    outline: none;
}

.admin-components-search-input::placeholder {
    color: var(--admin-muted);
}

.admin-components-search-clear {
    display: none;
    width: 1.5rem;
    height: 1.5rem;
    padding: 0.25rem;
    background: transparent;
    border: none;
    border-radius: 0.375rem;
    color: var(--admin-muted);
    cursor: pointer;
    transition: all 150ms ease;
}

.admin-components-search-clear:hover {
    background: rgba(95, 134, 255, 0.1);
    color: var(--admin-accent);
}

.admin-components-tag-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.75rem;
}

.admin-components-tag-filter {
    border: 1px solid var(--admin-line);
    background: rgba(255, 255, 255, 0.02);
    color: var(--admin-muted);
    padding: 0.34rem 0.7rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 160ms ease;
}

.admin-components-tag-filter:hover {
    border-color: rgba(95, 134, 255, 0.45);
    color: var(--admin-ink);
}

.admin-components-tag-filter.is-active {
    border-color: rgba(95, 134, 255, 0.65);
    background: rgba(95, 134, 255, 0.14);
    color: var(--admin-ink);
}

.admin-components-alert {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    margin-bottom: 1.5rem;
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
    border-radius: 0.625rem;
    color: #22c55e;
    font-size: 0.875rem;
}

.admin-components-alert svg {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}

.admin-components-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.admin-component-card {
    border: 1px solid var(--admin-line);
    border-radius: 1rem;
    background: var(--admin-surface);
    transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.admin-component-card:hover {
    border-color: var(--admin-accent, #5f86ff);
    background: linear-gradient(135deg, var(--admin-surface), rgba(95, 134, 255, 0.03));
    box-shadow: 0 4px 16px rgba(95, 134, 255, 0.15);
}

.admin-component-card-inner {
    padding: 1.25rem;
    display: flex;
    gap: 1rem;
}

.admin-component-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, var(--admin-accent, #5f86ff), #3ab7a5);
    color: white;
    font-size: 0.8rem;
    font-weight: 700;
    flex-shrink: 0;
}

.admin-component-content {
    flex: 1;
    min-width: 0;
}

.admin-component-header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.admin-component-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--admin-ink);
    letter-spacing: -0.01em;
}

.admin-component-name {
    margin: 0.25rem 0 0;
    font-size: 0.8125rem;
    color: var(--admin-accent, #5f86ff);
    font-family: 'Monaco', 'Courier New', monospace;
    font-weight: 500;
}

.admin-component-meta {
    text-align: right;
}

.admin-component-date {
    display: block;
    font-size: 0.75rem;
    color: var(--admin-muted);
}

.admin-component-description {
    margin: 0.625rem 0;
    font-size: 0.8125rem;
    color: var(--admin-muted);
    line-height: 1.4;
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.admin-component-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-bottom: 0.8rem;
}

.admin-component-tag {
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(95, 134, 255, 0.32);
    background: rgba(95, 134, 255, 0.08);
    color: var(--admin-ink);
    border-radius: 999px;
    padding: 0.2rem 0.56rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.01em;
}

.admin-component-footer {
    margin-top: 0.875rem;
    padding-top: 0.875rem;
    border-top: 1px solid var(--admin-line);
}

.admin-component-actions {
    display: flex;
    gap: 0.5rem;
}

.admin-component-action {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.875rem;
    background: transparent;
    border: 1px solid var(--admin-line);
    border-radius: 0.5rem;
    color: var(--admin-muted);
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 150ms ease;
    text-decoration: none;
}

.admin-component-action:hover {
    background: rgba(95, 134, 255, 0.08);
    border-color: var(--admin-accent, #5f86ff);
    color: var(--admin-accent);
}

.admin-component-action svg {
    width: 0.875rem;
    height: 0.875rem;
}

.admin-component-action-delete:hover {
    background: rgba(239, 68, 68, 0.1);
    border-color: #ef4444;
    color: #ef4444;
}

.admin-component-form-delete {
    display: contents;
}

.admin-components-empty {
    grid-column: 1 / -1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
    color: var(--admin-muted);
}

.admin-components-empty svg {
    width: 3rem;
    height: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.admin-components-empty h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--admin-ink);
}

.admin-components-empty p {
    margin: 0.5rem 0 1.5rem;
    font-size: 0.875rem;
}

.admin-components-btn-primary {
    display: inline-flex;
    padding: 0.625rem 1.25rem;
    background: var(--admin-accent);
    color: white;
    border: none;
    border-radius: 0.625rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 200ms ease;
    text-decoration: none;
}

.admin-components-btn-primary:hover {
    background: var(--admin-accent-hover, #5f86ff);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(95, 134, 255, 0.3);
}

.admin-components-pagination {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.admin-component-preview-modal {
    position: fixed;
    inset: 0;
    z-index: 1200;
    display: grid;
    place-items: center;
    padding: 1rem;
}

body.admin-component-preview-open {
    overflow: hidden;
}

.admin-component-preview-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(9, 14, 26, 0.62);
    backdrop-filter: blur(6px);
}

.admin-component-preview-dialog {
    position: relative;
    width: min(980px, 92vw);
    height: min(78vh, 780px);
    border-radius: 1.15rem;
    border: 1px solid rgba(148, 163, 184, 0.24);
    background: linear-gradient(180deg, rgba(252, 254, 255, 0.99), rgba(245, 250, 255, 0.98));
    box-shadow: 0 40px 85px rgba(2, 6, 23, 0.35);
    overflow: hidden;
    display: grid;
    grid-template-rows: auto auto 1fr;
}

.admin-component-preview-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    padding: 0.68rem 0.8rem;
    border-bottom: 1px solid rgba(30, 41, 59, 0.12);
    background: rgba(255, 255, 255, 0.9);
}

.admin-component-preview-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.55rem;
    padding: 0.48rem 0.8rem;
    border-bottom: 1px solid rgba(30, 41, 59, 0.1);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(245, 250, 255, 0.94));
}

.admin-component-preview-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.admin-component-preview-tabs,
.admin-component-preview-device {
    display: inline-flex;
    align-items: center;
    gap: 0.18rem;
    border: 1px solid rgba(51, 65, 85, 0.14);
    border-radius: 999px;
    padding: 0.14rem;
    background: rgba(255, 255, 255, 0.72);
}

.admin-component-preview-tab,
.admin-component-preview-device-btn {
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: var(--admin-muted);
    font-size: 0.71rem;
    font-weight: 640;
    padding: 0.28rem 0.54rem;
    cursor: pointer;
    transition: all 150ms ease;
}

.admin-component-preview-nav {
    width: 1.75rem;
    height: 1.75rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(51, 65, 85, 0.16);
    background: rgba(255, 255, 255, 0.8);
    color: var(--admin-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 150ms ease;
}

.admin-component-preview-nav:hover {
    color: var(--admin-ink);
    border-color: rgba(95, 134, 255, 0.4);
    background: rgba(95, 134, 255, 0.12);
}

.admin-component-preview-nav svg {
    width: 0.82rem;
    height: 0.82rem;
}

.admin-component-preview-copy {
    border: 1px solid rgba(51, 65, 85, 0.16);
    background: rgba(255, 255, 255, 0.8);
    color: var(--admin-ink);
    border-radius: 0.5rem;
    height: 1.75rem;
    padding: 0 0.52rem;
    font-size: 0.69rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: all 150ms ease;
}

.admin-component-preview-copy:hover {
    border-color: rgba(95, 134, 255, 0.4);
    background: rgba(95, 134, 255, 0.12);
}

.admin-component-preview-copy.is-copied {
    color: #0f5132;
    border-color: rgba(34, 197, 94, 0.4);
    background: rgba(34, 197, 94, 0.18);
}

.admin-component-preview-tab:hover,
.admin-component-preview-device-btn:hover {
    color: var(--admin-ink);
    background: rgba(95, 134, 255, 0.1);
}

.admin-component-preview-tab.is-active,
.admin-component-preview-device-btn.is-active {
    background: linear-gradient(180deg, rgba(95, 134, 255, 0.2), rgba(95, 134, 255, 0.13));
    color: var(--admin-ink);
    box-shadow: inset 0 0 0 1px rgba(95, 134, 255, 0.2);
}

.admin-component-preview-title {
    margin: 0;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--admin-ink);
}

.admin-component-preview-subtitle {
    margin: 0.2rem 0 0;
    font-size: 0.75rem;
    color: var(--admin-muted);
}

.admin-component-preview-close {
    border: 1px solid var(--admin-line);
    background: rgba(255, 255, 255, 0.86);
    color: var(--admin-muted);
    width: 1.75rem;
    height: 1.75rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    cursor: pointer;
}

.admin-component-preview-close:hover {
    color: var(--admin-ink);
    border-color: rgba(95, 134, 255, 0.4);
    background: rgba(95, 134, 255, 0.1);
}

.admin-component-preview-close svg {
    width: 0.86rem;
    height: 0.86rem;
}

.admin-component-preview-body {
    min-height: 0;
    padding: 0.62rem;
    background: radial-gradient(circle at 0% 0%, rgba(95, 134, 255, 0.09), transparent 42%), #f4f8ff;
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.62rem;
    overflow-x: hidden;
}

.admin-component-preview-dialog[data-preview-layout='split'] .admin-component-preview-body {
    grid-template-columns: minmax(0, 1fr) minmax(0, 0.86fr);
}

.admin-component-preview-render-wrap {
    width: 100%;
    height: 100%;
    border-radius: 0.72rem;
    border: 1px solid rgba(30, 41, 59, 0.12);
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12);
    overflow: hidden;
    transition: width 220ms ease, margin 220ms ease;
    background: #ffffff;
}

.admin-component-preview-render-wrap[data-preview-device='desktop'] {
    width: 100%;
    margin: 0;
}

.admin-component-preview-render-wrap[data-preview-device='mobile'] {
    width: 100%;
    margin: 0;
}

.admin-component-preview-frame {
    width: 100%;
    height: 100%;
    border: 0;
    background: #f5f7fb;
}

.admin-component-preview-code {
    margin: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    border-radius: 0.72rem;
    border: 1px solid rgba(148, 163, 184, 0.25);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
    background: linear-gradient(180deg, #0b1220 0%, #111827 100%);
    color: #e2ecff;
    padding: 0.72rem;
    font-size: 0.74rem;
    line-height: 1.48;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
}

@media (max-width: 640px) {
    .admin-components-shell {
        padding: 1rem 0.75rem;
    }

    .admin-components-header-top {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.85rem;
    }

    .admin-components-header-identity {
        gap: 0.6rem;
    }

    .admin-components-header-actions {
        width: 100%;
        justify-content: space-between;
    }

    .admin-components-btn-new {
        padding: 0.46rem 0.8rem;
    }

    .admin-components-container {
        grid-template-columns: 1fr;
    }

    .admin-component-header-row {
        flex-direction: column;
    }

    .admin-component-meta {
        text-align: left;
    }

    .admin-component-preview-dialog {
        width: 100%;
        height: 88vh;
    }

    .admin-component-preview-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .admin-component-preview-actions {
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .admin-component-preview-tabs,
    .admin-component-preview-device {
        width: 100%;
        justify-content: center;
    }

    .admin-component-preview-dialog[data-preview-layout='split'] .admin-component-preview-body {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let activeTagFilter = 'all';
let lastPreviewTrigger = null;
let activePreviewMode = 'render';
let activePreviewDevice = 'desktop';
let currentPreviewComponent = null;
let currentPreviewIndex = -1;
let copyFeedbackTimer = null;

const componentPreviewPayloadNode = document.getElementById('component-preview-payload');
const componentPreviewFrame = document.getElementById('component-preview-frame');
const componentPreviewModal = document.getElementById('component-preview-modal');
const componentPreviewTitle = document.getElementById('component-preview-title');
const componentPreviewSubtitle = document.getElementById('component-preview-subtitle');
const componentPreviewCode = document.getElementById('component-preview-code');
const componentPreviewRenderWrap = document.getElementById('component-preview-render-wrap');
const componentPreviewDialog = document.querySelector('.admin-component-preview-dialog');
const componentPreviewPrev = document.getElementById('component-preview-prev');
const componentPreviewNext = document.getElementById('component-preview-next');
const componentPreviewCopy = document.getElementById('component-preview-copy');
const componentImportTrigger = document.getElementById('components-import-trigger');
const componentImportInput = document.getElementById('components-import-file');
const componentImportForm = document.getElementById('components-import-form');
const componentImportSubmit = document.getElementById('components-import-submit');
const componentImportMeta = document.getElementById('components-import-meta');
const componentExportZipInputs = document.getElementById('components-export-zip-inputs');

const componentPreviewMap = (() => {
        if (!componentPreviewPayloadNode) {
                return new Map();
        }

        try {
                const payload = JSON.parse(componentPreviewPayloadNode.textContent || '[]');
                if (!Array.isArray(payload)) {
                        return new Map();
                }

                return new Map(payload.map((component) => [String(component.id), component]));
        } catch {
                return new Map();
        }
})();

const componentPreviewList = Array.from(componentPreviewMap.values());

const createPreviewDocument = (component) => {
        const title = component?.title || 'Komponenten-Preview';
        const description = component?.description || '';
        const html = component?.content || '';
        const css = component?.css || '';
        const js = (component?.js || '').replace(/<\/script>/gi, '<\\/script>');

        return `<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>${title}</title>
    <style>
            *, *::before, *::after { box-sizing: border-box; }
        :root { color-scheme: light; }
            html, body { width: 100%; max-width: 100%; overflow-x: hidden; }
        body {
            margin: 0;
            background: linear-gradient(180deg, #f4f8ff 0%, #edf3fb 100%);
            color: #0f172a;
            font-family: system-ui, -apple-system, Segoe UI, sans-serif;
                padding: 0.75rem;
        }
        .preview-shell {
                width: 100%;
                max-width: 100%;
                margin: 0;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 0.95rem;
            padding: 1rem;
            box-shadow: 0 24px 54px rgba(15, 23, 42, 0.12);
                overflow-x: hidden;
        }
        .preview-meta {
            margin-bottom: 0.85rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            padding-bottom: 0.6rem;
        }
        .preview-meta h1 {
            margin: 0;
            font-size: 0.95rem;
        }
        .preview-meta p {
            margin: 0.3rem 0 0;
            font-size: 0.8rem;
            color: #64748b;
        }
        .preview-content { min-height: 80px; }
        .preview-content img,
        .preview-content video,
        .preview-content iframe,
        .preview-content table,
        .preview-content pre,
        .preview-content code,
        .preview-content svg {
            max-width: 100%;
        }
        .preview-content a {
            pointer-events: none;
            cursor: default;
        }
        ${css}
    </style>
</head>
<body>
    <section class="preview-shell">
        <header class="preview-meta">
            <h1>${title}</h1>
            ${description ? `<p>${description}</p>` : ''}
        </header>
        <div class="preview-content">${html}</div>
    </section>
    <script>
        document.addEventListener('click', (event) => {
            const anchor = event.target instanceof Element ? event.target.closest('a') : null;
            if (!anchor) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
        }, true);

        try {
            ${js}
        } catch (error) {
            console.error('Component preview script error:', error);
        }
    <\/script>
</body>
</html>`;
};

const syncPreviewUiState = () => {
    const previewMode = activePreviewMode;
    const previewDevice = activePreviewDevice;

    document.querySelectorAll('.admin-component-preview-tab').forEach((button) => {
        const active = button.dataset.previewMode === previewMode;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    document.querySelectorAll('.admin-component-preview-device-btn').forEach((button) => {
        const active = button.dataset.previewDevice === previewDevice;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    if (componentPreviewRenderWrap instanceof HTMLElement) {
        componentPreviewRenderWrap.dataset.previewDevice = previewDevice;
        componentPreviewRenderWrap.hidden = !(previewMode === 'render' || previewMode === 'split');
    }

    if (componentPreviewCode instanceof HTMLElement) {
        componentPreviewCode.hidden = previewMode === 'render';
    }

    if (componentPreviewDialog instanceof HTMLElement) {
        componentPreviewDialog.dataset.previewLayout = previewMode === 'split' ? 'split' : 'single';
    }
};

const updatePreviewContent = () => {
    if (!currentPreviewComponent) {
        return;
    }

    if (componentPreviewFrame instanceof HTMLIFrameElement) {
        componentPreviewFrame.srcdoc = createPreviewDocument(currentPreviewComponent);
    }

    if (!(componentPreviewCode instanceof HTMLElement)) {
        return;
    }

    if (activePreviewMode === 'html') {
        componentPreviewCode.textContent = currentPreviewComponent.content || '<!-- Kein HTML vorhanden -->';
        return;
    }

    if (activePreviewMode === 'css') {
        componentPreviewCode.textContent = (currentPreviewComponent.css || '').trim() || '/* Kein CSS vorhanden */';
        return;
    }

    if (activePreviewMode === 'js') {
        componentPreviewCode.textContent = (currentPreviewComponent.js || '').trim() || '// Kein JavaScript vorhanden';
        return;
    }

    if (activePreviewMode === 'split') {
        const htmlText = currentPreviewComponent.content || '<!-- Kein HTML vorhanden -->';
        const cssText = (currentPreviewComponent.css || '').trim() || '/* Kein CSS vorhanden */';
        const jsText = (currentPreviewComponent.js || '').trim() || '// Kein JavaScript vorhanden';
        componentPreviewCode.textContent = [
            '/* HTML */',
            htmlText,
            '',
            '/* CSS */',
            cssText,
            '',
            '/* JS */',
            jsText,
        ].join('\n');
        return;
    }

    componentPreviewCode.textContent = '';
};

const closeComponentPreview = () => {
        if (!(componentPreviewModal instanceof HTMLElement)) {
                return;
        }

        componentPreviewModal.hidden = true;
        document.body.classList.remove('admin-component-preview-open');

        if (componentPreviewFrame instanceof HTMLIFrameElement) {
                componentPreviewFrame.srcdoc = '';
        }

        if (componentPreviewCode instanceof HTMLElement) {
            componentPreviewCode.textContent = '';
        }

        currentPreviewComponent = null;
        currentPreviewIndex = -1;
        activePreviewMode = 'render';
        activePreviewDevice = 'desktop';
        syncPreviewUiState();

        if (lastPreviewTrigger instanceof HTMLElement) {
                lastPreviewTrigger.focus();
        }
};

const openComponentPreview = (componentId, trigger) => {
    const component = componentPreviewMap.get(String(componentId));
    const nextIndex = componentPreviewList.findIndex((item) => String(item.id) === String(componentId));
        if (!component || !(componentPreviewModal instanceof HTMLElement) || !(componentPreviewFrame instanceof HTMLIFrameElement)) {
                return;
        }

        lastPreviewTrigger = trigger instanceof HTMLElement ? trigger : null;
        currentPreviewComponent = component;
    currentPreviewIndex = nextIndex;
        activePreviewMode = 'render';
        activePreviewDevice = 'desktop';
        componentPreviewModal.hidden = false;
        document.body.classList.add('admin-component-preview-open');

        if (componentPreviewTitle instanceof HTMLElement) {
                componentPreviewTitle.textContent = component.title || 'Komponenten-Preview';
        }

        if (componentPreviewSubtitle instanceof HTMLElement) {
                componentPreviewSubtitle.textContent = `/${component.name || 'component'}`;
        }

        syncPreviewUiState();
        updatePreviewContent();
};

    const openComponentPreviewByIndex = (index) => {
        if (!componentPreviewList.length) {
            return;
        }

        const normalizedIndex = ((index % componentPreviewList.length) + componentPreviewList.length) % componentPreviewList.length;
        const component = componentPreviewList[normalizedIndex];
        if (!component) {
            return;
        }

        currentPreviewComponent = component;
        currentPreviewIndex = normalizedIndex;

        if (componentPreviewTitle instanceof HTMLElement) {
            componentPreviewTitle.textContent = component.title || 'Komponenten-Preview';
        }

        if (componentPreviewSubtitle instanceof HTMLElement) {
            componentPreviewSubtitle.textContent = `/${component.name || 'component'}`;
        }

        syncPreviewUiState();
        updatePreviewContent();
    };

    const copyPreviewCode = async () => {
        if (!currentPreviewComponent || !(componentPreviewCopy instanceof HTMLButtonElement)) {
            return;
        }

        let payload = currentPreviewComponent.content || '';
        if (activePreviewMode === 'css') {
            payload = currentPreviewComponent.css || '';
        } else if (activePreviewMode === 'js') {
            payload = currentPreviewComponent.js || '';
        } else if (activePreviewMode === 'split') {
            payload = [
                '/* HTML */',
                currentPreviewComponent.content || '',
                '',
                '/* CSS */',
                currentPreviewComponent.css || '',
                '',
                '/* JS */',
                currentPreviewComponent.js || '',
            ].join('\n');
        }

        try {
            await navigator.clipboard.writeText(payload);
            componentPreviewCopy.textContent = 'Copied';
            componentPreviewCopy.classList.add('is-copied');

            if (copyFeedbackTimer !== null) {
                window.clearTimeout(copyFeedbackTimer);
            }

            copyFeedbackTimer = window.setTimeout(() => {
                componentPreviewCopy.textContent = 'Copy';
                componentPreviewCopy.classList.remove('is-copied');
                copyFeedbackTimer = null;
            }, 1200);
        } catch {
            // Silent failure for unsupported clipboard APIs.
        }
    };

function filterComponents() {
    const searchInput = document.getElementById('component-search');
    const query = searchInput.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.admin-component-card');
    const clearBtn = document.querySelector('.admin-components-search-clear');

    clearBtn.style.display = query ? 'block' : 'none';

    cards.forEach(card => {
        const name = card.dataset.componentName.toLowerCase();
        const title = card.dataset.componentTitle.toLowerCase();
        const tags = (card.dataset.componentTags || '').toLowerCase();
        const matchesText = name.includes(query) || title.includes(query) || tags.includes(query);
        const matchesTag = activeTagFilter === 'all' || tags.split(/\s+/).includes(activeTagFilter);
        card.style.display = matchesText && matchesTag ? '' : 'none';
    });
}

document.getElementById('component-search')?.addEventListener('input', filterComponents);

document.querySelectorAll('.admin-components-tag-filter').forEach((button) => {
    button.addEventListener('click', () => {
        activeTagFilter = (button.dataset.filterTag || 'all').toLowerCase();

        document.querySelectorAll('.admin-components-tag-filter').forEach((candidate) => {
            candidate.classList.toggle('is-active', candidate === button);
        });

        filterComponents();
    });
});

document.querySelectorAll('[data-preview-component-id]').forEach((button) => {
    button.addEventListener('click', () => {
        openComponentPreview(button.dataset.previewComponentId, button);
    });
});

document.querySelectorAll('[data-preview-close]').forEach((button) => {
    button.addEventListener('click', closeComponentPreview);
});

document.querySelectorAll('.admin-component-preview-tab').forEach((button) => {
    button.addEventListener('click', () => {
        activePreviewMode = button.dataset.previewMode || 'render';
        syncPreviewUiState();
        updatePreviewContent();
    });
});

document.querySelectorAll('.admin-component-preview-device-btn').forEach((button) => {
    button.addEventListener('click', () => {
        activePreviewDevice = button.dataset.previewDevice || 'desktop';
        syncPreviewUiState();
    });
});

componentPreviewPrev?.addEventListener('click', () => {
    openComponentPreviewByIndex(currentPreviewIndex - 1);
});

componentPreviewNext?.addEventListener('click', () => {
    openComponentPreviewByIndex(currentPreviewIndex + 1);
});

componentPreviewCopy?.addEventListener('click', () => {
    void copyPreviewCode();
});

componentImportTrigger?.addEventListener('click', () => {
    componentImportInput?.click();
});

componentImportInput?.addEventListener('change', () => {
    if (!(componentImportInput instanceof HTMLInputElement) || !(componentImportSubmit instanceof HTMLButtonElement) || !(componentImportMeta instanceof HTMLElement)) {
        return;
    }

    const count = componentImportInput.files?.length ?? 0;
    componentImportSubmit.disabled = count === 0;
    componentImportMeta.hidden = count === 0;
    componentImportMeta.textContent = count > 0 ? String(count) : '';
});

// ── ZIP Popover + Selection Mode ──────────────────────────

const zipTrigger     = document.getElementById('components-zip-trigger');
const zipPopover     = document.getElementById('components-zip-popover');
const zipAll         = document.getElementById('components-zip-all');
const zipPick        = document.getElementById('components-zip-pick');
const selectBar      = document.getElementById('components-select-bar');
const selectCount    = document.getElementById('components-select-count');
const selectCancel   = document.getElementById('components-select-cancel');
const selectDownload = document.getElementById('components-select-download');
const container      = document.querySelector('.admin-components-container');

let selectMode = false;

const openZipPopover = () => {
    if (!zipPopover || !zipTrigger) return;
    zipPopover.hidden = false;
    zipTrigger.setAttribute('aria-expanded', 'true');
};

const closeZipPopover = () => {
    if (!zipPopover || !zipTrigger) return;
    zipPopover.hidden = true;
    zipTrigger.setAttribute('aria-expanded', 'false');
};

const enterSelectMode = () => {
    selectMode = true;
    container?.classList.add('is-select-mode');
    if (selectBar) selectBar.hidden = false;
    syncSelectBar();
};

const exitSelectMode = () => {
    selectMode = false;
    container?.classList.remove('is-select-mode');
    if (selectBar) selectBar.hidden = true;
    document.querySelectorAll('.admin-component-select').forEach((cb) => {
        if (cb instanceof HTMLInputElement) cb.checked = false;
        cb.closest('.admin-component-card')?.classList.remove('is-checked');
    });
    if (componentExportZipInputs instanceof HTMLElement) componentExportZipInputs.innerHTML = '';
};

const syncSelectBar = () => {
    const selected = Array.from(document.querySelectorAll('.admin-component-select:checked'))
        .map((cb) => cb instanceof HTMLInputElement ? cb.value : '')
        .filter(Boolean);

    if (componentExportZipInputs instanceof HTMLElement) {
        componentExportZipInputs.innerHTML = selected
            .map((id) => `<input type="hidden" name="component_ids[]" value="${id}">`)
            .join('');
    }

    if (selectCount) selectCount.textContent = `${selected.length} ausgewählt`;
    if (selectDownload instanceof HTMLButtonElement) selectDownload.disabled = selected.length === 0;
};

zipTrigger?.addEventListener('click', (e) => {
    e.stopPropagation();
    zipPopover?.hidden === false ? closeZipPopover() : openZipPopover();
});

zipAll?.addEventListener('click', (e) => {
    e.stopPropagation();
    closeZipPopover();
    const allIds = Array.from(document.querySelectorAll('.admin-component-card')).map((card) => {
        const cb = card.querySelector('.admin-component-select');
        return cb instanceof HTMLInputElement ? cb.value : '';
    }).filter(Boolean);
    if (componentExportZipInputs instanceof HTMLElement) {
        componentExportZipInputs.innerHTML = allIds
            .map((id) => `<input type="hidden" name="component_ids[]" value="${id}">`)
            .join('');
    }
    document.getElementById('components-export-zip-form')?.submit();
});

zipPick?.addEventListener('click', (e) => {
    e.stopPropagation();
    closeZipPopover();
    enterSelectMode();
});

document.addEventListener('click', (e) => {
    if (zipPopover && !zipPopover.hidden) {
        const target = /** @type {Element} */(e.target);
        if (!zipPopover.contains(target) && !zipTrigger?.contains(target)) {
            closeZipPopover();
        }
    }
});

selectCancel?.addEventListener('click', exitSelectMode);

selectDownload?.addEventListener('click', () => {
    document.getElementById('components-export-zip-form')?.submit();
});

document.querySelectorAll('.admin-component-select').forEach((checkbox) => {
    checkbox.addEventListener('change', () => {
        const card = checkbox.closest('.admin-component-card');
        card?.classList.toggle('is-checked', checkbox instanceof HTMLInputElement && checkbox.checked);
        syncSelectBar();
    });
});

// Click anywhere on card body toggles selection in select mode
document.querySelectorAll('.admin-component-card').forEach((card) => {
    card.addEventListener('click', (e) => {
        if (!selectMode) return;
        if (/** @type {Element} */(e.target).closest('button, a, label')) return;
        const cb = card.querySelector('.admin-component-select');
        if (cb instanceof HTMLInputElement) {
            cb.checked = !cb.checked;
            card.classList.toggle('is-checked', cb.checked);
            syncSelectBar();
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        if (zipPopover && !zipPopover.hidden) { closeZipPopover(); return; }
        if (selectMode) { exitSelectMode(); return; }
        if (componentPreviewModal instanceof HTMLElement && !componentPreviewModal.hidden) {
            closeComponentPreview();
            return;
        }
    }

    if (componentPreviewModal instanceof HTMLElement && !componentPreviewModal.hidden && event.key === 'ArrowLeft') {
        openComponentPreviewByIndex(currentPreviewIndex - 1);
        return;
    }

    if (componentPreviewModal instanceof HTMLElement && !componentPreviewModal.hidden && event.key === 'ArrowRight') {
        openComponentPreviewByIndex(currentPreviewIndex + 1);
    }
});
</script>
@endsection