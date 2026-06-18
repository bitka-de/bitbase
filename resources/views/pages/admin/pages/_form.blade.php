@php
$page = $page ?? null;
$isEdit = $page !== null;
$starterData = $starterData ?? [];
$value = static function (string $key, mixed $fallback = '') use ($page, $starterData) {
    $pageValue = $page?->{$key};

    return old($key, $pageValue ?? ($starterData[$key] ?? $fallback));
};
$schemaDataValue = old('schema_data', isset($page->schema_data) ? json_encode($page->schema_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '');
$redirectOldUrlsValue = old('redirect_old_urls', isset($page->redirect_old_urls) ? json_encode($page->redirect_old_urls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '');
$contentValue = $value('content', '');

$toDisplayString = static function (mixed $input): string {
    if ($input instanceof \BackedEnum) {
        return (string) $input->value;
    }

    if ($input instanceof \UnitEnum) {
        return $input->name;
    }

    return (string) $input;
};

$localeDisplay = $toDisplayString($value('locale', app()->getLocale()));
$statusDisplay = $toDisplayString($value('status', 'draft'));
@endphp

@if ($errors->any())
    <div class="alert alert-danger" role="alert" style="margin-bottom: 0.8rem;">
        Bitte pruefe die Eingaben und versuche es erneut.
    </div>
@endif

<div
    class="cms-shell"
    data-cms-form
    data-frontend-css="{{ Vite::asset('resources/css/public.css') }}"
    data-app-name="{{ config('app.name') }}"
    data-preview-year="{{ now()->format('Y') }}"
    data-csrf="{{ csrf_token() }}"
>
    <script type="application/json" data-cms-components>@json(($contentComponents ?? collect())->values())</script>

    <div class="cms-page-headline cms-page-headline-compact">
        <div>
            <h2 class="cms-page-title" data-cms-page-title>{{ $value('title', 'Neue Seite') }}</h2>
            <p class="help-text">Titel und URL werden zentral gepflegt.</p>
        </div>
        <div class="cms-title-meta">
            <span class="cms-mini-pill">{{ strtoupper($localeDisplay) }}</span>
            <span class="cms-mini-pill">{{ strtoupper($statusDisplay) }}</span>
        </div>
    </div>

    <div class="cms-tabbar" role="tablist" aria-label="CMS Tabs">
        <button type="button" class="cms-tab is-active" data-cms-tab-btn="content" role="tab" aria-selected="true">Inhalt</button>
        <button type="button" class="cms-tab" data-cms-tab-btn="seo" role="tab" aria-selected="false">SEO</button>
        <button type="button" class="cms-tab" data-cms-tab-btn="social" role="tab" aria-selected="false">Social</button>
        <button type="button" class="cms-tab" data-cms-tab-btn="schema" role="tab" aria-selected="false">Schema</button>
        <button type="button" class="cms-tab" data-cms-tab-btn="media" role="tab" aria-selected="false">Medien</button>
        <button type="button" class="cms-tab" data-cms-tab-btn="publish" role="tab" aria-selected="false">Veroeffentlichung</button>
        <button type="button" class="cms-tab" data-cms-tab-btn="redirects" role="tab" aria-selected="false">Weiterleitungen</button>
        <button type="button" class="cms-tab" data-cms-tab-btn="preview" role="tab" aria-selected="false">Vorschau</button>
        <button type="button" class="cms-tab" data-cms-tab-btn="seo-check" role="tab" aria-selected="false">SEO-Check</button>
        @if($isEdit && isset($revisions) && $revisions->isNotEmpty())
            <button type="button" class="cms-tab cms-tab-history" data-cms-tab-btn="revisions" role="tab" aria-selected="false">
                <svg viewBox="0 0 24 24" aria-hidden="true" width="13" height="13" style="flex-shrink:0">
                    <path d="M12 8v4l3 3M12 3a9 9 0 1 0 0 18A9 9 0 0 0 12 3z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Versionen
                <span class="cms-tab-badge">{{ $revisions->count() }}</span>
            </button>
        @endif
    </div>

    <section class="cms-pane" data-cms-tab-pane="content">
        <div class="cms-pane-head">
            <h3 class="admin-section-title">Inhalt</h3>
            <span class="cms-section-kicker">Core</span>
        </div>

        <div class="cms-core-zone">
            <div class="cms-grid cms-grid-title">
                <div>
                    <label for="title" class="label">Titel</label>
                    <input id="title" name="title" type="text" class="input" value="{{ $value('title', '') }}" required>
                    @error('title')
                        <p class="help-text" style="color: var(--danger);">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="slug" class="label">Slug</label>
                    <input id="slug" name="slug" type="text" class="input" value="{{ $value('slug', '') }}">
                    <p class="help-text">Wird automatisch vorgeschlagen.</p>
                </div>
            </div>

            <div class="cms-grid cms-grid-content-top">
                <div>
                    <label for="excerpt" class="label">Kurzbeschreibung</label>
                    <textarea id="excerpt" name="excerpt" class="textarea" rows="3">{{ $value('excerpt', '') }}</textarea>
                </div>

                <div class="cms-quick-hints">
                    <div class="cms-quick-hint">
                        <strong>Smart Start</strong>
                        <span>Slug und Seitenkopf aktualisieren sich direkt aus dem Titel.</span>
                    </div>
                    <div class="cms-quick-hint">
                        <strong>Flow</strong>
                        <span>Schreibe zuerst Inhalt, optimiere danach SEO und Publishing.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="cms-editor-surface" data-editor-surface>
            <div class="cms-editor-head">
                <div class="cms-editor-title-group">
                    <label for="content-source" class="label">Inhalt</label>
                </div>
                <div class="cms-editor-controls">
                    <div class="cms-editor-switch" role="tablist" aria-label="Editor Modus">
                        <button type="button" class="cms-editor-btn cms-editor-btn-icon" data-editor-mode="html" aria-label="HTML Modus" title="HTML">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M9 8l-4 4 4 4M15 8l4 4-4 4M13 6l-2 12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button type="button" class="cms-editor-btn cms-editor-btn-icon is-active" data-editor-mode="wysiwyg" aria-label="WYSIWYG Modus" title="WYSIWYG">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M5 6h14M5 12h9M5 18h12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </button>
                        <button type="button" class="cms-editor-btn cms-editor-btn-icon" data-editor-mode="preview" aria-label="Vorschau Modus" title="Vorschau">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z" fill="none" stroke="currentColor" stroke-width="1.8" />
                                <circle cx="12" cy="12" r="2.5" fill="none" stroke="currentColor" stroke-width="1.8" />
                            </svg>
                        </button>
                    </div>

                    <div class="cms-editor-switch cms-editor-viewport-switch" role="tablist" aria-label="Editor Geraetansicht">
                        <button type="button" class="cms-editor-btn cms-editor-btn-icon is-active" data-editor-viewport-btn="desktop" role="tab" aria-selected="true" aria-label="Desktop Ansicht" title="Desktop">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M4 5h16v10H4zM9 19h6M12 15v4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button type="button" class="cms-editor-btn cms-editor-btn-icon" data-editor-viewport-btn="mobile" role="tab" aria-selected="false" aria-label="Mobile Ansicht" title="Mobile">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <rect x="8" y="3" width="8" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="1.8" />
                                <circle cx="12" cy="17" r="0.9" fill="currentColor" />
                            </svg>
                        </button>
                    </div>

                    <div class="cms-editor-layout-switch">
                        <label for="template" class="cms-editor-layout-label">Layout</label>
                        <select id="template" name="template" class="select cms-editor-layout-select">
                            @foreach (($templates ?? ['default' => 'Standard']) as $templateValue => $templateLabel)
                                <option value="{{ $templateValue }}" @selected($value('template', 'default') === $templateValue)>{{ $templateLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button
                        type="button"
                        class="cms-editor-btn cms-icon-btn"
                        data-editor-fullscreen="false"
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

                <textarea id="content-source" name="content" class="textarea cms-editor-area" rows="14" hidden>{{ $contentValue }}</textarea>
                <div id="content-code-editor" class="cms-editor-area cms-code-editor"></div>
            <iframe id="content-wysiwyg" class="cms-editor-area cms-wysiwyg-frame" hidden title="WYSIWYG Editor"></iframe>
            <iframe id="content-preview" class="cms-editor-area cms-preview-frame" hidden title="Seitenvorschau"></iframe>

            @error('content')
                <p class="help-text" style="color: var(--danger);">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <section class="cms-pane" data-cms-tab-pane="seo" hidden>
        <div class="cms-pane-head">
            <h3 class="admin-section-title">SEO</h3>
            <span class="cms-section-kicker">Search</span>
        </div>
        <div>
            <label for="seo_title" class="label">SEO Title</label>
            <input id="seo_title" name="seo_title" type="text" class="input" value="{{ $value('seo_title', '') }}">
        </div>
        <div>
            <label for="meta_description" class="label">Meta Description</label>
            <textarea id="meta_description" name="meta_description" class="textarea" rows="3">{{ $value('meta_description', '') }}</textarea>
        </div>
        <div class="cms-grid">
            <div>
                <label for="canonical_url" class="label">Canonical URL (optional)</label>
                <input id="canonical_url" name="canonical_url" type="url" class="input" value="{{ old('canonical_url', $page->canonical_url ?? '') }}">
            </div>
            <div>
                <label for="locale" class="label">Sprache / Locale</label>
                <input id="locale" name="locale" type="text" class="input" value="{{ $value('locale', app()->getLocale()) }}" required>
            </div>
        </div>
        <div class="cms-grid">
            <div>
                <label for="robots_index" class="label">Robots Index</label>
                <select id="robots_index" name="robots_index" class="select">
                    <option value="index" @selected($value('robots_index', 'index') === 'index')>index</option>
                    <option value="noindex" @selected($value('robots_index', 'index') === 'noindex')>noindex</option>
                </select>
            </div>
            <div>
                <label for="robots_follow" class="label">Robots Follow</label>
                <select id="robots_follow" name="robots_follow" class="select">
                    <option value="follow" @selected($value('robots_follow', 'follow') === 'follow')>follow</option>
                    <option value="nofollow" @selected($value('robots_follow', 'follow') === 'nofollow')>nofollow</option>
                </select>
            </div>
        </div>
        <div class="cms-serp-preview" data-serp-preview data-serp-device="desktop">
            <div class="cms-serp-head">
                <strong>Google SERP Vorschau</strong>
                <div class="cms-serp-switch" role="tablist" aria-label="SERP Geraet umschalten">
                    <button type="button" class="cms-serp-switch-btn is-active" data-serp-device-btn="desktop" role="tab" aria-selected="true">Desktop</button>
                    <button type="button" class="cms-serp-switch-btn" data-serp-device-btn="mobile" role="tab" aria-selected="false">Mobile</button>
                </div>
            </div>

            <div class="cms-serp-stage" data-serp-stage>
                <article class="cms-serp-result" aria-label="Google Suchergebnis Vorschau">
                    <div class="cms-serp-source-row">
                        <span class="cms-serp-favicon" aria-hidden="true"></span>
                        <div class="cms-serp-source-meta">
                            <p class="cms-serp-site" data-serp-site>{{ parse_url(url('/'), PHP_URL_HOST) }}</p>
                            <p class="cms-serp-url" data-serp-url>{{ old('canonical_url', $page->canonical_url ?? url('/'.$value('slug', 'seite'))) }}</p>
                        </div>
                    </div>
                    <p class="cms-serp-title" data-serp-title>{{ $value('seo_title', $value('title', 'Seitentitel')) ?: 'Seitentitel' }}</p>
                    <p class="cms-serp-desc" data-serp-desc>{{ $value('meta_description', $value('excerpt', 'Meta Description Vorschau')) ?: 'Meta Description Vorschau' }}</p>
                </article>
            </div>

            <p class="help-text">Titel und Beschreibung werden live wie in der Google-Ergebnisdarstellung gekuerzt.</p>
        </div>
    </section>

    <section class="cms-pane" data-cms-tab-pane="social" hidden>
        <div class="cms-pane-head">
            <h3 class="admin-section-title">Social</h3>
            <span class="cms-section-kicker">Sharing</span>
        </div>
        <div class="cms-grid">
            <div>
                <label for="og_title" class="label">OG Title</label>
                <input id="og_title" name="og_title" type="text" class="input" value="{{ old('og_title', $page->og_title ?? '') }}">
            </div>
            <div>
                <label for="twitter_title" class="label">Twitter Title</label>
                <input id="twitter_title" name="twitter_title" type="text" class="input" value="{{ old('twitter_title', $page->twitter_title ?? '') }}">
            </div>
        </div>
        <div class="cms-grid">
            <div>
                <label for="og_description" class="label">OG Description</label>
                <textarea id="og_description" name="og_description" class="textarea" rows="3">{{ old('og_description', $page->og_description ?? '') }}</textarea>
            </div>
            <div>
                <label for="twitter_description" class="label">Twitter Description</label>
                <textarea id="twitter_description" name="twitter_description" class="textarea" rows="3">{{ old('twitter_description', $page->twitter_description ?? '') }}</textarea>
            </div>
        </div>
        <div class="cms-grid">
            <div>
                <label for="og_image_id" class="label">OG Image ID</label>
                <input id="og_image_id" name="og_image_id" type="number" class="input" value="{{ old('og_image_id', $page->og_image_id ?? '') }}">
            </div>
            <div>
                <label for="twitter_image_id" class="label">Twitter Image ID</label>
                <input id="twitter_image_id" name="twitter_image_id" type="number" class="input" value="{{ old('twitter_image_id', $page->twitter_image_id ?? '') }}">
            </div>
        </div>
    </section>

    <section class="cms-pane" data-cms-tab-pane="schema" hidden>
        <div class="cms-pane-head">
            <h3 class="admin-section-title">Schema</h3>
            <span class="cms-section-kicker">Data</span>
        </div>
        <div>
            <label for="schema_type" class="label">Schema Typ</label>
            <select id="schema_type" name="schema_type" class="select">
                <option value="">Auto / leer</option>
                @foreach (['WebPage','Article','BlogPosting','Product','LocalBusiness','FAQPage','BreadcrumbList','VideoObject','Event','JobPosting'] as $schemaType)
                    <option value="{{ $schemaType }}" @selected(old('schema_type', $page->schema_type ?? '') === $schemaType)>{{ $schemaType }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="schema_data_text" class="label">Schema JSON (optional)</label>
            <textarea id="schema_data_text" name="schema_data_text" class="textarea" rows="8">{{ $schemaDataValue }}</textarea>
        </div>
    </section>

    <section class="cms-pane" data-cms-tab-pane="media" hidden>
        <div class="cms-pane-head">
            <h3 class="admin-section-title">Medien</h3>
            <span class="cms-section-kicker">Assets</span>
        </div>
        <p class="help-text">Medienfelder fuer SEO/Social werden aktuell ueber OG/Twitter Bild IDs gepflegt.</p>
    </section>

    <section class="cms-pane" data-cms-tab-pane="publish" hidden>
        <div class="cms-pane-head">
            <h3 class="admin-section-title">Veroeffentlichung</h3>
            <span class="cms-section-kicker">Control</span>
        </div>
        <div class="cms-grid">
            <div>
                <label for="status" class="label">Status</label>
                <select id="status" name="status" class="select" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($value('status', 'draft') === $status->value)>{{ $status->value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="published_at" class="label">Published At</label>
                <input id="published_at" name="published_at" type="datetime-local" class="input" value="{{ old('published_at', isset($page->published_at) && $page->published_at ? $page->published_at->format('Y-m-d\\TH:i') : '') }}">
            </div>
        </div>
        <div class="cms-grid cms-grid-3">
            <div>
                <label for="parent_id" class="label">Parent ID</label>
                <input id="parent_id" name="parent_id" type="number" class="input" value="{{ old('parent_id', $page->parent_id ?? '') }}">
            </div>
            <div>
                <label for="sort_order" class="label">Sortierung</label>
                <input id="sort_order" name="sort_order" type="number" class="input" value="{{ old('sort_order', $page->sort_order ?? 0) }}">
            </div>
        </div>
        <div class="cms-grid cms-grid-3">
            <div>
                <label for="reviewer_id" class="label">Reviewer ID</label>
                <input id="reviewer_id" name="reviewer_id" type="number" class="input" value="{{ old('reviewer_id', $page->reviewer_id ?? '') }}">
            </div>
            <div>
                <label for="sitemap_priority" class="label">Sitemap Priority</label>
                <input id="sitemap_priority" name="sitemap_priority" type="number" step="0.1" min="0" max="1" class="input" value="{{ $value('sitemap_priority', 0.5) }}">
            </div>
            <div>
                <label for="sitemap_changefreq" class="label">Sitemap Changefreq</label>
                <select id="sitemap_changefreq" name="sitemap_changefreq" class="select">
                    @foreach (['always','hourly','daily','weekly','monthly','yearly','never'] as $freq)
                        <option value="{{ $freq }}" @selected($value('sitemap_changefreq', 'weekly') === $freq)>{{ $freq }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <label class="cms-inline-check">
            <input type="hidden" name="sitemap_include" value="0">
            <input type="checkbox" name="sitemap_include" value="1" @checked((bool) $value('sitemap_include', true))>
            In Sitemap aufnehmen
        </label>
    </section>

    <section class="cms-pane" data-cms-tab-pane="redirects" hidden>
        <div class="cms-pane-head">
            <h3 class="admin-section-title">Weiterleitungen</h3>
            <span class="cms-section-kicker">Routing</span>
        </div>
        <div>
            <label for="redirect_old_urls_text" class="label">Alte URLs als JSON Array</label>
            <textarea id="redirect_old_urls_text" name="redirect_old_urls_text" class="textarea" rows="5">{{ $redirectOldUrlsValue }}</textarea>
            <p class="help-text">Beispiel: ["/alte-url","/alt/seite"]</p>
        </div>
    </section>

    <section class="cms-pane" data-cms-tab-pane="preview" hidden>
        <div class="cms-pane-head">
            <h3 class="admin-section-title">Vorschau</h3>
            <span class="cms-section-kicker">Review</span>
        </div>
        @if ($isEdit)
            <p><a class="btn btn-secondary" href="{{ route('pages.show', ['slugPath' => $page->slug_path]) }}" target="_blank" rel="noopener">Oeffentliche Ansicht</a></p>
        @else
            <p class="help-text">Nach dem ersten Speichern kann eine Vorschau geoeffnet werden.</p>
        @endif
    </section>

    <section class="cms-pane" data-cms-tab-pane="seo-check" hidden>
        <div class="cms-pane-head">
            <h3 class="admin-section-title">SEO-Check</h3>
            <span class="cms-section-kicker">Audit</span>
        </div>
        @if (isset($audit) && $audit)
            <p>
                <strong>Score:</strong> {{ $audit->score }}
                <span class="admin-status {{ $audit->status === 'green' ? 'is-live' : ($audit->status === 'yellow' ? 'is-draft' : 'is-active') }}">{{ strtoupper($audit->status) }}</span>
            </p>
            @if (! empty($audit->issues))
                <ul class="cms-issues">
                    @foreach ($audit->issues as $issue)
                        <li>{{ $issue['message'] ?? 'SEO Hinweis' }}</li>
                    @endforeach
                </ul>
            @endif
        @else
            <p class="help-text">SEO-Audit wird nach dem Speichern automatisch erstellt.</p>
        @endif
    </section>

    @if($isEdit && isset($revisions) && $revisions->isNotEmpty())
    <section class="cms-pane cms-pane-revisions" data-cms-tab-pane="revisions" hidden>
        <div class="cms-pane-head">
            <h3 class="admin-section-title">Versionsverlauf</h3>
            <span class="cms-section-kicker">History</span>
        </div>
        <div class="cms-revisions-split">
            <div class="cms-revisions-sidebar">
                <div class="cms-revisions-sidebar-head">
                    <div>
                        <h4 class="cms-revisions-title">Letzte {{ $revisions->count() }} Versionen</h4>
                    </div>
                    <div class="cms-revisions-sidebar-actions">
                        <span class="cms-revisions-counter">{{ $revisions->count() }}</span>
                        @if($revisions->count() > 1)
                        <button
                            type="button"
                            class="cms-revisions-prune-btn"
                            data-revisions-prune
                            data-prune-url="{{ route('admin.pages.revisions.prune', ['page' => $page]) }}"
                        >Alle ausser aktuelle</button>
                        @endif
                    </div>
                </div>
                <div class="cms-revisions-list" role="list">
                    @foreach($revisions as $revisionIndex => $revisionLoop)
                    @php
                        $rPayload     = is_array($revisionLoop->payload) ? $revisionLoop->payload : [];
                        $rTitle       = $rPayload['title'] ?? '—';
                        $rContent     = $rPayload['content'] ?? '';
                        $rExcerpt     = $rPayload['excerpt'] ?? '';
                        $rTemplate    = $rPayload['template'] ?? 'default';
                        $rRelativeTime = $revisionLoop->created_at?->locale('de')->diffForHumans() ?? 'unbekannt';
                        $rAbsoluteTime = $revisionLoop->created_at?->format('d.m.Y · H:i') ?? '—';
                        $rCompareRevision = $revisions->get($revisionIndex + 1);
                        $rComparePayload = is_array($rCompareRevision?->payload) ? $rCompareRevision->payload : [];
                        $rChangeFieldLabels = [
                            'title' => 'Titel',
                            'h1' => 'H1',
                            'slug' => 'Slug',
                            'excerpt' => 'Einleitung',
                            'content' => 'Inhalt',
                            'template' => 'Template',
                            'status' => 'Status',
                            'seo_title' => 'SEO Titel',
                            'meta_description' => 'Meta Description',
                            'canonical_url' => 'Canonical URL',
                            'sitemap_include' => 'Sitemap',
                        ];
                        $rChangeKeywords = [];

                        foreach ($rChangeFieldLabels as $rField => $rLabel) {
                            $rCurrentValue = $rPayload[$rField] ?? null;
                            $rPreviousValue = $rComparePayload[$rField] ?? null;

                            if (is_array($rCurrentValue)) {
                                $rCurrentValue = json_encode($rCurrentValue);
                            }

                            if (is_array($rPreviousValue)) {
                                $rPreviousValue = json_encode($rPreviousValue);
                            }

                            if (is_string($rCurrentValue)) {
                                $rCurrentValue = trim($rCurrentValue);
                            }

                            if (is_string($rPreviousValue)) {
                                $rPreviousValue = trim($rPreviousValue);
                            }

                            if ((string) $rCurrentValue !== (string) $rPreviousValue) {
                                $rChangeKeywords[] = $rLabel;
                            }
                        }

                        $rContentCurrentLength = mb_strlen(strip_tags((string) ($rPayload['content'] ?? '')));
                        $rContentPreviousLength = mb_strlen(strip_tags((string) ($rComparePayload['content'] ?? '')));
                        $rContentDelta = $rContentCurrentLength - $rContentPreviousLength;

                        if ($rContentDelta > 0) {
                            $rChangeKeywords[] = '+'.$rContentDelta.' Zeichen';
                        } elseif ($rContentDelta < 0) {
                            $rChangeKeywords[] = $rContentDelta.' Zeichen';
                        }

                        $rChangeKeywords = array_values(array_unique($rChangeKeywords));

                        if (empty($rChangeKeywords)) {
                            $rChangeKeywords = ['Kleine Anpassungen'];
                        }

                        $rRestoreUrl  = route('admin.pages.revisions.restore', ['page' => $page]);
                        $rPreviewData = [
                            'title' => $rTitle,
                            'content' => $rContent,
                            'excerpt' => $rExcerpt,
                            'template' => $rTemplate,
                            'restoreUrl' => $rRestoreUrl,
                            'restoreId' => $revisionLoop->id,
                            'isCurrent' => $revisionIndex === 0,
                            'label' => $rRelativeTime.' · '.$rAbsoluteTime,
                        ];
                    @endphp
                    <article class="cms-revision-card" data-revision-card role="listitem">
                        <span class="cms-revision-card-rail" aria-hidden="true"></span>
                        <div class="cms-revision-card-head">
                            <div class="cms-revision-card-meta">
                                <span class="cms-revision-card-time">{{ $rRelativeTime }}</span>
                                <span class="cms-revision-card-date">{{ $rAbsoluteTime }}</span>
                                <span class="cms-mini-pill">{{ strtoupper((string) $revisionLoop->change_type) }}</span>
                                @if($revisionIndex === 0)
                                    <span class="cms-mini-pill cms-mini-pill-current">AKTUELL</span>
                                @endif
                            </div>
                            <span class="cms-revision-card-user">{{ $revisionLoop->user?->name ?? 'System' }}</span>
                        </div>
                        <button type="button" class="cms-revision-card-trigger" data-revision-trigger>
                            <span class="cms-revision-card-open">Ansehen</span>
                        </button>
                        <details class="cms-revision-changes">
                            <summary>Geaendert</summary>
                            <div class="cms-revision-keywords">
                                @foreach($rChangeKeywords as $rKeyword)
                                    <span class="cms-mini-pill cms-mini-pill-subtle">{{ $rKeyword }}</span>
                                @endforeach
                            </div>
                        </details>
                        <script type="application/json" data-revision-payload>@json($rPreviewData)</script>
                    </article>
                    @endforeach
                </div>
            </div>
            <div class="cms-revisions-preview-panel">
                <div class="cms-revisions-preview-empty" data-revision-preview-empty>
                    <svg viewBox="0 0 24 24" width="32" height="32" aria-hidden="true">
                        <path d="M12 8v4l3 3M12 3a9 9 0 1 0 0 18A9 9 0 0 0 12 3z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p>Waehle links eine Version<br>fuer die Studio-Vorschau</p>
                </div>
                <div class="cms-revisions-preview-active" data-revision-preview-active hidden>
                    <div class="cms-revisions-preview-bar" data-revision-preview-bar>
                        <div class="cms-revisions-preview-bar-meta">
                            <span class="cms-revisions-preview-kicker">Vorschau</span>
                            <span data-revision-preview-label></span>
                        </div>
                        <button
                            type="button"
                            class="btn btn-primary"
                            data-revision-restore-confirm
                        >Wiederherstellen</button>
                    </div>
                    <div class="cms-revisions-preview-loading" data-revision-preview-loading hidden>
                        <span class="cms-revisions-preview-spinner" aria-hidden="true"></span>
                        <span>Vorschau wird geladen</span>
                    </div>
                    <iframe
                        class="cms-revisions-preview-frame"
                        data-revision-preview-frame
                        title="Versionsvorschau"
                    ></iframe>
                </div>
            </div>
        </div>
    </section>
    @endif

    <input type="hidden" name="schema_data" id="schema_data" value="">
    <input type="hidden" name="redirect_old_urls" id="redirect_old_urls" value="">

    <div class="cms-sticky-save" role="region" aria-label="Speichern">
        <button type="submit" class="btn btn-primary" data-save-button>{{ $isEdit ? 'Aenderungen speichern' : 'Seite speichern' }}</button>
    </div>
</div>
