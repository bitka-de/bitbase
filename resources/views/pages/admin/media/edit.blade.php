@extends('layouts.admin')

@section('meta_title', 'Medien bearbeiten | ' . config('app.name'))
@section('meta_description', 'Medien-Metadaten bearbeiten.')
@section('canonical_url', route('admin.media.edit', $media))
@section('admin_title', 'Medien bearbeiten')
@section('admin_subtitle', $media->name ?? 'Datei-Metadaten und Alternativen')

@section('content')
@php
    $focalX = old('focal_x', data_get($media->focal_point, 'x', 50));
    $focalY = old('focal_y', data_get($media->focal_point, 'y', 50));
    $crop = data_get($media->variants, 'crop', []);
    $cropEnabled = old('crop_enabled', is_array($crop) && ! empty($crop) ? '1' : '0');
@endphp
<section class="admin-media-edit-shell">
    <div class="admin-media-edit-head">
        <a href="{{ route('admin.media.index') }}" class="admin-media-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Zurück zur Bibliothek
        </a>
    </div>

    <div class="admin-media-edit-layout">
        <aside class="admin-media-edit-preview">
            <div class="admin-media-edit-preview-wrap">
                <img src="{{ $media->url }}" alt="{{ $media->alt_text ?: 'Preview' }}" class="admin-media-edit-preview-img">
            </div>

            <div class="admin-media-edit-info">
                <h3>Details</h3>
                <dl>
                    <div>
                        <dt>Auflösung Original</dt>
                        <dd>{{ $media->width }} × {{ $media->height }} px</dd>
                    </div>
                    @php
                        $xsVariant = data_get($media->variants, 'xs', []);
                    @endphp
                    @if (! empty($xsVariant))
                        <div>
                            <dt>Auflösung _xs</dt>
                            <dd>{{ data_get($xsVariant, 'width', '–') }} × {{ data_get($xsVariant, 'height', '–') }} px</dd>
                        </div>
                    @endif
                    <div>
                        <dt>Format</dt>
                        <dd>{{ strtoupper((string) str_replace('image/', '', (string) $media->mime_type)) }}</dd>
                    </div>
                    <div>
                        <dt>Dateigröße</dt>
                        <dd>
                            @php
                                $bytes = $media->file_size ?? 0;
                                $units = ['B', 'KB', 'MB', 'GB'];
                                $size = $bytes;
                                $unit = 0;
                                while ($size >= 1024 && $unit < count($units) - 1) {
                                    $size /= 1024;
                                    $unit++;
                                }
                            @endphp
                            {{ number_format($size, 1) }} {{ $units[$unit] }}
                        </dd>
                    </div>
                    <div>
                        <dt>Erstellt</dt>
                        <dd>{{ $media->created_at?->format('d.m.Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="admin-media-edit-variants">
                <h3>Varianten</h3>
                <div class="admin-media-edit-variant-links">
                    <a href="{{ $media->url }}" target="_blank" rel="noopener">
                        <span>Original</span>
                        <span class="admin-media-edit-variant-link">{{ basename($media->path) }}</span>
                    </a>
                    @php
                        $xsPath = data_get($media->variants, 'xs.path');
                    @endphp
                    @if ($xsPath)
                        <a href="{{ route('media.show', ['filename' => basename($xsPath)]) }}" target="_blank" rel="noopener">
                            <span>Mobile _xs</span>
                            <span class="admin-media-edit-variant-link">{{ basename($xsPath) }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </aside>

        <main class="admin-media-edit-form-wrap">
            <form id="admin-media-edit-form" method="POST" action="{{ route('admin.media.update', $media) }}" class="admin-media-edit-form">
                @csrf
                @method('PATCH')

                <fieldset>
                    <legend>Allgemein</legend>

                    <label class="admin-media-form-field">
                        <span>Dateiname</span>
                        <input type="text" name="name" value="{{ old('name', $media->name) }}" maxlength="255" placeholder="z.B. Produkt-Hero oder Leave empty for auto">
                    </label>

                    <label class="admin-media-form-field">
                        <span>Alt-Text</span>
                        <input type="text" name="alt_text" value="{{ old('alt_text', $media->alt_text) }}" maxlength="255" placeholder="Beschreibt den Bildinhalt fuer Barrierefreiheit">
                    </label>

                    <label class="admin-media-form-field">
                        <span>Quelle</span>
                        <input type="text" name="source" value="{{ old('source', $media->source) }}" maxlength="255" placeholder="z.B. Fotograf: Max Mustermann">
                    </label>

                    <label class="admin-media-form-field">
                        <span>Tags</span>
                        <input type="text" name="tags" value="{{ old('tags', implode(', ', is_array($media->tags) ? $media->tags : [])) }}" maxlength="320" placeholder="z.B. hero, team, produkt, kampagne">
                    </label>
                </fieldset>

                <fieldset>
                    <legend>Focal Point &amp; Crop</legend>
                    <div class="admin-media-form-actions">
                        <button type="button" class="btn btn-secondary" data-crop-open>Crop im Popup öffnen</button>
                    </div>
                    <p class="admin-media-help">Crop und Focal Point werden komplett im Popup gesteuert.</p>
                </fieldset>

                <fieldset>
                    <legend>Speichern</legend>
                    <div class="admin-media-form-actions">
                        <button type="submit" class="btn">Metadaten speichern</button>
                        <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">Abbrechen</a>
                    </div>
                </fieldset>
            </form>
        </main>
    </div>
</section>

<div class="admin-media-crop-modal" data-crop-modal hidden>
    <div class="admin-media-crop-modal-backdrop" data-crop-close></div>
    <div class="admin-media-crop-modal-dialog" role="dialog" aria-modal="true" aria-label="Bild zuschneiden">
        <div class="admin-media-crop-modal-head">
            <h3>Focal Point &amp; Crop</h3>
            <button type="button" class="btn btn-secondary" data-crop-close>Schließen</button>
        </div>
        <div class="admin-media-crop-modal-body">
            <div class="admin-media-edit-crop-stage-wrap">
                <div
                    class="admin-media-edit-crop-stage"
                    data-crop-stage
                    data-focal-x="{{ $focalX }}"
                    data-focal-y="{{ $focalY }}"
                    data-crop-x="{{ old('crop_x', data_get($crop, 'x', 0)) }}"
                    data-crop-y="{{ old('crop_y', data_get($crop, 'y', 0)) }}"
                    data-crop-width="{{ old('crop_width', data_get($crop, 'width', 100)) }}"
                    data-crop-height="{{ old('crop_height', data_get($crop, 'height', 100)) }}"
                    data-crop-enabled="{{ $cropEnabled }}"
                >
                    <img src="{{ $media->url }}" alt="{{ $media->alt_text ?: 'Preview' }}" class="admin-media-edit-preview-img" data-crop-image>
                    <div class="admin-media-edit-crop-box" data-crop-box hidden>
                        <button type="button" class="admin-media-edit-crop-handle is-nw" data-crop-handle="nw" aria-label="Resize NW"></button>
                        <button type="button" class="admin-media-edit-crop-handle is-ne" data-crop-handle="ne" aria-label="Resize NE"></button>
                        <button type="button" class="admin-media-edit-crop-handle is-sw" data-crop-handle="sw" aria-label="Resize SW"></button>
                        <button type="button" class="admin-media-edit-crop-handle is-se" data-crop-handle="se" aria-label="Resize SE"></button>
                        <button type="button" class="admin-media-edit-crop-handle is-n" data-crop-handle="n" aria-label="Resize N"></button>
                        <button type="button" class="admin-media-edit-crop-handle is-s" data-crop-handle="s" aria-label="Resize S"></button>
                        <button type="button" class="admin-media-edit-crop-handle is-w" data-crop-handle="w" aria-label="Resize W"></button>
                        <button type="button" class="admin-media-edit-crop-handle is-e" data-crop-handle="e" aria-label="Resize E"></button>
                    </div>
                    <button type="button" class="admin-media-edit-focal" data-crop-focal aria-label="Focal Point"></button>
                </div>
            </div>
            <aside class="admin-media-crop-controls" data-crop-input-grid>
                <section class="admin-media-crop-mini-previews" aria-label="Crop Vorschau">
                    <article class="admin-media-crop-mini-preview">
                        <header class="admin-media-crop-mini-preview-head">Original</header>
                        <div class="admin-media-crop-mini-preview-media" data-crop-preview-frame style="aspect-ratio: 16 / 10;">
                            <canvas class="admin-media-crop-mini-preview-canvas" data-crop-preview-canvas aria-label="Original Vorschau"></canvas>
                            <span class="admin-media-crop-mini-preview-focal" data-crop-preview-focal aria-hidden="true"></span>
                        </div>
                    </article>

                    <p class="admin-media-crop-mini-preview-status" data-crop-preview-status>Vollbild-Vorschau</p>
                </section>

                <label class="admin-media-form-checkbox">
                    <input form="admin-media-edit-form" type="checkbox" name="crop_enabled" value="1" data-crop-enabled-input {{ (string) $cropEnabled === '1' ? 'checked' : '' }}>
                    <span>Crop aktivieren</span>
                </label>

                <div class="admin-media-form-grid">
                    <label class="admin-media-form-field">
                        <span>Focal X (%)</span>
                        <input form="admin-media-edit-form" type="number" min="0" max="100" step="0.1" name="focal_x" value="{{ $focalX }}" data-focal-x-input>
                    </label>
                    <label class="admin-media-form-field">
                        <span>Focal Y (%)</span>
                        <input form="admin-media-edit-form" type="number" min="0" max="100" step="0.1" name="focal_y" value="{{ $focalY }}" data-focal-y-input>
                    </label>
                    <label class="admin-media-form-field">
                        <span>Crop X (%)</span>
                        <input form="admin-media-edit-form" type="number" min="0" max="100" step="0.1" name="crop_x" value="{{ old('crop_x', data_get($crop, 'x', 0)) }}" data-crop-x-input>
                    </label>
                    <label class="admin-media-form-field">
                        <span>Crop Y (%)</span>
                        <input form="admin-media-edit-form" type="number" min="0" max="100" step="0.1" name="crop_y" value="{{ old('crop_y', data_get($crop, 'y', 0)) }}" data-crop-y-input>
                    </label>
                    <label class="admin-media-form-field">
                        <span>Crop Breite (%)</span>
                        <input form="admin-media-edit-form" type="number" min="0.5" max="100" step="0.1" name="crop_width" value="{{ old('crop_width', data_get($crop, 'width', 100)) }}" data-crop-width-input>
                    </label>
                    <label class="admin-media-form-field">
                        <span>Crop Höhe (%)</span>
                        <input form="admin-media-edit-form" type="number" min="0.5" max="100" step="0.1" name="crop_height" value="{{ old('crop_height', data_get($crop, 'height', 100)) }}" data-crop-height-input>
                    </label>
                </div>
            </aside>
        </div>
        <div class="admin-media-crop-modal-foot">
            <div class="admin-media-crop-foot-controls">
                <label class="admin-media-crop-ratio">
                    <span>Format</span>
                    <select class="input" data-crop-ratio>
                        <option value="free" selected>Frei</option>
                        <option value="1:1">1:1</option>
                        <option value="16:9">16:9</option>
                        <option value="4:3">4:3</option>
                    </select>
                </label>
                <button type="button" class="btn btn-secondary" data-crop-draw-toggle>Crop im Bild ziehen</button>
                <button type="button" class="btn btn-secondary" data-crop-reset>Crop zurücksetzen</button>
                <button type="submit" class="btn" form="admin-media-edit-form">Im Popup speichern</button>
            </div>
            <span class="admin-media-help">Ziehe den Rahmen oder die Handles. Der Punkt ist der Focal Point.</span>
        </div>
    </div>
</div>

<style>
.admin-media-edit-shell {
    display: grid;
    gap: 1.2rem;
}

.admin-media-edit-head {
    display: flex;
    align-items: center;
}

.admin-media-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--admin-accent);
    border: 1px solid var(--admin-line);
    border-radius: 999px;
    padding: 0.35rem 0.75rem;
    background: rgba(255, 255, 255, 0.8);
    transition: border-color 120ms ease, color 120ms ease;
}

.admin-media-back:hover {
    border-color: rgba(95, 134, 255, 0.5);
    color: #4a6ad6;
}

.admin-media-back svg {
    width: 0.85rem;
    height: 0.85rem;
}

.admin-media-edit-layout {
    display: grid;
    grid-template-columns: 360px minmax(0, 1fr);
    gap: 1.2rem;
    align-items: start;
}

.admin-media-edit-preview,
.admin-media-edit-form-wrap {
    border: 1px solid var(--admin-line);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(249, 251, 255, 0.84));
    border-radius: 1rem;
    padding: 1rem;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
}

.admin-media-edit-preview-wrap {
    border-radius: 0.8rem;
    overflow: hidden;
    border: 1px solid rgba(95, 134, 255, 0.18);
    background: linear-gradient(135deg, #eef4ff, #eff9f6);
    aspect-ratio: 1;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.admin-media-crop-modal {
    position: fixed;
    inset: 0;
    z-index: 1400;
}

.admin-media-crop-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(8, 15, 28, 0.68);
    backdrop-filter: blur(6px);
}

.admin-media-crop-modal-dialog {
    position: relative;
    z-index: 1;
    width: min(96vw, 1300px);
    height: min(92vh, 960px);
    margin: 4vh auto;
    border-radius: 1rem;
    border: 1px solid var(--admin-line);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(248, 251, 255, 0.95));
    box-shadow: 0 28px 80px rgba(8, 15, 28, 0.32);
    display: grid;
    grid-template-rows: auto minmax(0, 1fr) auto;
    overflow: hidden;
}

.admin-media-crop-modal-head,
.admin-media-crop-modal-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.8rem 1rem;
    background: rgba(255, 255, 255, 0.9);
    border-bottom: 1px solid var(--admin-line);
}

.admin-media-crop-modal-foot {
    border-bottom: 0;
    border-top: 1px solid var(--admin-line);
}

.admin-media-crop-modal-head h3 {
    margin: 0;
    font-size: 0.96rem;
    color: var(--admin-ink);
}

.admin-media-crop-foot-controls {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
}

.admin-media-crop-ratio {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: var(--admin-muted);
}

.admin-media-crop-ratio select {
    min-width: 110px;
    border: 1px solid var(--admin-line);
    border-radius: 0.6rem;
    padding: 0.35rem 0.55rem;
    font: inherit;
    font-size: 0.82rem;
    background: #fff;
}

.admin-media-crop-modal-body {
    min-height: 0;
    overflow: hidden;
    padding: 1rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 1rem;
}

.admin-media-edit-crop-stage-wrap {
    min-height: 0;
    display: grid;
    place-items: center;
    overflow: auto;
    border: 1px solid var(--admin-line);
    border-radius: 0.85rem;
    background: rgba(241, 246, 255, 0.72);
}

.admin-media-crop-controls {
    display: grid;
    gap: 0.7rem;
    align-content: start;
    border: 1px solid var(--admin-line);
    border-radius: 0.85rem;
    background: rgba(255, 255, 255, 0.92);
    padding: 0.8rem;
    overflow: auto;
}

.admin-media-crop-mini-preview {
    display: grid;
    gap: 0.45rem;
}

.admin-media-crop-mini-previews {
    display: grid;
    gap: 0.6rem;
    padding-bottom: 0.3rem;
    border-bottom: 1px dashed var(--admin-line);
}

.admin-media-crop-mini-preview-head {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: var(--admin-muted);
    text-transform: uppercase;
}

.admin-media-crop-mini-preview-media {
    position: relative;
    overflow: hidden;
    border: 1px solid var(--admin-line);
    border-radius: 0.7rem;
    background: linear-gradient(135deg, #f0f5ff, #eff9f5);
    aspect-ratio: 16 / 10;
    min-height: 110px;
}

.admin-media-crop-mini-preview-media.is-xs {
    min-height: 84px;
}

.admin-media-crop-mini-preview-canvas {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
}

.admin-media-crop-mini-preview-focal {
    position: absolute;
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: rgba(40, 104, 255, 0.98);
    border: 2px solid #ffffff;
    box-shadow: 0 0 0 2px rgba(40, 104, 255, 0.2);
    transform: translate(-50%, -50%);
    left: 50%;
    top: 50%;
    pointer-events: none;
}

.admin-media-crop-mini-preview-status {
    margin: 0;
    font-size: 0.72rem;
    color: var(--admin-muted);
}

.admin-media-edit-crop-stage {
    position: relative;
    width: fit-content;
    max-width: 100%;
    max-height: 100%;
    cursor: crosshair;
    user-select: none;
}

.admin-media-edit-preview-img {
    max-width: min(100%, 1180px);
    max-height: calc(92vh - 220px);
    width: auto;
    height: auto;
    object-fit: contain;
    display: block;
}

.admin-media-edit-crop-box {
    position: absolute;
    border: 2px solid rgba(95, 134, 255, 0.95);
    background: rgba(95, 134, 255, 0.14);
    box-shadow: 0 0 0 999px rgba(12, 20, 35, 0.32);
    pointer-events: auto;
    cursor: move;
}

.admin-media-edit-crop-handle {
    position: absolute;
    width: 12px;
    height: 12px;
    border-radius: 999px;
    border: 1px solid rgba(22, 34, 56, 0.32);
    background: #ffffff;
    padding: 0;
    margin: 0;
}

.admin-media-edit-crop-handle.is-nw { top: -6px; left: -6px; cursor: nwse-resize; }
.admin-media-edit-crop-handle.is-ne { top: -6px; right: -6px; cursor: nesw-resize; }
.admin-media-edit-crop-handle.is-sw { bottom: -6px; left: -6px; cursor: nesw-resize; }
.admin-media-edit-crop-handle.is-se { bottom: -6px; right: -6px; cursor: nwse-resize; }
.admin-media-edit-crop-handle.is-n { top: -7px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
.admin-media-edit-crop-handle.is-s { bottom: -7px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
.admin-media-edit-crop-handle.is-w { left: -7px; top: 50%; transform: translateY(-50%); cursor: ew-resize; }
.admin-media-edit-crop-handle.is-e { right: -7px; top: 50%; transform: translateY(-50%); cursor: ew-resize; }

.admin-media-edit-crop-stage.is-draw-mode {
    cursor: crosshair;
}

.admin-media-edit-focal {
    position: absolute;
    width: 18px;
    height: 18px;
    border-radius: 999px;
    border: 2px solid #fff;
    background: rgba(39, 95, 255, 0.95);
    box-shadow: 0 0 0 3px rgba(39, 95, 255, 0.22);
    transform: translate(-50%, -50%);
    cursor: grab;
}

.admin-media-edit-focal:active {
    cursor: grabbing;
}

.admin-media-edit-info,
.admin-media-edit-variants {
    margin-top: 0.9rem;
    padding-top: 0.9rem;
    border-top: 1px solid var(--admin-line);
}

.admin-media-edit-info h3,
.admin-media-edit-variants h3 {
    margin: 0 0 0.55rem;
    font-size: 0.9rem;
    color: var(--admin-ink);
}

.admin-media-edit-info dl {
    display: grid;
    gap: 0.45rem;
}

.admin-media-edit-info div {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    gap: 0.6rem;
    align-items: start;
}

.admin-media-edit-info dt {
    font-size: 0.75rem;
    color: var(--admin-muted);
    font-weight: 600;
}

.admin-media-edit-info dd {
    margin: 0;
    font-size: 0.85rem;
    color: var(--admin-ink);
    word-break: break-word;
}

.admin-media-edit-variant-links {
    display: grid;
    gap: 0.5rem;
}

.admin-media-edit-variant-links a {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.5rem 0.65rem;
    border: 1px solid var(--admin-line);
    border-radius: 0.7rem;
    background: rgba(255, 255, 255, 0.85);
    font-size: 0.75rem;
    color: var(--admin-ink);
    transition: border-color 120ms ease, background 120ms ease;
}

.admin-media-edit-variant-links a:hover {
    border-color: rgba(95, 134, 255, 0.45);
    background: rgba(95, 134, 255, 0.06);
}

.admin-media-edit-variant-link {
    font-size: 0.7rem;
    color: var(--admin-muted);
    font-weight: 500;
    word-break: break-all;
}

.admin-media-edit-form {
    display: grid;
    gap: 1.2rem;
}

.admin-media-edit-form fieldset {
    margin: 0;
    padding: 0;
    border: none;
    display: grid;
    gap: 0.75rem;
}

.admin-media-edit-form legend {
    margin: 0 0 0.45rem;
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--admin-ink);
}

.admin-media-form-field {
    display: grid;
    gap: 0.3rem;
}

.admin-media-form-field > span {
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--admin-muted);
}

.admin-media-form-field input {
    width: 100%;
    border: 1px solid var(--admin-line);
    border-radius: 0.72rem;
    padding: 0.58rem 0.7rem;
    font: inherit;
    font-size: 0.9rem;
    background: rgba(255, 255, 255, 0.9);
    transition: border-color 120ms ease, box-shadow 120ms ease;
}

.admin-media-form-field input:focus {
    outline: none;
    border-color: rgba(95, 134, 255, 0.62);
    box-shadow: 0 0 0 3px rgba(95, 134, 255, 0.12);
}

.admin-media-form-actions {
    display: flex;
    gap: 0.6rem;
}

.admin-media-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.65rem;
}

.admin-media-form-checkbox {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.84rem;
    color: var(--admin-ink);
}

.admin-media-help {
    margin: 0;
    font-size: 0.78rem;
    color: var(--admin-muted);
}

body.admin-media-crop-open {
    overflow: hidden;
}

@media (max-width: 980px) {
    .admin-media-edit-layout {
        grid-template-columns: 1fr;
    }

    .admin-media-edit-preview {
        display: grid;
        grid-template-columns: 180px minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
    }

    .admin-media-edit-preview-wrap {
        grid-column: 1;
        grid-row: 1 / 4;
        aspect-ratio: 1;
        margin-bottom: 0;
    }

    .admin-media-edit-info,
    .admin-media-edit-variants {
        grid-column: 2;
        margin-top: 0;
        padding-top: 0;
        border-top: none;
        border-left: 1px solid var(--admin-line);
        padding-left: 1rem;
    }

    .admin-media-form-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 680px) {
    .admin-media-edit-preview {
        grid-template-columns: 1fr;
    }

    .admin-media-edit-preview-wrap {
        grid-column: 1;
        grid-row: auto;
    }

    .admin-media-edit-info,
    .admin-media-edit-variants {
        grid-column: 1;
        border-top: 1px solid var(--admin-line);
        border-left: none;
        padding-top: 0.9rem;
        padding-left: 0;
    }

    .admin-media-form-actions {
        flex-direction: column;
    }

    .admin-media-crop-modal-dialog {
        width: calc(100vw - 0.8rem);
        height: calc(100vh - 0.8rem);
        margin: 0.4rem;
    }

    .admin-media-crop-modal-head,
    .admin-media-crop-modal-foot {
        flex-direction: column;
        align-items: flex-start;
    }

    .admin-media-crop-modal-body {
        grid-template-columns: 1fr;
        overflow: auto;
    }

    .admin-media-crop-foot-controls {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const stage = document.querySelector('[data-crop-stage]');
    if (!(stage instanceof HTMLElement)) {
        return;
    }

    const focal = stage.querySelector('[data-crop-focal]');
    const cropBox = stage.querySelector('[data-crop-box]');
    const cropImage = stage.querySelector('[data-crop-image]');
    const focalXInput = document.querySelector('[data-focal-x-input]');
    const focalYInput = document.querySelector('[data-focal-y-input]');
    const cropEnabledInput = document.querySelector('[data-crop-enabled-input]');
    const cropXInput = document.querySelector('[data-crop-x-input]');
    const cropYInput = document.querySelector('[data-crop-y-input]');
    const cropWidthInput = document.querySelector('[data-crop-width-input]');
    const cropHeightInput = document.querySelector('[data-crop-height-input]');
    const cropDrawToggle = document.querySelector('[data-crop-draw-toggle]');
    const cropResetButton = document.querySelector('[data-crop-reset]');
    const cropInputGrid = document.querySelector('[data-crop-input-grid]');
    const cropHandles = [...stage.querySelectorAll('[data-crop-handle]')];
    const cropRatioSelect = document.querySelector('[data-crop-ratio]');
    const cropModal = document.querySelector('[data-crop-modal]');
    const cropOpenButton = document.querySelector('[data-crop-open]');
    const cropCloseButtons = [...document.querySelectorAll('[data-crop-close]')];
    const cropPreviewFrame = document.querySelector('[data-crop-preview-frame]');
    const cropPreviewCanvas = document.querySelector('[data-crop-preview-canvas]');
    const cropPreviewFocal = document.querySelector('[data-crop-preview-focal]');
    const cropPreviewStatus = document.querySelector('[data-crop-preview-status]');

    if (!(focal instanceof HTMLElement) || !(cropBox instanceof HTMLElement)) {
        return;
    }

    const openCropModal = () => {
        if (!(cropModal instanceof HTMLElement)) {
            return;
        }

        if (cropEnabledInput instanceof HTMLInputElement) {
            cropEnabledInput.checked = true;
        }

        cropModal.hidden = false;
        document.body.classList.add('admin-media-crop-open');
        render();
    };

    const closeCropModal = () => {
        if (!(cropModal instanceof HTMLElement)) {
            return;
        }

        cropModal.hidden = true;
        document.body.classList.remove('admin-media-crop-open');
        drawMode = false;
        stage.classList.remove('is-draw-mode');
        if (cropDrawToggle instanceof HTMLButtonElement) {
            cropDrawToggle.classList.remove('is-active');
            cropDrawToggle.textContent = 'Crop im Bild ziehen';
        }
    };

    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
    const clamp01 = (value) => clamp(value, 0, 1);
    const read = (input, fallback = 0) => {
        const number = Number(input?.value ?? fallback);
        return Number.isFinite(number) ? number : fallback;
    };

    const write = (input, value) => {
        if (input instanceof HTMLInputElement) {
            input.value = value.toFixed(1).replace(/\.0$/, '');
        }
    };

    let drawMode = false;
    let activeDrag = null;
    const DRAW_MIN_SIZE = 0.5;
    const DRAW_COMMIT_THRESHOLD = 1.2;

    const readCropState = () => {
        return {
            x: clamp(read(cropXInput, 0), 0, 100),
            y: clamp(read(cropYInput, 0), 0, 100),
            width: clamp(read(cropWidthInput, 100), 0.5, 100),
            height: clamp(read(cropHeightInput, 100), 0.5, 100),
        };
    };

    const writeCropState = (state) => {
        write(cropXInput, state.x);
        write(cropYInput, state.y);
        write(cropWidthInput, state.width);
        write(cropHeightInput, state.height);
    };

    const normalizeCropState = (state) => {
        let x = clamp(state.x, 0, 100);
        let y = clamp(state.y, 0, 100);
        let width = clamp(state.width, 0.5, 100);
        let height = clamp(state.height, 0.5, 100);

        if (x + width > 100) {
            x = 100 - width;
        }

        if (y + height > 100) {
            y = 100 - height;
        }

        return { x, y, width, height };
    };

    const pointerToPercent = (event) => {
        const rect = stage.getBoundingClientRect();
        return {
            x: clamp(((event.clientX - rect.left) / rect.width) * 100, 0, 100),
            y: clamp(((event.clientY - rect.top) / rect.height) * 100, 0, 100),
        };
    };

    const getRatioValue = () => {
        if (!(cropRatioSelect instanceof HTMLSelectElement)) {
            return null;
        }

        const value = cropRatioSelect.value;
        if (value === '1:1') {
            return 1;
        }

        if (value === '16:9') {
            return 16 / 9;
        }

        if (value === '4:3') {
            return 4 / 3;
        }

        return null;
    };

    const stageRatioScale = (ratio) => {
        const rect = stage.getBoundingClientRect();
        if (!ratio || rect.width <= 0 || rect.height <= 0) {
            return 1;
        }

        return rect.width / (ratio * rect.height);
    };

    const enforceRatio = (state, options = {}) => {
        const ratio = getRatioValue();
        if (!ratio) {
            return normalizeCropState(state);
        }

        const scale = stageRatioScale(ratio);
        const prev = options.previous || state;
        const anchorX = options.anchorX || 'left';
        const anchorY = options.anchorY || 'top';
        const driver = options.driver || 'width';

        let width = state.width;
        let height = state.height;

        if (driver === 'height') {
            width = Math.max(0.5, height / scale);
        } else {
            height = Math.max(0.5, width * scale);
        }

        let x = state.x;
        let y = state.y;
        const deltaW = width - prev.width;
        const deltaH = height - prev.height;

        if (anchorX === 'center') {
            x -= deltaW / 2;
        } else if (anchorX === 'right') {
            x -= deltaW;
        }

        if (anchorY === 'center') {
            y -= deltaH / 2;
        } else if (anchorY === 'bottom') {
            y -= deltaH;
        }

        let next = normalizeCropState({ x, y, width, height });

        if (next.x + next.width > 100) {
            next.width = 100 - next.x;
            next.height = Math.max(0.5, next.width * scale);
        }

        if (next.y + next.height > 100) {
            next.height = 100 - next.y;
            next.width = Math.max(0.5, next.height / scale);
        }

        return normalizeCropState(next);
    };

    const adaptCropToSelectedRatio = (state) => {
        const ratio = getRatioValue();
        if (!ratio) {
            return normalizeCropState(state);
        }

        const scale = stageRatioScale(ratio);
        if (!Number.isFinite(scale) || scale <= 0) {
            return normalizeCropState(state);
        }

        const normalized = normalizeCropState(state);
        const centerX = normalized.x + (normalized.width / 2);
        const centerY = normalized.y + (normalized.height / 2);
        const area = Math.max(0.25, normalized.width * normalized.height);

        let width = Math.sqrt(area / scale);
        let height = width * scale;

        const maxWidthByCenter = Math.max(1, 2 * Math.min(centerX, 100 - centerX));
        const maxHeightByCenter = Math.max(1, 2 * Math.min(centerY, 100 - centerY));

        if (width > maxWidthByCenter) {
            width = maxWidthByCenter;
            height = width * scale;
        }

        if (height > maxHeightByCenter) {
            height = maxHeightByCenter;
            width = height / scale;
        }

        width = clamp(width, 0.5, 100);
        height = clamp(height, 0.5, 100);

        const x = centerX - (width / 2);
        const y = centerY - (height / 2);

        return normalizeCropState({ x, y, width, height });
    };

    const render = () => {
        const focalX = clamp(read(focalXInput, 50), 0, 100);
        const focalY = clamp(read(focalYInput, 50), 0, 100);
        write(focalXInput, focalX);
        write(focalYInput, focalY);

        focal.style.left = `${focalX}%`;
        focal.style.top = `${focalY}%`;

        const enabled = cropEnabledInput instanceof HTMLInputElement ? cropEnabledInput.checked : false;

        if (cropInputGrid instanceof HTMLElement) {
            cropInputGrid.style.opacity = enabled ? '1' : '0.55';
        }

        const imageWidth = cropImage instanceof HTMLImageElement && cropImage.naturalWidth > 0 ? cropImage.naturalWidth : 1;
        const imageHeight = cropImage instanceof HTMLImageElement && cropImage.naturalHeight > 0 ? cropImage.naturalHeight : 1;
        const imageAspect = imageWidth / imageHeight;

        const drawCanvasPreview = (canvas, frame, state, mode) => {
            if (!(canvas instanceof HTMLCanvasElement) || !(frame instanceof HTMLElement) || !(cropImage instanceof HTMLImageElement)) {
                return null;
            }

            const rect = frame.getBoundingClientRect();
            const frameWidth = Math.max(1, Math.round(rect.width));
            const frameHeight = Math.max(1, Math.round(rect.height));
            if (frameWidth < 1 || frameHeight < 1) {
                return null;
            }

            canvas.width = frameWidth;
            canvas.height = frameHeight;

            const ctx = canvas.getContext('2d');
            if (!ctx) {
                return null;
            }

            const sourceX = (state.x / 100) * imageWidth;
            const sourceY = (state.y / 100) * imageHeight;
            const sourceW = Math.max(1, (state.width / 100) * imageWidth);
            const sourceH = Math.max(1, (state.height / 100) * imageHeight);

            let drawW = frameWidth;
            let drawH = frameHeight;
            let drawX = 0;
            let drawY = 0;

            if (mode === 'contain') {
                const scale = Math.min(frameWidth / sourceW, frameHeight / sourceH);
                drawW = sourceW * scale;
                drawH = sourceH * scale;
                drawX = (frameWidth - drawW) / 2;
                drawY = (frameHeight - drawH) / 2;
            }

            ctx.clearRect(0, 0, frameWidth, frameHeight);
            ctx.drawImage(cropImage, sourceX, sourceY, sourceW, sourceH, drawX, drawY, drawW, drawH);

            return {
                frameWidth,
                frameHeight,
                drawX,
                drawY,
                drawW,
                drawH,
            };
        };

        const updatePreviewFocal = (focalElement, layout, state) => {
            if (!(focalElement instanceof HTMLElement) || !layout) {
                return;
            }

            const relativeX = clamp01((focalX - state.x) / state.width);
            const relativeY = clamp01((focalY - state.y) / state.height);
            const markerX = layout.drawX + (relativeX * layout.drawW);
            const markerY = layout.drawY + (relativeY * layout.drawH);

            focalElement.style.left = `${(markerX / layout.frameWidth) * 100}%`;
            focalElement.style.top = `${(markerY / layout.frameHeight) * 100}%`;
        };

        if (!enabled) {
            cropBox.hidden = true;

            if (cropPreviewFrame instanceof HTMLElement) {
                cropPreviewFrame.style.aspectRatio = `${imageAspect}`;
            }

            const fullState = { x: 0, y: 0, width: 100, height: 100 };
            const originalLayout = drawCanvasPreview(cropPreviewCanvas, cropPreviewFrame, fullState, 'cover');
            updatePreviewFocal(cropPreviewFocal, originalLayout, fullState);

            if (cropPreviewStatus instanceof HTMLElement) {
                cropPreviewStatus.textContent = 'Vollbild-Vorschau';
            }

            return;
        }

        const state = normalizeCropState(readCropState());
        const cropX = state.x;
        const cropY = state.y;
        const cropWidth = state.width;
        const cropHeight = state.height;

        write(cropXInput, cropX);
        write(cropYInput, cropY);
        write(cropWidthInput, cropWidth);
        write(cropHeightInput, cropHeight);

        cropBox.hidden = false;
        cropBox.style.left = `${cropX}%`;
        cropBox.style.top = `${cropY}%`;
        cropBox.style.width = `${cropWidth}%`;
        cropBox.style.height = `${cropHeight}%`;

        const cropAspect = Math.max(0.05, (cropWidth / cropHeight) * imageAspect);

        if (cropPreviewFrame instanceof HTMLElement) {
            cropPreviewFrame.style.aspectRatio = `${cropAspect}`;
        }

        const originalLayout = drawCanvasPreview(cropPreviewCanvas, cropPreviewFrame, state, 'cover');
        updatePreviewFocal(cropPreviewFocal, originalLayout, state);

        if (cropPreviewStatus instanceof HTMLElement) {
            cropPreviewStatus.textContent = `Crop ${cropWidth.toFixed(1)}% x ${cropHeight.toFixed(1)}%`;
        }
    };

    const updateFocalFromPointer = (event) => {
        const point = pointerToPercent(event);
        const x = point.x;
        const y = point.y;
        write(focalXInput, x);
        write(focalYInput, y);
        render();
    };

    const beginDrag = (event, mode, handle = null) => {
        const enabled = cropEnabledInput instanceof HTMLInputElement ? cropEnabledInput.checked : false;
        if (mode !== 'focal' && !enabled) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const point = pointerToPercent(event);
        activeDrag = {
            mode,
            handle,
            startPointerX: point.x,
            startPointerY: point.y,
            startCrop: readCropState(),
            hasMoved: false,
        };

        stage.setPointerCapture(event.pointerId);
    };

    const buildDrawCropState = (startX, startY, pointX, pointY) => {
        const dx = pointX - startX;
        const dy = pointY - startY;
        const dirX = dx >= 0 ? 1 : -1;
        const dirY = dy >= 0 ? 1 : -1;

        const ratio = getRatioValue();
        if (!ratio) {
            const width = Math.max(DRAW_MIN_SIZE, Math.abs(dx));
            const height = Math.max(DRAW_MIN_SIZE, Math.abs(dy));
            const x = dirX > 0 ? startX : startX - width;
            const y = dirY > 0 ? startY : startY - height;
            return normalizeCropState({ x, y, width, height });
        }

        const scale = stageRatioScale(ratio);
        if (!Number.isFinite(scale) || scale <= 0) {
            return normalizeCropState({ x: startX, y: startY, width: DRAW_MIN_SIZE, height: DRAW_MIN_SIZE });
        }

        const widthByX = Math.abs(dx);
        const widthByY = Math.abs(dy) / scale;
        let width = widthByX >= widthByY ? widthByX : widthByY;

        const maxWidthByX = dirX > 0 ? (100 - startX) : startX;
        const maxWidthByY = (dirY > 0 ? (100 - startY) : startY) / scale;
        const maxWidth = Math.max(DRAW_MIN_SIZE, Math.min(maxWidthByX, maxWidthByY));
        width = clamp(width, DRAW_MIN_SIZE, maxWidth);

        const height = Math.max(DRAW_MIN_SIZE, width * scale);
        const x = dirX > 0 ? startX : startX - width;
        const y = dirY > 0 ? startY : startY - height;

        return normalizeCropState({ x, y, width, height });
    };

    const applyResizeHandle = (state, handle, dx, dy) => {
        let { x, y, width, height } = state;
        const right = x + width;
        const bottom = y + height;

        if (handle.includes('e')) {
            width = clamp(width + dx, 0.5, 100 - x);
        }

        if (handle.includes('s')) {
            height = clamp(height + dy, 0.5, 100 - y);
        }

        if (handle.includes('w')) {
            const nextX = clamp(x + dx, 0, right - 0.5);
            x = nextX;
            width = clamp(right - x, 0.5, 100 - x);
        }

        if (handle.includes('n')) {
            const nextY = clamp(y + dy, 0, bottom - 0.5);
            y = nextY;
            height = clamp(bottom - y, 0.5, 100 - y);
        }

        const normalized = normalizeCropState({ x, y, width, height });
        const ratio = getRatioValue();
        if (!ratio) {
            return normalized;
        }

        if (handle === 'n' || handle === 's') {
            return enforceRatio(normalized, {
                previous: state,
                driver: 'height',
                anchorX: 'center',
                anchorY: handle === 'n' ? 'bottom' : 'top',
            });
        }

        if (handle === 'e' || handle === 'w') {
            return enforceRatio(normalized, {
                previous: state,
                driver: 'width',
                anchorX: handle === 'w' ? 'right' : 'left',
                anchorY: 'center',
            });
        }

        return enforceRatio(normalized, {
            previous: state,
            driver: 'width',
            anchorX: handle.includes('w') ? 'right' : 'left',
            anchorY: handle.includes('n') ? 'bottom' : 'top',
        });
    };

    const onPointerMove = (event) => {
        if (!activeDrag) {
            return;
        }

        const point = pointerToPercent(event);
        const dx = point.x - activeDrag.startPointerX;
        const dy = point.y - activeDrag.startPointerY;

        if (activeDrag.mode === 'focal') {
            updateFocalFromPointer(event);
            return;
        }

        if (activeDrag.mode === 'draw') {
            const moveX = Math.abs(point.x - activeDrag.startPointerX);
            const moveY = Math.abs(point.y - activeDrag.startPointerY);
            if (moveX > 0.05 || moveY > 0.05) {
                activeDrag.hasMoved = true;
            }

            const next = buildDrawCropState(activeDrag.startPointerX, activeDrag.startPointerY, point.x, point.y);

            writeCropState(next);
            render();
            return;
        }

        if (activeDrag.mode === 'move') {
            const start = activeDrag.startCrop;
            const next = normalizeCropState({
                x: start.x + dx,
                y: start.y + dy,
                width: start.width,
                height: start.height,
            });
            writeCropState(next);
            render();
            return;
        }

        if (activeDrag.mode === 'resize') {
            const start = activeDrag.startCrop;
            const next = applyResizeHandle(start, activeDrag.handle || 'se', dx, dy);
            writeCropState(next);
            render();
        }
    };

    const endPointerDrag = (event) => {
        if (!activeDrag) {
            return;
        }

        if (activeDrag.mode === 'draw') {
            const current = readCropState();
            const drawDelta = Math.max(
                Math.abs(current.width - activeDrag.startCrop.width),
                Math.abs(current.height - activeDrag.startCrop.height)
            );

            if (!activeDrag.hasMoved || drawDelta < DRAW_COMMIT_THRESHOLD) {
                writeCropState(activeDrag.startCrop);
                render();
            }
        }

        activeDrag = null;
        if (stage.hasPointerCapture(event.pointerId)) {
            stage.releasePointerCapture(event.pointerId);
        }
    };

    focal.addEventListener('pointerdown', (event) => {
        beginDrag(event, 'focal');
    });

    cropBox.addEventListener('pointerdown', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.dataset.cropHandle) {
            beginDrag(event, 'resize', target.dataset.cropHandle);
            return;
        }

        beginDrag(event, 'move');
    });

    stage.addEventListener('pointerdown', (event) => {
        if (activeDrag) {
            return;
        }

        const enabled = cropEnabledInput instanceof HTMLInputElement ? cropEnabledInput.checked : false;
        if (enabled && drawMode) {
            beginDrag(event, 'draw');
            return;
        }

        updateFocalFromPointer(event);
    });

    stage.addEventListener('pointermove', onPointerMove);
    stage.addEventListener('pointerup', endPointerDrag);
    stage.addEventListener('pointercancel', endPointerDrag);

    [focalXInput, focalYInput, cropXInput, cropYInput, cropWidthInput, cropHeightInput].forEach((input) => {
        if (input instanceof HTMLInputElement) {
            input.addEventListener('input', render);
        }
    });

    if (cropRatioSelect instanceof HTMLSelectElement) {
        cropRatioSelect.addEventListener('change', () => {
            const ratio = getRatioValue();
            if (!ratio) {
                render();
                return;
            }

            const next = adaptCropToSelectedRatio(readCropState());
            writeCropState(next);
            render();
        });
    }

    if (cropEnabledInput instanceof HTMLInputElement) {
        cropEnabledInput.addEventListener('change', render);
    }

    if (cropDrawToggle instanceof HTMLButtonElement) {
        cropDrawToggle.addEventListener('click', () => {
            drawMode = !drawMode;
            stage.classList.toggle('is-draw-mode', drawMode);
            cropDrawToggle.classList.toggle('is-active', drawMode);
            cropDrawToggle.textContent = drawMode ? 'Crop zeichnen aktiv' : 'Crop im Bild ziehen';
        });
    }

    if (cropResetButton instanceof HTMLButtonElement) {
        cropResetButton.addEventListener('click', () => {
            write(cropXInput, 0);
            write(cropYInput, 0);
            write(cropWidthInput, 100);
            write(cropHeightInput, 100);

            if (cropEnabledInput instanceof HTMLInputElement) {
                cropEnabledInput.checked = false;
            }

            drawMode = false;
            stage.classList.remove('is-draw-mode');
            if (cropDrawToggle instanceof HTMLButtonElement) {
                cropDrawToggle.classList.remove('is-active');
                cropDrawToggle.textContent = 'Crop im Bild ziehen';
            }

            render();
        });
    }

    if (cropOpenButton instanceof HTMLButtonElement) {
        cropOpenButton.addEventListener('click', openCropModal);
    }

    cropCloseButtons.forEach((button) => {
        button.addEventListener('click', closeCropModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || !(cropModal instanceof HTMLElement) || cropModal.hidden) {
            return;
        }

        closeCropModal();
    });

    if (cropImage instanceof HTMLImageElement && !cropImage.complete) {
        cropImage.addEventListener('load', () => {
            render();
        }, { once: true });
    }

    render();
});
</script>
@endsection
