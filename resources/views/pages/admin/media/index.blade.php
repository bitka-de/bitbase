@extends('layouts.admin')

@section('meta_title', 'Medien verwalten | ' . config('app.name'))
@section('meta_description', 'Medien hochladen, als WebP optimieren und Metadaten pflegen.')
@section('canonical_url', route('admin.media.index'))
@section('admin_title', 'Medien')
@section('admin_subtitle', 'Upload mit WebP-Optimierung, Alt-Text und Quelle')

@section('content')
<section class="admin-media-hero" aria-label="Medien Intro">
    <div class="admin-media-hero-content">
        <p class="admin-media-kicker">Media Pipeline</p>
        <h2 class="admin-media-headline">Smart Media Hub fuer schnelle Pflege und saubere Assets</h2>
        <p class="admin-media-lead">Upload, Suche, Auswahl und Loeschen laufen jetzt direkt in einer Arbeitsflaeche. Alle Bilder werden weiterhin automatisch in WebP gewandelt, komprimiert und in Original plus <strong>_xs</strong> abgelegt.</p>
    </div>
    <div class="admin-media-hero-stats" aria-hidden="true">
        <div>
            <span>Gesamt</span>
            <strong>{{ $stats['total'] }}</strong>
        </div>
        <div>
            <span>Diesen Monat</span>
            <strong>{{ $stats['this_month'] }}</strong>
        </div>
        <div>
            <span>Format</span>
            <strong>WEBP</strong>
        </div>
    </div>
</section>

@if (session('success'))
    <div class="admin-media-alert admin-media-alert--success" role="status">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error') || $errors->any())
    <div class="admin-media-alert admin-media-alert--error" role="alert">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 9v4m0 4h.01M10.29 3.86l-8.2 14.2A2 2 0 0 0 3.8 21h16.4a2 2 0 0 0 1.71-2.94l-8.2-14.2a2 2 0 0 0-3.42 0Z" />
        </svg>
        <span>{{ session('error') ?: $errors->first() }}</span>
    </div>
@endif

<section class="admin-media-page-actions" aria-label="Medien Aktionen">
    <div class="admin-media-page-actions__copy">
        <strong>Upload</strong>
        <span>Oeffnet den Mehrfach-Upload im Popover mit Drag-and-Drop und Live-Fortschritt.</span>
    </div>
    <button type="button" class="btn admin-media-upload-trigger admin-media-upload-trigger--top" data-media-upload-open>Upload</button>
</section>

<section class="admin-media-layout" aria-label="Medien Upload und Bibliothek">
    <div class="admin-media-panel admin-media-panel-library">
        <div class="admin-media-library-shell">
            <aside class="admin-media-tags-sidebar" data-media-tags-sidebar>
                <div class="admin-media-tags-sidebar-head">
                    <div>
                        <strong>Tags</strong>
                        <span>Filtern oder Medien per Drag-and-Drop zuordnen.</span>
                    </div>
                    <button type="button" class="admin-media-tags-toggle" data-media-tags-toggle aria-expanded="true">Einklappen</button>
                </div>

                <div class="admin-media-tags-sidebar-body" data-media-tags-body>
                    <div class="admin-media-tags-create">
                        <label class="admin-media-tags-create-field">
                            <span>Neuer Tag</span>
                            <input type="text" class="admin-media-tags-create-input" placeholder="z.B. hero" data-media-tag-create-input>
                        </label>
                        <button type="button" class="admin-media-toolbar-btn" data-media-tag-create>Add</button>
                    </div>

                    <nav class="admin-media-tags-nav" aria-label="Media Tags Navigation">
                        <button type="button" class="admin-media-tag-filter is-active" data-media-tag-filter="__all__">
                            <span>Alle Medien</span>
                            <small data-media-all-count>{{ $mediaItems->total() }}</small>
                        </button>

                        <div class="admin-media-tag-list" data-media-tag-list>
                            @foreach ($availableTags as $tag)
                                <button
                                    type="button"
                                    class="admin-media-tag-filter"
                                    data-media-tag-filter="{{ $tag['name'] }}"
                                    data-media-tag-dropzone="{{ $tag['name'] }}"
                                >
                                    <span>{{ $tag['name'] }}</span>
                                    <small data-media-tag-count>{{ $tag['count'] }}</small>
                                </button>
                            @endforeach
                        </div>
                    </nav>
                </div>
            </aside>

            <div class="admin-media-library-content">
                <div class="admin-media-library-top">
                    <div class="admin-media-library-head">
                        <div>
                            <h3>Medien Bibliothek</h3>
                            <p>Suchen, markieren, taggen und verwalten in einer ruhigen Uebersicht.</p>
                        </div>
                        <span class="admin-media-library-count" data-media-library-count>{{ $mediaItems->total() }} Dateien</span>
                    </div>

                    <div class="admin-media-toolbar" data-media-toolbar>
                        <label class="admin-media-search-wrap" for="admin-media-search">
                            <span>Suche</span>
                            <input id="admin-media-search" type="search" class="admin-media-search" placeholder="Name, Alt-Text, Quelle, Pfad oder Tags filtern" data-media-search>
                        </label>

                        <div class="admin-media-toolbar-actions">
                            <span class="admin-media-selection-status" data-media-selection-status>0 ausgewaehlt</span>
                            <button type="button" class="admin-media-toolbar-btn" data-media-select-visible>Alle sichtbaren waehlen</button>
                            <button type="button" class="admin-media-toolbar-btn" data-media-clear-selection>Auswahl loeschen</button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.media.bulk-destroy') }}" class="admin-media-bulk-form" data-media-bulk-form>
                        @csrf
                        @method('DELETE')
                        <div class="admin-media-bulk-bar" data-media-bulk-bar hidden>
                            <div>
                                <strong data-media-bulk-count>0 Bilder markiert</strong>
                                <span>Loescht markierte Dateien aus Mediathek und Storage.</span>
                            </div>
                            <div class="admin-media-bulk-actions">
                                <button type="submit" class="btn btn-danger">Auswahl loeschen</button>
                            </div>
                        </div>
                        <div data-media-bulk-hidden-inputs></div>
                    </form>
                </div>

                <div class="admin-media-grid" data-media-grid>
            @forelse ($mediaItems as $media)
                @php
                    $variants = is_array($media->variants) ? $media->variants : [];
                    $xsPath = data_get($variants, 'xs.path');
                    $xsUrl = $xsPath ? route('media.show', ['filename' => basename($xsPath)]) : null;
                    $tags = is_array($media->tags) ? $media->tags : [];
                @endphp
                <article
                    class="admin-media-card"
                    draggable="true"
                    data-media-id="{{ $media->id }}"
                    data-media-card
                    data-media-tags="{{ implode(',', $tags) }}"
                    data-media-filter="{{ strtolower(trim(($media->name ?: '').' '.($media->alt_text ?: '').' '.($media->source ?: '').' '.($media->path ?: '').' '.implode(' ', $tags))) }}"
                >
                    <label class="admin-media-select">
                        <input type="checkbox" value="{{ $media->id }}" data-media-checkbox>
                        <span>Auswaehlen</span>
                    </label>

                    <div class="admin-media-preview-wrap">
                        <img src="{{ $media->url }}" alt="{{ $media->alt_text ?: 'Media Preview' }}" class="admin-media-preview">
                        <span class="admin-media-dimension-badge">{{ $media->width }} x {{ $media->height }}</span>
                    </div>

                    <div class="admin-media-card-head">
                        <div>
                            <strong>{{ $media->name ?: basename($media->path) }}</strong>
                            <span>{{ $media->alt_text ?: 'Ohne Alt-Text' }}</span>
                        </div>
                        <span class="admin-media-format-pill">{{ strtoupper((string) str_replace('image/', '', (string) $media->mime_type)) }}</span>
                    </div>

                    <div class="admin-media-meta">
                        <small class="admin-media-meta-path">{{ $media->path }}</small>
                        <small>{{ $media->source ?: 'Keine Quelle gepflegt' }}</small>
                        @if ($xsPath)
                            <small>_xs: {{ $xsPath }}</small>
                        @endif
                    </div>

                    <div class="admin-media-tag-badges" data-media-card-tags>
                        @foreach ($tags as $tag)
                            <span class="admin-media-tag-badge">{{ $tag }}</span>
                        @endforeach
                    </div>

                    <div class="admin-media-links">
                        <a href="{{ $media->url }}" target="_blank" rel="noopener">Original</a>
                        @if ($xsUrl)
                            <a href="{{ $xsUrl }}" target="_blank" rel="noopener">_xs</a>
                        @endif
                        <a href="{{ route('admin.media.edit', $media) }}" class="admin-media-link-edit">Bearbeiten</a>
                        <form method="POST" action="{{ route('admin.media.destroy', $media) }}" class="admin-media-inline-delete" onsubmit="return confirm('Dieses Medium wirklich loeschen?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-media-link-delete">Loeschen</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="admin-media-empty">
                    <strong>Noch keine Medien vorhanden.</strong>
                    <span>Lade das erste Bild hoch, um die Bibliothek zu fuellen.</span>
                </div>
            @endforelse
                </div>

                <div class="admin-media-empty admin-media-empty-search" data-media-empty-search hidden>
                    <strong>Keine Treffer fuer diese Suche.</strong>
                    <span>Versuche einen anderen Begriff, wechsle die Tags oder loesche den Filter.</span>
                </div>

                <div class="admin-media-pagination">
                    {{ $mediaItems->links() }}
                </div>
            </div>
        </div>
    </div>
</section>

<div
    class="admin-media-upload-popover"
    data-media-upload-popover
    data-upload-url="{{ route('admin.media.store') }}"
    data-attach-tag-url-template="{{ route('admin.media.attach-tag', ['media' => '__MEDIA_ID__']) }}"
    data-edit-url-template="{{ route('admin.media.edit', ['media' => '__MEDIA_ID__']) }}"
    data-destroy-url-template="{{ route('admin.media.destroy', ['media' => '__MEDIA_ID__']) }}"
    data-csrf="{{ csrf_token() }}"
    hidden
>
    <div class="admin-media-upload-popover__backdrop" data-media-upload-close></div>
    <div class="admin-media-upload-popover__dialog" role="dialog" aria-modal="true" aria-labelledby="admin-media-upload-title">
        <div class="admin-media-upload-popover__head">
            <div>
                <strong id="admin-media-upload-title">Bilder hochladen</strong>
                <span>Einzeln oder gesammelt, mit sichtbarem Fortschritt pro Datei.</span>
            </div>
            <button type="button" class="admin-media-upload-popover__close" data-media-upload-close aria-label="Upload Popover schliessen">&times;</button>
        </div>

        <form class="admin-media-upload-form" data-media-upload-form>
            <div class="admin-form-grid">
                <label class="admin-form-field admin-form-field-full admin-media-file-dropzone">
                    <span>Dateien</span>
                    <input type="file" name="files[]" accept="image/*" multiple required data-media-upload-files>
                    <small data-media-upload-dropzone-note>Du kannst eine oder mehrere Dateien gleichzeitig auswaehlen oder direkt hier hineinziehen.</small>
                </label>

                <label class="admin-form-field admin-form-field-full">
                    <span>Dateiname fuer Einzelupload (optional)</span>
                    <input type="text" name="name" maxlength="255" placeholder="Bei Mehrfachupload wird pro Datei automatisch ein Name verwendet" data-media-upload-name>
                </label>

                <label class="admin-form-field admin-form-field-full">
                    <span>Alt-Text fuer alle Dateien</span>
                    <input type="text" name="alt_text" maxlength="255" placeholder="Optionaler Standard-Alt-Text" data-media-upload-alt>
                </label>

                <label class="admin-form-field admin-form-field-full">
                    <span>Quelle fuer alle Dateien</span>
                    <input type="text" name="source" maxlength="255" placeholder="z.B. Produktion / Fotograf / Kampagne" data-media-upload-source>
                </label>

                <label class="admin-form-field admin-form-field-full">
                    <span>Tags fuer alle Dateien</span>
                    <input type="text" name="tags" maxlength="320" placeholder="z.B. hero, team, kampagne" data-media-upload-tags>
                </label>

                <div class="admin-media-size-group">
                    <h4>Original</h4>
                    <label class="admin-form-field">
                        <span>Max Breite</span>
                        <input type="number" name="max_width" value="{{ old('max_width', $defaults['max_width']) }}" min="1" max="8000" required data-media-upload-max-width>
                    </label>
                    <label class="admin-form-field">
                        <span>Max Hoehe</span>
                        <input type="number" name="max_height" value="{{ old('max_height', $defaults['max_height']) }}" min="1" max="8000" required data-media-upload-max-height>
                    </label>
                </div>

                <div class="admin-media-size-group">
                    <h4>_xs Mobile</h4>
                    <label class="admin-form-field">
                        <span>Max Breite</span>
                        <input type="number" name="xs_max_width" value="{{ old('xs_max_width', $defaults['xs_max_width']) }}" min="1" max="4000" required data-media-upload-xs-max-width>
                    </label>
                    <label class="admin-form-field">
                        <span>Max Hoehe</span>
                        <input type="number" name="xs_max_height" value="{{ old('xs_max_height', $defaults['xs_max_height']) }}" min="1" max="4000" required data-media-upload-xs-max-height>
                    </label>
                </div>

                <label class="admin-form-field admin-form-field-full">
                    <span>Qualitaet (WebP)</span>
                    <input type="number" name="quality" value="{{ old('quality', $defaults['quality']) }}" min="1" max="100" required data-media-upload-quality>
                </label>
            </div>

            <div class="admin-media-upload-queue" data-media-upload-queue hidden>
                <div class="admin-media-upload-queue-head">
                    <strong>Upload-Fortschritt</strong>
                    <span data-media-upload-summary>0 / 0 fertig</span>
                </div>
                <div class="admin-media-upload-overall">
                    <div class="admin-media-upload-overall-head">
                        <strong>Gesamtfortschritt</strong>
                        <span data-media-upload-overall-percent>0%</span>
                    </div>
                    <div class="admin-media-upload-progress admin-media-upload-progress--overall">
                        <span data-media-upload-overall-bar></span>
                    </div>
                </div>
                <div class="admin-media-upload-items" data-media-upload-items></div>
            </div>

            <div class="admin-media-upload-actions">
                <p class="admin-media-upload-status" data-media-upload-status>Waehle Dateien aus und starte den Upload im Popover.</p>
                <div class="admin-media-upload-action-buttons">
                    <button type="button" class="admin-media-toolbar-btn" data-media-upload-close>Abbrechen</button>
                    <button type="submit" class="btn" data-media-upload-submit>Upload starten</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.admin-media-hero {
    position: relative;
    display: grid;
    grid-template-columns: 1.45fr 1fr;
    gap: 1.2rem;
    align-items: center;
    padding: 1.1rem 1.2rem;
    border: 1px solid var(--admin-line);
    border-radius: 1rem;
    margin-bottom: 1rem;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(249, 250, 252, 0.96));
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
}

.admin-media-kicker {
    margin: 0;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--admin-accent);
    font-weight: 700;
}

.admin-media-headline {
    margin: 0.35rem 0 0;
    font-size: clamp(1.2rem, 2vw, 1.75rem);
    color: var(--admin-ink);
    letter-spacing: -0.02em;
}

.admin-media-lead {
    margin: 0.55rem 0 0;
    color: var(--admin-muted);
    font-size: 0.9rem;
    line-height: 1.45;
}

.admin-media-hero-stats {
    display: grid;
    gap: 0.5rem;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.admin-media-hero-stats > div {
    border: 1px solid rgba(148, 163, 184, 0.18);
    background: rgba(255, 255, 255, 0.92);
    border-radius: 0.72rem;
    padding: 0.58rem 0.62rem;
    text-align: center;
}

.admin-media-hero-stats span {
    display: block;
    font-size: 0.68rem;
    color: var(--admin-muted);
    line-height: 1.2;
}

.admin-media-hero-stats strong {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.82rem;
    color: var(--admin-ink);
    letter-spacing: 0.01em;
}

.admin-media-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1rem;
}

.admin-media-page-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 0.9rem 1rem;
    border: 1px solid var(--admin-line);
    border-radius: 0.95rem;
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
}

.admin-media-alert {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    align-items: start;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding: 0.9rem 1rem;
    border-radius: 0.8rem;
    border: 1px solid transparent;
    font-size: 0.88rem;
    line-height: 1.45;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
}

.admin-media-alert svg {
    width: 1.2rem;
    height: 1.2rem;
    margin-top: 0.08rem;
    flex-shrink: 0;
}

.admin-media-alert span {
    min-width: 0;
    word-break: break-word;
}

.admin-media-alert--success {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.12), rgba(236, 253, 245, 0.96));
    border-color: rgba(34, 197, 94, 0.24);
    color: #15803d;
}

.admin-media-alert--error {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(254, 242, 242, 0.96));
    border-color: rgba(239, 68, 68, 0.26);
    color: #b91c1c;
}

.admin-media-page-actions__copy {
    display: grid;
    gap: 0.18rem;
}

.admin-media-page-actions__copy strong {
    color: var(--admin-ink);
    font-size: 0.96rem;
}

.admin-media-page-actions__copy span {
    color: var(--admin-muted);
    font-size: 0.76rem;
}

.admin-media-panel {
    border: 1px solid var(--admin-line);
    background: rgba(255, 255, 255, 0.97);
    border-radius: 0.95rem;
    padding: 0.95rem;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
}

.admin-media-panel-library {
    border-color: rgba(148, 163, 184, 0.12);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
}

.admin-media-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.8rem;
}

.admin-media-panel-head h3 {
    margin: 0;
    font-size: 0.96rem;
    color: var(--admin-ink);
    letter-spacing: -0.01em;
}

.admin-media-panel-head span {
    font-size: 0.68rem;
    color: var(--admin-muted);
    border: 1px solid var(--admin-line);
    border-radius: 999px;
    padding: 0.16rem 0.5rem;
    background: rgba(255, 255, 255, 0.7);
}

.admin-media-library-top {
    display: grid;
    gap: 0.9rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.admin-media-library-shell {
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    gap: 1.2rem;
}

.admin-media-library-content {
    min-width: 0;
}

.admin-media-tags-sidebar {
    display: grid;
    align-content: start;
    gap: 0.9rem;
    min-width: 0;
}

.admin-media-tags-sidebar-head {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 0.7rem;
}

.admin-media-tags-sidebar-head strong {
    display: block;
    color: var(--admin-ink);
    font-size: 0.86rem;
}

.admin-media-tags-sidebar-head span {
    display: block;
    margin-top: 0.14rem;
    color: var(--admin-muted);
    font-size: 0.72rem;
    line-height: 1.4;
}

.admin-media-tags-toggle {
    border: 0;
    background: rgba(241, 245, 249, 0.95);
    color: var(--admin-ink);
    border-radius: 999px;
    padding: 0.28rem 0.54rem;
    font: inherit;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
}

.admin-media-tags-sidebar-body {
    display: grid;
    gap: 0.9rem;
}

.admin-media-tags-sidebar.is-collapsed .admin-media-tags-sidebar-body {
    display: none;
}

.admin-media-tags-create {
    display: grid;
    gap: 0.45rem;
}

.admin-media-tags-create-field {
    display: grid;
    gap: 0.28rem;
}

.admin-media-tags-create-field span {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--admin-muted);
}

.admin-media-tags-create-input {
    width: 100%;
    border: 0;
    border-radius: 0.65rem;
    padding: 0.55rem 0.65rem;
    font: inherit;
    background: rgba(248, 250, 252, 0.96);
}

.admin-media-tags-nav {
    display: grid;
    gap: 0.45rem;
}

.admin-media-tag-list {
    display: grid;
    gap: 0.35rem;
}

.admin-media-tag-filter {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.65rem;
    width: 100%;
    border: 0;
    border-radius: 0.7rem;
    padding: 0.48rem 0.62rem;
    background: rgba(248, 250, 252, 0.96);
    color: var(--admin-ink);
    font: inherit;
    font-size: 0.75rem;
    text-align: left;
    cursor: pointer;
    transition: background 120ms ease, box-shadow 120ms ease, transform 120ms ease;
}

.admin-media-tag-filter:hover {
    background: #fff;
    box-shadow: inset 0 0 0 1px rgba(95, 134, 255, 0.14);
}

.admin-media-tag-filter.is-active {
    background: rgba(95, 134, 255, 0.1);
    color: var(--admin-accent);
    box-shadow: inset 0 0 0 1px rgba(95, 134, 255, 0.18);
}

.admin-media-tag-filter.is-drop-target {
    background: rgba(34, 197, 94, 0.12);
    box-shadow: inset 0 0 0 1px rgba(34, 197, 94, 0.2);
}

.admin-media-card-head,
.admin-media-preview-wrap,
.admin-media-tag-badges {
    cursor: grab;
}

.admin-media-tag-filter small {
    color: var(--admin-muted);
    font-size: 0.68rem;
    font-weight: 700;
}

.admin-media-library-head {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 0.9rem;
}

.admin-media-library-head h3 {
    margin: 0;
    font-size: 1rem;
    color: var(--admin-ink);
    letter-spacing: -0.01em;
}

.admin-media-library-head p {
    margin: 0.22rem 0 0;
    color: var(--admin-muted);
    font-size: 0.76rem;
}

.admin-media-library-count {
    flex-shrink: 0;
    font-size: 0.72rem;
    color: var(--admin-muted);
    border: 0;
    border-radius: 999px;
    padding: 0.22rem 0.58rem;
    background: rgba(248, 250, 252, 0.95);
}

.admin-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.62rem;
    margin-top: 0;
}

.admin-form-field {
    display: grid;
    gap: 0.26rem;
}

.admin-form-field-full {
    grid-column: 1 / -1;
}

.admin-form-field > span {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--admin-muted);
}

.admin-form-field input {
    width: 100%;
    border: 1px solid var(--admin-line);
    border-radius: 0.62rem;
    padding: 0.5rem 0.6rem;
    font: inherit;
    background: rgba(255, 255, 255, 0.85);
    transition: border-color 120ms ease, box-shadow 120ms ease;
}

.admin-form-field input:focus {
    outline: none;
    border-color: rgba(95, 134, 255, 0.62);
    box-shadow: 0 0 0 3px rgba(95, 134, 255, 0.12);
}

.admin-form-actions {
    grid-column: 1 / -1;
    margin-top: 0.18rem;
}

.admin-media-upload-trigger--top {
    flex-shrink: 0;
}

.admin-media-size-group {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.62rem;
    padding: 0.55rem;
    border: 1px solid rgba(95, 134, 255, 0.2);
    border-radius: 0.72rem;
    background: rgba(95, 134, 255, 0.05);
}

.admin-media-size-group h4 {
    grid-column: 1 / -1;
    margin: 0;
    font-size: 0.74rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--admin-accent);
}

.admin-media-grid {
    display: grid;
    gap: 0.8rem;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
}

.admin-media-toolbar {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 0.9rem;
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: 0;
}

.admin-media-search-wrap {
    display: grid;
    gap: 0.28rem;
    flex: 1 1 auto;
}

.admin-media-search-wrap span {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--admin-muted);
}

.admin-media-search {
    width: 100%;
    border: 0;
    border-radius: 0.7rem;
    padding: 0.62rem 0.72rem;
    font: inherit;
    background: rgba(248, 250, 252, 0.96);
}

.admin-media-search:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(95, 134, 255, 0.12);
    background: #fff;
}

.admin-media-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.admin-media-toolbar-btn {
    border: 0;
    background: rgba(248, 250, 252, 0.96);
    color: var(--admin-ink);
    border-radius: 999px;
    padding: 0.4rem 0.7rem;
    font: inherit;
    font-size: 0.76rem;
    font-weight: 600;
    cursor: pointer;
}

.admin-media-toolbar-btn:hover {
    background: rgba(241, 245, 249, 1);
}

.admin-media-selection-status {
    font-size: 0.76rem;
    font-weight: 700;
    color: var(--admin-accent);
}

.admin-media-bulk-form {
    display: grid;
    gap: 0.75rem;
}

.admin-media-bulk-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    padding: 0.8rem 0.9rem;
    border-radius: 0.8rem;
    border: 0;
    background: rgba(254, 242, 242, 0.9);
}

.admin-media-bulk-bar strong {
    display: block;
    color: #991b1b;
    font-size: 0.84rem;
}

.admin-media-bulk-bar span {
    font-size: 0.72rem;
    color: #b45309;
}

.admin-media-bulk-actions {
    display: flex;
    gap: 0.55rem;
}

.btn-danger {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: #fff;
    border: 0;
}

.admin-media-card {
    position: relative;
    display: grid;
    grid-template-columns: 1fr;
    grid-template-areas:
        'select'
        'preview'
        'head'
        'meta'
        'links';
    align-items: start;
    gap: 0.55rem;
    border: 0;
    border-radius: 0.8rem;
    padding: 0.7rem;
    background: rgba(250, 250, 251, 0.96);
    box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.08);
    transition: border-color 140ms ease, background 140ms ease, box-shadow 140ms ease;
}

.admin-media-card.is-dragging {
    opacity: 0.6;
    transform: scale(0.985);
}

.admin-media-card:hover {
    transform: none;
    background: #fff;
    box-shadow: inset 0 0 0 1px rgba(95, 134, 255, 0.18), 0 6px 16px rgba(15, 23, 42, 0.04);
}

.admin-media-card.is-selected {
    background: rgba(239, 244, 255, 0.9);
    box-shadow: inset 0 0 0 1px rgba(95, 134, 255, 0.3), 0 0 0 2px rgba(95, 134, 255, 0.05);
}

.admin-media-select {
    grid-area: select;
    display: inline-flex;
    align-items: center;
    gap: 0.38rem;
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--admin-muted);
    justify-self: start;
}

.admin-media-select input {
    accent-color: var(--admin-accent);
}

.admin-media-preview-wrap {
    grid-area: preview;
    position: relative;
    border-radius: 0.6rem;
    overflow: hidden;
    border: 0;
    background: #f3f4f6;
    min-height: 168px;
}

.admin-media-preview {
    width: 100%;
    height: 168px;
    display: block;
    object-fit: cover;
    max-height: none;
    user-select: none;
    -webkit-user-drag: none;
}

.admin-media-dimension-badge {
    position: absolute;
    left: 0.4rem;
    bottom: 0.4rem;
    padding: 0.18rem 0.38rem;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.72);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
}

.admin-media-card-head {
    grid-area: head;
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 0.65rem;
    min-width: 0;
}

.admin-media-card-head strong,
.admin-media-card-head span {
    display: block;
}

.admin-media-card-head strong {
    color: var(--admin-ink);
    font-size: 0.82rem;
    line-height: 1.3;
    word-break: break-word;
}

.admin-media-card-head span {
    margin-top: 0.14rem;
    color: var(--admin-muted);
    font-size: 0.7rem;
    line-height: 1.35;
}

.admin-media-format-pill {
    flex-shrink: 0;
    border-radius: 999px;
    padding: 0.18rem 0.44rem;
    background: rgba(148, 163, 184, 0.12);
    color: #475569;
    font-size: 0.61rem;
    font-weight: 700;
}

.admin-media-meta {
    grid-area: meta;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    color: var(--admin-muted);
    font-size: 0.7rem;
    min-width: 0;
}

.admin-media-meta-path {
    color: var(--admin-ink);
    font-weight: 600;
    word-break: break-all;
}

.admin-media-tag-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.admin-media-tag-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.16rem 0.42rem;
    border-radius: 999px;
    background: rgba(241, 245, 249, 0.95);
    color: #475569;
    font-size: 0.64rem;
    font-weight: 600;
}

.admin-media-links {
    grid-area: links;
    display: flex;
    gap: 0.42rem;
    flex-wrap: wrap;
    justify-content: flex-start;
    align-items: center;
    padding-top: 0.15rem;
}

.admin-media-inline-delete {
    margin: 0;
}

.admin-media-links a {
    font-size: 0.7rem;
    font-weight: 600;
    border: 0;
    border-radius: 999px;
    padding: 0.22rem 0.55rem;
    color: var(--admin-ink);
    background: rgba(241, 245, 249, 0.95);
    white-space: nowrap;
}

.admin-media-links a:hover {
    border-color: rgba(95, 134, 255, 0.45);
    color: var(--admin-accent);
}

.admin-media-edit-form {
    display: grid;
    gap: 0.48rem;
}

.admin-media-empty {
    border: 1px dashed rgba(148, 163, 184, 0.28);
    border-radius: 0.9rem;
    padding: 1.2rem;
    text-align: center;
    color: var(--admin-muted);
    background: rgba(248, 250, 252, 0.92);
}

.admin-media-link-edit {
    border: 0;
    border-radius: 999px;
    padding: 0.22rem 0.55rem;
    color: var(--admin-accent);
    background: rgba(95, 134, 255, 0.1);
    font-weight: 700;
}

.admin-media-link-edit:hover {
    border-color: rgba(95, 134, 255, 0.62);
    background: rgba(95, 134, 255, 0.16);
}

.admin-media-link-delete {
    border: 0;
    border-radius: 999px;
    padding: 0.22rem 0.55rem;
    color: #b91c1c;
    background: rgba(254, 242, 242, 0.96);
    font: inherit;
    font-size: 0.7rem;
    font-weight: 700;
    cursor: pointer;
}

.admin-media-link-delete:hover {
    border-color: rgba(239, 68, 68, 0.42);
    background: rgba(254, 226, 226, 0.96);
}

.admin-media-empty strong {
    display: block;
    color: var(--admin-ink);
    margin-bottom: 0.28rem;
}

.admin-media-pagination {
    margin-top: 1rem;
    padding-top: 0.85rem;
    border-top: 1px solid rgba(148, 163, 184, 0.08);
}

.admin-media-empty-search {
    margin-top: 0.2rem;
}

.admin-media-upload-popover {
    position: fixed;
    inset: 0;
    z-index: 1400;
}

.admin-media-upload-popover__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.56);
    backdrop-filter: blur(8px);
}

.admin-media-upload-popover__dialog {
    position: relative;
    z-index: 1;
    width: min(860px, calc(100vw - 2rem));
    max-height: calc(100vh - 2rem);
    margin: 1rem auto;
    overflow: auto;
    border-radius: 1rem;
    border: 1px solid rgba(31, 48, 72, 0.12);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(246, 249, 255, 0.96));
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.24);
}

.admin-media-upload-popover__head {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1rem 0.5rem;
}

.admin-media-upload-popover__head strong {
    display: block;
    color: var(--admin-ink);
    font-size: 1rem;
}

.admin-media-upload-popover__head span {
    display: block;
    margin-top: 0.2rem;
    color: var(--admin-muted);
    font-size: 0.78rem;
}

.admin-media-upload-popover__close {
    border: 0;
    background: transparent;
    color: var(--admin-muted);
    font-size: 1.8rem;
    line-height: 1;
    cursor: pointer;
}

.admin-media-upload-form {
    display: grid;
    gap: 1rem;
    padding: 0.75rem 1rem 1rem;
}

.admin-media-file-dropzone {
    position: relative;
    padding: 0.9rem;
    border: 1px dashed rgba(95, 134, 255, 0.36);
    border-radius: 0.85rem;
    background: rgba(95, 134, 255, 0.04);
    transition: border-color 140ms ease, background 140ms ease, box-shadow 140ms ease;
}

.admin-media-file-dropzone small {
    color: var(--admin-muted);
    font-size: 0.72rem;
}

.admin-media-file-dropzone.is-dragover {
    border-color: rgba(95, 134, 255, 0.7);
    background: rgba(95, 134, 255, 0.1);
    box-shadow: 0 0 0 3px rgba(95, 134, 255, 0.12);
}

.admin-media-upload-queue {
    display: grid;
    gap: 0.75rem;
    padding: 0.9rem;
    border: 1px solid rgba(31, 48, 72, 0.1);
    border-radius: 0.9rem;
    background: rgba(255, 255, 255, 0.78);
}

.admin-media-upload-queue-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.admin-media-upload-queue-head strong {
    color: var(--admin-ink);
    font-size: 0.84rem;
}

.admin-media-upload-queue-head span {
    color: var(--admin-muted);
    font-size: 0.74rem;
    font-weight: 700;
}

.admin-media-upload-overall {
    display: grid;
    gap: 0.35rem;
    padding: 0.75rem;
    border: 1px solid rgba(31, 48, 72, 0.08);
    border-radius: 0.8rem;
    background: rgba(247, 250, 255, 0.9);
}

.admin-media-upload-overall-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.admin-media-upload-overall-head strong {
    color: var(--admin-ink);
    font-size: 0.76rem;
}

.admin-media-upload-overall-head span {
    color: var(--admin-accent);
    font-size: 0.74rem;
    font-weight: 800;
}

.admin-media-upload-progress--overall {
    height: 0.7rem;
}

.admin-media-upload-items {
    display: grid;
    gap: 0.6rem;
}

.admin-media-upload-item {
    display: grid;
    gap: 0.35rem;
}

.admin-media-upload-item-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.6rem;
    font-size: 0.74rem;
}

.admin-media-upload-item-head strong {
    color: var(--admin-ink);
    word-break: break-word;
}

.admin-media-upload-item-head span {
    color: var(--admin-muted);
    font-weight: 700;
    white-space: nowrap;
}

.admin-media-upload-progress {
    position: relative;
    overflow: hidden;
    height: 0.55rem;
    border-radius: 999px;
    background: rgba(148, 163, 184, 0.2);
}

.admin-media-upload-progress > span {
    display: block;
    height: 100%;
    width: 0%;
    border-radius: inherit;
    background: linear-gradient(90deg, #5f86ff, #23bd91);
    transition: width 120ms linear;
}

.admin-media-upload-item-note {
    color: var(--admin-muted);
    font-size: 0.7rem;
}

.admin-media-upload-item.is-success .admin-media-upload-item-note {
    color: #0f766e;
}

.admin-media-upload-item.is-error .admin-media-upload-item-note {
    color: #b91c1c;
}

.admin-media-upload-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.admin-media-upload-status {
    margin: 0;
    color: var(--admin-muted);
    font-size: 0.76rem;
}

.admin-media-upload-action-buttons {
    display: flex;
    align-items: center;
    gap: 0.55rem;
}

/* Premium surface and typography pass */
.admin-media-hero,
.admin-media-page-actions,
.admin-media-panel-library,
.admin-media-upload-popover__dialog {
    font-family: 'Sora', 'Avenir Next', 'Segoe UI', sans-serif;
}

.admin-media-hero {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(168, 137, 97, 0.26);
    border-radius: 1.35rem;
    padding: 1.35rem 1.45rem;
    background:
        radial-gradient(circle at 8% -12%, rgba(252, 228, 176, 0.5), transparent 38%),
        radial-gradient(circle at 96% 8%, rgba(112, 173, 186, 0.2), transparent 42%),
        linear-gradient(128deg, rgba(255, 252, 246, 0.98), rgba(242, 249, 252, 0.95));
    box-shadow: 0 20px 55px rgba(28, 33, 40, 0.11);
}

.admin-media-hero::after {
    content: '';
    position: absolute;
    right: -84px;
    bottom: -80px;
    width: 230px;
    height: 230px;
    border-radius: 42%;
    background: radial-gradient(circle, rgba(255, 214, 153, 0.34), rgba(255, 214, 153, 0));
    pointer-events: none;
}

.admin-media-kicker {
    color: #9b6e2f;
    letter-spacing: 0.14em;
    font-size: 0.7rem;
}

.admin-media-headline {
    max-width: 26ch;
    color: #1a2330;
    font-weight: 700;
    line-height: 1.14;
}

.admin-media-lead {
    max-width: 68ch;
    font-size: 0.86rem;
    color: #4b5c70;
}

.admin-media-hero-stats > div {
    border: 1px solid rgba(138, 156, 177, 0.24);
    border-radius: 0.86rem;
    background: linear-gradient(170deg, rgba(255, 255, 255, 0.98), rgba(244, 249, 252, 0.92));
    box-shadow: 0 8px 22px rgba(34, 54, 79, 0.08);
}

.admin-media-hero-stats strong {
    font-size: 0.94rem;
    color: #1c2a3c;
}

.admin-media-page-actions {
    border: 1px solid rgba(162, 179, 199, 0.22);
    border-radius: 1rem;
    padding: 0.95rem 1.05rem;
    background: linear-gradient(160deg, rgba(255, 255, 255, 0.97), rgba(246, 251, 255, 0.94));
    box-shadow: 0 14px 36px rgba(33, 44, 62, 0.09);
}

.admin-media-upload-trigger,
.admin-media-upload-submit,
.admin-media-bulk-actions .btn-danger {
    border: 0;
    border-radius: 999px;
    padding: 0.55rem 1.05rem;
    background: linear-gradient(100deg, #1f5c7a, #2f7f8d);
    color: #f8fcff;
    letter-spacing: 0.01em;
    font-weight: 700;
    box-shadow: 0 14px 24px rgba(25, 82, 106, 0.28);
    transition: transform 150ms ease, box-shadow 150ms ease, filter 150ms ease;
}

.admin-media-upload-trigger:hover,
.admin-media-upload-submit:hover,
.admin-media-bulk-actions .btn-danger:hover {
    transform: translateY(-1px);
    filter: saturate(1.1);
    box-shadow: 0 16px 30px rgba(25, 82, 106, 0.32);
}

.admin-media-panel-library {
    border: 1px solid rgba(161, 179, 203, 0.23);
    border-radius: 1.25rem;
    padding: 1.15rem;
    background:
        radial-gradient(circle at 90% 2%, rgba(199, 228, 236, 0.34), transparent 34%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(245, 248, 252, 0.95));
    box-shadow: 0 22px 48px rgba(20, 31, 46, 0.09);
}

.admin-media-library-shell {
    grid-template-columns: 244px minmax(0, 1fr);
    gap: 1.35rem;
}

.admin-media-tags-sidebar {
    position: sticky;
    top: 1.1rem;
    align-self: start;
    border: 1px solid rgba(163, 181, 203, 0.24);
    border-radius: 1rem;
    padding: 0.84rem;
    background: linear-gradient(180deg, rgba(254, 255, 255, 0.96), rgba(246, 250, 253, 0.9));
}

.admin-media-tags-toggle,
.admin-media-toolbar-btn {
    border: 1px solid rgba(154, 175, 200, 0.24);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(244, 248, 252, 0.93));
    color: #203047;
}

.admin-media-tags-toggle:hover,
.admin-media-toolbar-btn:hover {
    border-color: rgba(92, 132, 166, 0.42);
    background: #fff;
}

.admin-media-tag-filter {
    border: 1px solid rgba(148, 167, 188, 0.22);
    border-radius: 0.78rem;
    background: rgba(252, 254, 255, 0.92);
}

.admin-media-tag-filter.is-active {
    border-color: rgba(43, 113, 140, 0.42);
    background: linear-gradient(165deg, rgba(221, 241, 247, 0.9), rgba(247, 254, 255, 0.95));
    color: #1f6078;
}

.admin-media-library-top {
    gap: 1.05rem;
    margin-bottom: 1.15rem;
}

.admin-media-library-head h3 {
    font-size: 1.12rem;
    font-weight: 700;
    letter-spacing: -0.018em;
    color: #1a2a3d;
}

.admin-media-library-count {
    border: 1px solid rgba(159, 177, 197, 0.28);
    padding: 0.28rem 0.66rem;
    background: linear-gradient(180deg, rgba(253, 254, 255, 0.94), rgba(244, 249, 252, 0.94));
    color: #3d4f65;
}

.admin-media-toolbar {
    align-items: end;
    gap: 0.75rem;
    padding: 0.82rem;
    border: 1px solid rgba(162, 181, 203, 0.2);
    border-radius: 0.95rem;
    background: rgba(250, 253, 255, 0.78);
}

.admin-media-search {
    border: 1px solid rgba(157, 176, 197, 0.22);
    background: #fcfdff;
    border-radius: 0.76rem;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.admin-media-search:focus {
    border-color: rgba(49, 120, 147, 0.56);
    box-shadow: 0 0 0 3px rgba(60, 137, 165, 0.16);
}

.admin-media-grid {
    gap: 0.95rem;
    grid-template-columns: repeat(auto-fill, minmax(246px, 1fr));
}

.admin-media-card {
    border: 1px solid rgba(150, 170, 193, 0.2);
    border-radius: 1rem;
    padding: 0.76rem;
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(247, 250, 253, 0.95));
    box-shadow: 0 10px 24px rgba(31, 42, 59, 0.08);
    transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
    animation: mediaCardEnter 420ms ease both;
}

.admin-media-card:nth-child(2n) {
    animation-delay: 40ms;
}

.admin-media-card:nth-child(3n) {
    animation-delay: 80ms;
}

.admin-media-card:hover {
    transform: translateY(-2px);
    border-color: rgba(55, 127, 155, 0.38);
    box-shadow: 0 18px 34px rgba(30, 57, 82, 0.14);
}

.admin-media-preview-wrap {
    border-radius: 0.8rem;
    min-height: 176px;
    border: 1px solid rgba(163, 182, 205, 0.2);
    background: linear-gradient(180deg, #f0f5f9, #d8e7ef);
}

.admin-media-preview {
    height: 176px;
}

.admin-media-dimension-badge {
    background: rgba(14, 29, 43, 0.78);
    border: 1px solid rgba(240, 247, 255, 0.32);
}

.admin-media-format-pill {
    background: rgba(211, 228, 239, 0.62);
    color: #2a5366;
}

.admin-media-tag-badge {
    border: 1px solid rgba(155, 178, 201, 0.3);
    background: rgba(244, 250, 254, 0.9);
    color: #31566f;
}

.admin-media-links a,
.admin-media-link-delete,
.admin-media-link-edit {
    border: 1px solid rgba(156, 176, 196, 0.25);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(244, 249, 253, 0.96));
}

.admin-media-link-edit {
    color: #1f6684;
}

.admin-media-upload-popover__backdrop {
    background:
        radial-gradient(circle at 12% 8%, rgba(88, 147, 164, 0.22), transparent 33%),
        rgba(10, 18, 26, 0.62);
}

.admin-media-upload-popover__dialog {
    border: 1px solid rgba(173, 196, 218, 0.25);
    border-radius: 1.2rem;
    background:
        radial-gradient(circle at 90% -6%, rgba(255, 218, 173, 0.34), transparent 38%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(243, 249, 252, 0.96));
}

.admin-media-upload-popover__head {
    padding: 1.1rem 1.15rem 0.65rem;
}

.admin-media-upload-form {
    padding: 0.78rem 1.15rem 1.1rem;
}

.admin-media-file-dropzone {
    border: 1px dashed rgba(57, 126, 152, 0.48);
    background: linear-gradient(180deg, rgba(231, 244, 248, 0.7), rgba(244, 250, 253, 0.86));
}

.admin-media-upload-queue {
    border: 1px solid rgba(160, 183, 206, 0.3);
    background: rgba(253, 255, 255, 0.86);
}

.admin-media-upload-progress > span {
    background: linear-gradient(90deg, #2c6e86, #41b88e);
}

.admin-media-alert {
    border-radius: 0.92rem;
}

.admin-media-alert--success {
    background: linear-gradient(135deg, rgba(20, 166, 110, 0.16), rgba(241, 255, 250, 0.96));
    border-color: rgba(20, 166, 110, 0.28);
    color: #0b7653;
}

.admin-media-alert--error {
    background: linear-gradient(135deg, rgba(222, 88, 74, 0.15), rgba(255, 244, 242, 0.95));
    border-color: rgba(220, 75, 75, 0.28);
    color: #9f2d2d;
}

@keyframes mediaCardEnter {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 680px) {
    .admin-media-hero {
        grid-template-columns: 1fr;
        gap: 0.85rem;
    }

    .admin-media-hero-stats {
        grid-template-columns: 1fr 1fr;
    }

    .admin-media-layout {
        grid-template-columns: 1fr;
    }

    .admin-form-grid {
        grid-template-columns: 1fr;
    }

    .admin-media-preview-wrap {
        min-height: 150px;
    }

    .admin-media-preview {
        height: 150px;
    }

    .admin-media-links {
        justify-content: flex-start;
    }

    .admin-media-page-actions,
    .admin-media-library-head,
    .admin-media-toolbar,
    .admin-media-bulk-bar,
    .admin-media-upload-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .admin-media-library-shell {
        grid-template-columns: 1fr;
    }

    .admin-media-tags-sidebar {
        position: static;
    }

    .admin-media-size-group {
        grid-template-columns: 1fr;
    }

    .admin-media-upload-popover__dialog {
        width: calc(100vw - 1rem);
        margin: 0.5rem auto;
        max-height: calc(100vh - 1rem);
    }

    .admin-media-upload-action-buttons {
        justify-content: stretch;
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const grid = document.querySelector('[data-media-grid]');
    const searchInput = document.querySelector('[data-media-search]');
    const cards = [...document.querySelectorAll('[data-media-card]')];
    const checkboxes = [...document.querySelectorAll('[data-media-checkbox]')];
    const selectionStatus = document.querySelector('[data-media-selection-status]');
    const bulkBar = document.querySelector('[data-media-bulk-bar]');
    const bulkCount = document.querySelector('[data-media-bulk-count]');
    const bulkForm = document.querySelector('[data-media-bulk-form]');
    const bulkHiddenInputs = document.querySelector('[data-media-bulk-hidden-inputs]');
    const emptySearch = document.querySelector('[data-media-empty-search]');
    const selectVisibleButton = document.querySelector('[data-media-select-visible]');
    const clearSelectionButton = document.querySelector('[data-media-clear-selection]');
    const uploadPopover = document.querySelector('[data-media-upload-popover]');
    const uploadOpenButton = document.querySelector('[data-media-upload-open]');
    const uploadCloseButtons = [...document.querySelectorAll('[data-media-upload-close]')];
    const uploadForm = document.querySelector('[data-media-upload-form]');
    const uploadFilesInput = document.querySelector('[data-media-upload-files]');
    const uploadNameInput = document.querySelector('[data-media-upload-name]');
    const uploadAltInput = document.querySelector('[data-media-upload-alt]');
    const uploadSourceInput = document.querySelector('[data-media-upload-source]');
    const uploadTagsInput = document.querySelector('[data-media-upload-tags]');
    const uploadMaxWidthInput = document.querySelector('[data-media-upload-max-width]');
    const uploadMaxHeightInput = document.querySelector('[data-media-upload-max-height]');
    const uploadXsMaxWidthInput = document.querySelector('[data-media-upload-xs-max-width]');
    const uploadXsMaxHeightInput = document.querySelector('[data-media-upload-xs-max-height]');
    const uploadQualityInput = document.querySelector('[data-media-upload-quality]');
    const uploadQueue = document.querySelector('[data-media-upload-queue]');
    const uploadItems = document.querySelector('[data-media-upload-items]');
    const uploadSummary = document.querySelector('[data-media-upload-summary]');
    const uploadOverallBar = document.querySelector('[data-media-upload-overall-bar]');
    const uploadOverallPercent = document.querySelector('[data-media-upload-overall-percent]');
    const uploadStatus = document.querySelector('[data-media-upload-status]');
    const uploadSubmit = document.querySelector('[data-media-upload-submit]');
    const uploadDropzone = document.querySelector('.admin-media-file-dropzone');
    const uploadDropzoneNote = document.querySelector('[data-media-upload-dropzone-note]');
    const libraryCountBadge = document.querySelector('[data-media-library-count]');
    const allCountBadge = document.querySelector('[data-media-all-count]');
    const tagSidebar = document.querySelector('[data-media-tags-sidebar]');
    const tagSidebarToggle = document.querySelector('[data-media-tags-toggle]');
    const tagSidebarBody = document.querySelector('[data-media-tags-body]');
    const tagList = document.querySelector('[data-media-tag-list]');
    const tagCreateInput = document.querySelector('[data-media-tag-create-input]');
    const tagCreateButton = document.querySelector('[data-media-tag-create]');
    const heroTotalStat = document.querySelector('.admin-media-hero-stats strong');
    const csrfToken = uploadPopover?.dataset.csrf || '';
    const uploadUrl = uploadPopover?.dataset.uploadUrl || '';
    const attachTagUrlTemplate = uploadPopover?.dataset.attachTagUrlTemplate || '';
    const editUrlTemplate = uploadPopover?.dataset.editUrlTemplate || '';
    const destroyUrlTemplate = uploadPopover?.dataset.destroyUrlTemplate || '';

    if (!grid || !bulkForm) {
        return;
    }

    let isUploading = false;
    let activeDraggedCard = null;
    const activeTagFilters = new Set();

    const normalizeTag = (value) => {
        return String(value || '').trim().toLowerCase();
    };

    const parseTagList = (value) => {
        return String(value || '')
            .split(',')
            .map((tag) => normalizeTag(tag))
            .filter((tag, index, items) => tag !== '' && items.indexOf(tag) === index);
    };

    const tagFilterButtons = () => [...document.querySelectorAll('[data-media-tag-filter]')];

    const updateOverallProgress = (totalCount, completedCount, currentFilePercent = 0) => {
        if (!uploadOverallBar || !uploadOverallPercent) {
            return;
        }

        const safeTotal = Math.max(1, totalCount);
        const percent = Math.max(0, Math.min(100, ((completedCount + (currentFilePercent / 100)) / safeTotal) * 100));
        uploadOverallBar.style.width = `${percent}%`;
        uploadOverallPercent.textContent = `${Math.round(percent)}%`;
    };

    const updateDropzoneNote = (files) => {
        if (!uploadDropzoneNote) {
            return;
        }

        const count = files?.length || 0;
        if (count === 0) {
            uploadDropzoneNote.textContent = 'Du kannst eine oder mehrere Dateien gleichzeitig auswaehlen oder direkt hier hineinziehen.';
            return;
        }

        uploadDropzoneNote.textContent = count === 1
            ? `${files[0].name} ausgewaehlt.`
            : `${count} Dateien ausgewaehlt und bereit fuer den Upload.`;
    };

    const syncBulkInputs = () => {
        bulkHiddenInputs?.querySelectorAll('input[name="media_ids[]"]').forEach((input) => input.remove());

        const checked = checkboxes.filter((checkbox) => checkbox.checked);
        checked.forEach((checkbox) => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'media_ids[]';
            hiddenInput.value = checkbox.value;
            bulkHiddenInputs?.appendChild(hiddenInput);
        });

        const count = checked.length;
        if (selectionStatus) {
            selectionStatus.textContent = `${count} ausgewaehlt`;
        }
        if (bulkCount) {
            bulkCount.textContent = count === 1 ? '1 Bild markiert' : `${count} Bilder markiert`;
        }
        if (bulkBar) {
            bulkBar.hidden = count === 0;
        }
    };

    const visibleCards = () => cards.filter((card) => !card.hidden);

    const updateLibraryCount = (delta) => {
        if (libraryCountBadge) {
            const match = libraryCountBadge.textContent.match(/\d+/);
            const current = match ? Number(match[0]) : cards.length;
            libraryCountBadge.textContent = `${Math.max(0, current + delta)} Dateien`;
        }

        if (allCountBadge) {
            const current = Number(allCountBadge.textContent || '0');
            if (Number.isFinite(current)) {
                allCountBadge.textContent = String(Math.max(0, current + delta));
            }
        }

        if (heroTotalStat) {
            const current = Number(heroTotalStat.textContent || '0');
            if (Number.isFinite(current)) {
                heroTotalStat.textContent = String(Math.max(0, current + delta));
            }
        }
    };

    const createDeleteFormMarkup = (mediaId) => {
        const action = destroyUrlTemplate.replace('__MEDIA_ID__', String(mediaId));
        return `
            <form method="POST" action="${action}" class="admin-media-inline-delete" onsubmit="return confirm('Dieses Medium wirklich loeschen?');">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-media-link-delete">Loeschen</button>
            </form>
        `;
    };

    const buildFilterValue = (item) => {
        const tagText = Array.isArray(item.tags) ? item.tags.join(' ') : '';
        return `${item.name || ''} ${item.alt_text || ''} ${item.source || ''} ${item.filename || ''} ${tagText}`.trim().toLowerCase();
    };

    const renderCardTagsMarkup = (tags) => {
        const safeTags = Array.isArray(tags) ? tags : [];
        return safeTags.map((tag) => `<span class="admin-media-tag-badge">${tag}</span>`).join('');
    };

    const createMediaCard = (item) => {
        const article = document.createElement('article');
        article.className = 'admin-media-card';
        article.setAttribute('draggable', 'true');
        article.dataset.mediaId = String(item.id);
        article.setAttribute('data-media-card', '');
        article.dataset.mediaFilter = buildFilterValue(item);
        article.dataset.mediaTags = Array.isArray(item.tags) ? item.tags.join(',') : '';

        const editUrl = editUrlTemplate.replace('__MEDIA_ID__', String(item.id));
        const xsLink = item.preview_url && item.preview_url !== item.url
            ? `<a href="${item.preview_url}" target="_blank" rel="noopener">_xs</a>`
            : '';

        article.innerHTML = `
            <label class="admin-media-select">
                <input type="checkbox" value="${item.id}" data-media-checkbox>
                <span>Auswaehlen</span>
            </label>
            <div class="admin-media-preview-wrap">
                <img src="${item.url}" alt="${item.alt_text || 'Media Preview'}" class="admin-media-preview">
                <span class="admin-media-dimension-badge">${item.width || '?'} x ${item.height || '?'}</span>
            </div>
            <div class="admin-media-card-head">
                <div>
                    <strong>${item.name || item.filename || 'Neues Medium'}</strong>
                    <span>${item.alt_text || 'Ohne Alt-Text'}</span>
                </div>
                <span class="admin-media-format-pill">WEBP</span>
            </div>
            <div class="admin-media-meta">
                <small class="admin-media-meta-path">${item.filename || ''}</small>
                <small>${item.source || 'Keine Quelle gepflegt'}</small>
            </div>
            <div class="admin-media-tag-badges" data-media-card-tags>${renderCardTagsMarkup(item.tags)}</div>
            <div class="admin-media-links">
                <a href="${item.url}" target="_blank" rel="noopener">Original</a>
                ${xsLink}
                <a href="${editUrl}" class="admin-media-link-edit">Bearbeiten</a>
                ${createDeleteFormMarkup(item.id)}
            </div>
        `;

        const checkbox = article.querySelector('[data-media-checkbox]');
        if (checkbox instanceof HTMLInputElement) {
            checkbox.addEventListener('change', () => {
                article.classList.toggle('is-selected', checkbox.checked);
                if (!checkboxes.includes(checkbox)) {
                    checkboxes.push(checkbox);
                }
                if (!cards.includes(article)) {
                    cards.push(article);
                }
                syncBulkInputs();
            });
        }

        wireCardDrag(article);

        return article;
    };

    const ensureTagFilterExists = (tagName) => {
        const normalizedTag = normalizeTag(tagName);
        if (!normalizedTag || !tagList) {
            return null;
        }

        const existing = tagList.querySelector(`[data-media-tag-filter="${normalizedTag}"]`);
        if (existing instanceof HTMLButtonElement) {
            return existing;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'admin-media-tag-filter';
        button.dataset.mediaTagFilter = normalizedTag;
        button.dataset.mediaTagDropzone = normalizedTag;
        button.innerHTML = `<span>${normalizedTag}</span><small data-media-tag-count>0</small>`;
        tagList.appendChild(button);
        wireTagFilterButton(button);
        wireTagDropzone(button);
        return button;
    };

    const syncTagCounts = () => {
        const counts = new Map();

        cards.forEach((card) => {
            const tags = parseTagList(card.dataset.mediaTags || '');
            tags.forEach((tag) => {
                counts.set(tag, (counts.get(tag) || 0) + 1);
            });
        });

        tagFilterButtons().forEach((button) => {
            const tag = button.dataset.mediaTagFilter || '';
            if (tag === '__all__') {
                if (allCountBadge) {
                    allCountBadge.textContent = String(cards.length);
                }
                return;
            }

            const countEl = button.querySelector('[data-media-tag-count]');
            if (countEl instanceof HTMLElement) {
                countEl.textContent = String(counts.get(tag) || 0);
            }
        });
    };

    const setCardTags = (card, tags) => {
        const normalizedTags = parseTagList(Array.isArray(tags) ? tags.join(',') : tags);
        card.dataset.mediaTags = normalizedTags.join(',');
        card.dataset.mediaFilter = `${buildFilterValue({
            name: card.querySelector('.admin-media-card-head strong')?.textContent || '',
            alt_text: card.querySelector('.admin-media-card-head span')?.textContent || '',
            source: card.querySelector('.admin-media-meta small:nth-child(2)')?.textContent || '',
            filename: card.querySelector('.admin-media-meta-path')?.textContent || '',
            tags: normalizedTags,
        })}`;
        const badges = card.querySelector('[data-media-card-tags]');
        if (badges instanceof HTMLElement) {
            badges.innerHTML = renderCardTagsMarkup(normalizedTags);
        }
    };

    const wireCardDrag = (card) => {
        card.addEventListener('dragstart', (event) => {
            const mediaId = card.dataset.mediaId || '';
            if (!mediaId) {
                return;
            }

            activeDraggedCard = card;
            event.dataTransfer?.setData('text/media-id', mediaId);
            event.dataTransfer?.setData('text/plain', mediaId);
            event.dataTransfer.effectAllowed = 'move';
            card.classList.add('is-dragging');
        });

        card.addEventListener('dragend', () => {
            activeDraggedCard = null;
            card.classList.remove('is-dragging');
        });
    };

    const attachTagToMedia = async (card, tagName) => {
        const mediaId = card.dataset.mediaId || '';
        const normalizedTag = normalizeTag(tagName);
        if (!mediaId || !normalizedTag || !attachTagUrlTemplate) {
            return;
        }

        const existingTags = parseTagList(card.dataset.mediaTags || '');
        if (existingTags.includes(normalizedTag)) {
            return;
        }

        const response = await fetch(attachTagUrlTemplate.replace('__MEDIA_ID__', mediaId), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ tag: normalizedTag }),
        });

        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            if (uploadStatus) {
                uploadStatus.textContent = payload?.message || 'Tag konnte nicht gespeichert werden.';
            }
            return;
        }

        const updatedTags = Array.isArray(payload?.item?.tags) ? payload.item.tags : [normalizedTag];
        setCardTags(card, updatedTags);
        ensureTagFilterExists(normalizedTag);
        syncTagCounts();
        applyFilter();
    };

    const wireTagFilterButton = (button) => {
        button.addEventListener('click', () => {
            const tag = button.dataset.mediaTagFilter || '';
            if (tag === '__all__') {
                activeTagFilters.clear();
            } else {
                const shouldClear = activeTagFilters.has(tag);
                activeTagFilters.clear();
                if (!shouldClear) {
                    activeTagFilters.add(tag);
                }
            }

            tagFilterButtons().forEach((filterButton) => {
                const filterTag = filterButton.dataset.mediaTagFilter || '';
                const isActive = filterTag === '__all__'
                    ? activeTagFilters.size === 0
                    : activeTagFilters.has(filterTag);
                filterButton.classList.toggle('is-active', isActive);
            });

            applyFilter();
        });
    };

    const wireTagDropzone = (button) => {
        const tag = button.dataset.mediaTagDropzone || '';
        if (!tag) {
            return;
        }

        button.addEventListener('dragover', (event) => {
            event.preventDefault();
            button.classList.add('is-drop-target');
        });

        button.addEventListener('dragleave', () => {
            button.classList.remove('is-drop-target');
        });

        button.addEventListener('drop', async (event) => {
            event.preventDefault();
            button.classList.remove('is-drop-target');
            const mediaId = event.dataTransfer?.getData('text/media-id') || event.dataTransfer?.getData('text/plain') || '';
            const card = activeDraggedCard || cards.find((entry) => entry.dataset.mediaId === mediaId);
            if (!card) {
                return;
            }

            await attachTagToMedia(card, tag);
        });
    };

    const openUploadPopover = () => {
        if (!uploadPopover || isUploading) {
            return;
        }

        uploadPopover.hidden = false;
        document.body.classList.add('admin-media-popover-open');
    };

    const closeUploadPopover = () => {
        if (!uploadPopover || isUploading) {
            return;
        }

        uploadPopover.hidden = true;
        document.body.classList.remove('admin-media-popover-open');
        if (uploadForm instanceof HTMLFormElement) {
            uploadForm.reset();
        }
        if (uploadQueue) {
            uploadQueue.hidden = true;
        }
        if (uploadItems) {
            uploadItems.innerHTML = '';
        }
        if (uploadSummary) {
            uploadSummary.textContent = '0 / 0 fertig';
        }
        updateOverallProgress(1, 0, 0);
        updateDropzoneNote([]);
        if (uploadStatus) {
            uploadStatus.textContent = 'Waehle Dateien aus und starte den Upload im Popover.';
        }
    };

    const createQueueItem = (file) => {
        const item = document.createElement('div');
        item.className = 'admin-media-upload-item';
        item.innerHTML = `
            <div class="admin-media-upload-item-head">
                <strong>${file.name}</strong>
                <span>0%</span>
            </div>
            <div class="admin-media-upload-progress"><span></span></div>
            <div class="admin-media-upload-item-note">Wartet auf Upload ...</div>
        `;
        uploadItems?.appendChild(item);
        return item;
    };

    const setQueueProgress = (item, progress, note, state = '') => {
        const progressBar = item.querySelector('.admin-media-upload-progress > span');
        const percentLabel = item.querySelector('.admin-media-upload-item-head span');
        const noteLabel = item.querySelector('.admin-media-upload-item-note');
        item.classList.remove('is-success', 'is-error');
        if (state) {
            item.classList.add(state);
        }
        if (progressBar) {
            progressBar.style.width = `${Math.max(0, Math.min(100, progress))}%`;
        }
        if (percentLabel) {
            percentLabel.textContent = `${Math.round(progress)}%`;
        }
        if (noteLabel) {
            noteLabel.textContent = note;
        }
    };

    const uploadSingleFile = (file, queueItem, totalCount, completedCount) => new Promise((resolve) => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('name', totalCount === 1 ? (uploadNameInput?.value || '') : '');
        formData.append('alt_text', uploadAltInput?.value || '');
        formData.append('source', uploadSourceInput?.value || '');
        formData.append('tags', uploadTagsInput?.value || '');
        formData.append('max_width', uploadMaxWidthInput?.value || '');
        formData.append('max_height', uploadMaxHeightInput?.value || '');
        formData.append('xs_max_width', uploadXsMaxWidthInput?.value || '');
        formData.append('xs_max_height', uploadXsMaxHeightInput?.value || '');
        formData.append('quality', uploadQualityInput?.value || '');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

        xhr.upload.addEventListener('progress', (event) => {
            if (!event.lengthComputable) {
                return;
            }
            const percent = (event.loaded / event.total) * 100;
            setQueueProgress(queueItem, percent, 'Datei wird hochgeladen ...');
            updateOverallProgress(totalCount, completedCount, percent);
        });

        xhr.addEventListener('load', () => {
            let payload = {};
            try {
                payload = JSON.parse(xhr.responseText || '{}');
            } catch {
                payload = {};
            }

            if (xhr.status >= 200 && xhr.status < 300) {
                const item = payload.item || (Array.isArray(payload.items) ? payload.items[0] : null);
                setQueueProgress(queueItem, 100, payload.message || 'Upload abgeschlossen.', 'is-success');
                resolve({ success: true, item });
                return;
            }

            const validationErrors = payload?.errors ? Object.values(payload.errors).flat().join(' ') : '';
            setQueueProgress(queueItem, 100, validationErrors || payload?.message || 'Upload fehlgeschlagen.', 'is-error');
            resolve({ success: false, item: null });
        });

        xhr.addEventListener('error', () => {
            setQueueProgress(queueItem, 100, 'Upload fehlgeschlagen.', 'is-error');
            resolve({ success: false, item: null });
        });

        xhr.send(formData);

        if (uploadSummary) {
            uploadSummary.textContent = `${completedCount} / ${totalCount} fertig`;
        }
        updateOverallProgress(totalCount, completedCount, 0);
    });

    const applyFilter = () => {
        const needle = (searchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        cards.forEach((card) => {
            const haystack = card.dataset.mediaFilter || '';
            const cardTags = parseTagList(card.dataset.mediaTags || '');
            const matchesText = needle === '' || haystack.includes(needle);
            const matchesTags = activeTagFilters.size === 0 || [...activeTagFilters].every((tag) => cardTags.includes(tag));
            const matches = matchesText && matchesTags;
            card.hidden = !matches;
            if (matches) {
                visibleCount += 1;
            }
        });

        if (emptySearch) {
            emptySearch.hidden = visibleCount > 0 || needle === '';
        }
    };

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const card = checkbox.closest('[data-media-card]');
            if (card) {
                card.classList.toggle('is-selected', checkbox.checked);
            }
            syncBulkInputs();
        });
    });

    searchInput?.addEventListener('input', applyFilter);

    tagFilterButtons().forEach((button) => {
        wireTagFilterButton(button);
        wireTagDropzone(button);
    });

    cards.forEach((card) => wireCardDrag(card));

    tagSidebarToggle?.addEventListener('click', () => {
        if (!(tagSidebar instanceof HTMLElement)) {
            return;
        }

        const collapsed = tagSidebar.classList.toggle('is-collapsed');
        tagSidebarToggle.textContent = collapsed ? 'Ausklappen' : 'Einklappen';
        tagSidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        if (tagSidebarBody instanceof HTMLElement) {
            tagSidebarBody.hidden = collapsed;
        }
    });

    tagCreateButton?.addEventListener('click', () => {
        const value = normalizeTag(tagCreateInput instanceof HTMLInputElement ? tagCreateInput.value : '');
        if (!value) {
            return;
        }

        ensureTagFilterExists(value);
        if (tagCreateInput instanceof HTMLInputElement) {
            tagCreateInput.value = '';
        }
    });

    selectVisibleButton?.addEventListener('click', () => {
        visibleCards().forEach((card) => {
            const checkbox = card.querySelector('[data-media-checkbox]');
            if (checkbox instanceof HTMLInputElement) {
                checkbox.checked = true;
                card.classList.add('is-selected');
            }
        });
        syncBulkInputs();
    });

    clearSelectionButton?.addEventListener('click', () => {
        checkboxes.forEach((checkbox) => {
            checkbox.checked = false;
            const card = checkbox.closest('[data-media-card]');
            card?.classList.remove('is-selected');
        });
        syncBulkInputs();
    });

    uploadOpenButton?.addEventListener('click', openUploadPopover);
    uploadCloseButtons.forEach((button) => button.addEventListener('click', closeUploadPopover));

    uploadFilesInput?.addEventListener('change', () => {
        if (uploadFilesInput instanceof HTMLInputElement) {
            updateDropzoneNote(uploadFilesInput.files ? [...uploadFilesInput.files] : []);
        }
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        uploadDropzone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            uploadDropzone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        uploadDropzone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            if (eventName !== 'drop') {
                uploadDropzone.classList.remove('is-dragover');
            }
        });
    });

    uploadDropzone?.addEventListener('drop', (event) => {
        uploadDropzone.classList.remove('is-dragover');
        const transfer = event.dataTransfer;
        if (!(uploadFilesInput instanceof HTMLInputElement) || !transfer?.files?.length) {
            return;
        }

        const imageFiles = [...transfer.files].filter((file) => file.type.startsWith('image/'));
        if (imageFiles.length === 0) {
            updateDropzoneNote([]);
            if (uploadStatus) {
                uploadStatus.textContent = 'Es wurden keine gueltigen Bilddateien gezogen.';
            }
            return;
        }

        const dataTransfer = new DataTransfer();
        imageFiles.forEach((file) => dataTransfer.items.add(file));
        uploadFilesInput.files = dataTransfer.files;
        updateDropzoneNote(imageFiles);
        if (uploadStatus) {
            uploadStatus.textContent = `${imageFiles.length} Datei(en) per Drag-and-Drop vorbereitet.`;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeUploadPopover();
        }
    });

    uploadForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!(uploadFilesInput instanceof HTMLInputElement) || !uploadFilesInput.files || uploadFilesInput.files.length === 0 || isUploading) {
            if (uploadStatus) {
                uploadStatus.textContent = 'Bitte zuerst mindestens eine Datei auswaehlen.';
            }
            return;
        }

        if (!uploadUrl || !csrfToken) {
            if (uploadStatus) {
                uploadStatus.textContent = 'Upload ist nicht korrekt konfiguriert.';
            }
            return;
        }

        isUploading = true;
        if (uploadSubmit instanceof HTMLButtonElement) {
            uploadSubmit.disabled = true;
        }
        if (uploadQueue) {
            uploadQueue.hidden = false;
        }
        if (uploadItems) {
            uploadItems.innerHTML = '';
        }

        const files = [...uploadFilesInput.files];
        const queueEntries = files.map((file) => ({ file, element: createQueueItem(file) }));
        let completed = 0;
        let succeeded = 0;
        updateOverallProgress(files.length, 0, 0);

        if (uploadStatus) {
            uploadStatus.textContent = `${files.length} Datei(en) werden verarbeitet ...`;
        }

        for (const entry of queueEntries) {
            const result = await uploadSingleFile(entry.file, entry.element, files.length, completed);
            completed += 1;
            if (uploadSummary) {
                uploadSummary.textContent = `${completed} / ${files.length} fertig`;
            }
            updateOverallProgress(files.length, completed, 0);

            if (result.success && result.item) {
                succeeded += 1;
                const card = createMediaCard(result.item);
                grid.prepend(card);
                updateLibraryCount(1);
            }
        }

        if (uploadStatus) {
            uploadStatus.textContent = succeeded === files.length
                ? `${succeeded} Datei(en) erfolgreich hochgeladen.`
                : `${succeeded} von ${files.length} Datei(en) erfolgreich hochgeladen.`;
        }

        applyFilter();
        syncTagCounts();
        syncBulkInputs();
        isUploading = false;
        if (uploadSubmit instanceof HTMLButtonElement) {
            uploadSubmit.disabled = false;
        }

        if (succeeded === files.length && files.length > 0) {
            window.setTimeout(() => {
                closeUploadPopover();
            }, 250);
        }
    });

    bulkForm.addEventListener('submit', (event) => {
        const selected = checkboxes.filter((checkbox) => checkbox.checked).length;
        if (selected === 0) {
            event.preventDefault();
            return;
        }

        if (!window.confirm(selected === 1 ? '1 Medium wirklich loeschen?' : `${selected} Medien wirklich loeschen?`)) {
            event.preventDefault();
        }
    });

    applyFilter();
    syncTagCounts();
    syncBulkInputs();
});
</script>
@endsection
