@php
$component = $component ?? null;
$value = static function (string $key, mixed $fallback = '') use ($component) {
    return old($key, $component?->{$key} ?? $fallback);
};

$rawTags = old('tags', $component?->tags ?? []);
$tagsValue = is_array($rawTags) ? implode(', ', $rawTags) : (string) $rawTags;
@endphp

@if ($errors->any())
    <div class="alert alert-danger" role="alert" style="margin-bottom: 0.8rem;">
        Bitte pruefe die Eingaben und versuche es erneut.
    </div>
@endif

<div
    class="cms-shell"
    data-component-form
    data-frontend-css="{{ Vite::asset('resources/css/public.css') }}"
    data-app-name="{{ config('app.name') }}"
    data-preview-year="{{ now()->format('Y') }}"
    data-csrf="{{ csrf_token() }}"
>
    <div class="cms-pane" style="padding-top: 0; border-top: none; gap: 0.8rem;">
        <div class="cms-pane-head">
            <h3 class="admin-section-title">Komponente</h3>
            <span class="cms-section-kicker">Reusable</span>
        </div>

        <div class="cms-grid cms-grid-title">
            <div>
                <label for="title" class="label">Titel</label>
                <input id="title" name="title" type="text" class="input" value="{{ $value('title') }}" required>
            </div>
            <div>
                <label for="name" class="label">Name fuer Slash Insert</label>
                <input id="name" name="name" type="text" class="input" value="{{ $value('name') }}" required>
                <p class="help-text">Verwendung im Editor mit /{{ $value('name', 'name') }}</p>
            </div>
        </div>

        <div>
            <label for="description" class="label">Kurzbeschreibung</label>
            <textarea id="description" name="description" class="textarea" rows="2">{{ $value('description') }}</textarea>
        </div>

        <div>
            <label for="tags" class="label">Tags</label>
            <input id="tags" name="tags" type="text" class="input" value="{{ $tagsValue }}" placeholder="z.B. hero, marketing, conversion">
            <p class="help-text">Mehrere Tags mit Komma trennen. Beispiel: hero, faq, ecommerce</p>
            @error('tags')
                <p class="help-text" style="color: var(--danger);">{{ $message }}</p>
            @enderror
            @error('tags.*')
                <p class="help-text" style="color: var(--danger);">{{ $message }}</p>
            @enderror
        </div>

        <div class="cms-editor-surface" data-component-editor-surface>
            <div class="cms-editor-head">
                <div class="cms-editor-title-group">
                    <label for="component-html-source" class="label">Komponenten Code</label>
                </div>
                <div class="cms-editor-controls">
                    <div class="cms-editor-switch" role="tablist" aria-label="Komponenten Editor Modus">
                        <button type="button" class="cms-editor-btn is-active" data-component-mode="html" aria-label="HTML Modus" title="HTML">HTML</button>
                        <button type="button" class="cms-editor-btn" data-component-mode="css" aria-label="CSS Modus" title="CSS">CSS</button>
                        <button type="button" class="cms-editor-btn" data-component-mode="js" aria-label="JS Modus" title="JS">JS</button>
                        <button type="button" class="cms-editor-btn" data-component-mode="wysiwyg" aria-label="Preview Modus" title="Preview">Preview</button>
                    </div>

                    <button
                        type="button"
                        class="cms-editor-btn cms-icon-btn"
                        data-component-fullscreen="false"
                        aria-label="Editor im Vollbild anzeigen"
                        title="Vollbild"
                    >
                        <svg class="cms-icon cms-icon-open" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M8 4H4v4M16 4h4v4M8 20H4v-4M16 20h4v-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg class="cms-icon cms-icon-close" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M9 4H4v5M15 4h5v5M9 20H4v-5M15 20h5v-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>

            <textarea id="component-html-source" name="content" class="textarea cms-editor-area" rows="16" hidden required>{{ $value('content') }}</textarea>
            <div id="component-html-editor" class="cms-editor-area cms-code-editor"></div>

            <textarea id="component-css-source" name="css" class="textarea cms-editor-area" rows="12" hidden>{{ $value('css') }}</textarea>
            <div id="component-css-editor" class="cms-editor-area cms-code-editor" hidden></div>

            <textarea id="component-js-source" name="js" class="textarea cms-editor-area" rows="12" hidden>{{ $value('js') }}</textarea>
            <div id="component-js-editor" class="cms-editor-area cms-code-editor" hidden></div>

            <iframe id="component-wysiwyg" class="cms-editor-area cms-wysiwyg-frame" hidden title="Komponenten Preview"></iframe>

            @error('content')
                <p class="help-text" style="color: var(--danger);">{{ $message }}</p>
            @enderror

            @error('css')
                <p class="help-text" style="color: var(--danger);">{{ $message }}</p>
            @enderror

            @error('js')
                <p class="help-text" style="color: var(--danger);">{{ $message }}</p>
            @enderror

            <p class="help-text">In Preview werden HTML, CSS und JS sofort zusammen gerendert.</p>
        </div>

        <div class="cms-sticky-save" role="region" aria-label="Speichern">
            <button type="submit" class="btn btn-primary" data-save-button>{{ $component ? 'Komponente speichern' : 'Komponente erstellen' }}</button>
        </div>
    </div>
</div>