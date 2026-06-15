@extends('layouts.default')

@section('meta_title', 'Stylebook | ' . config('app.name'))
@section('meta_description', 'Uebersicht der wichtigsten UI-Elemente: Typografie, Buttons, Formulare, Alerts, Cards und Tabellen.')
@section('meta_robots', 'noindex,follow')
@section('canonical_url', route('stylebook'))
@section('og_title', 'Stylebook | ' . config('app.name'))
@section('og_description', 'Design- und Komponenten-Uebersicht fuer das aktuelle Theme.')

@section('content')
    <section class="surface boxed stylebook-main">
        <header class="stylebook-hero">
            <span class="accent-badge">Design System</span>
            <h1 class="stylebook-title">Stylebook</h1>
            <p class="stylebook-lead">
                Editorial aufgebautes Referenzboard fuer Farben, Typografie und Kernkomponenten.
            </p>
        </header>

        <div class="stylebook-shell">
            <aside class="stylebook-nav" aria-label="Stylebook Navigation">
                <a href="#sb-foundations" class="stylebook-nav-link">Foundations</a>
                <a href="#sb-typography" class="stylebook-nav-link">Typografie</a>
                <a href="#sb-buttons" class="stylebook-nav-link">Buttons</a>
                <a href="#sb-alerts" class="stylebook-nav-link">Alerts</a>
                <a href="#sb-forms" class="stylebook-nav-link">Formulare</a>
                <a href="#sb-cards" class="stylebook-nav-link">Cards</a>
                <a href="#sb-table" class="stylebook-nav-link">Tabelle</a>
            </aside>

            <div class="stylebook-content">
                <section id="sb-foundations" class="card stylebook-section stylebook-section-wide stack-md" aria-labelledby="sb-foundations-title">
                    <h2 id="sb-foundations-title" class="stylebook-section-title">Foundations</h2>

                    <div class="stylebook-foundations-grid">
                        <article class="stylebook-foundation-card stack-sm" aria-labelledby="sb-colors">
                            <h3 id="sb-colors" class="stylebook-foundation-title">Farben</h3>
                            <div class="stylebook-color-grid">
                                <div class="stylebook-color-swatch"><span style="background: var(--page-bg)"></span><p>Page BG</p></div>
                                <div class="stylebook-color-swatch"><span style="background: var(--card-bg)"></span><p>Card BG</p></div>
                                <div class="stylebook-color-swatch"><span style="background: var(--ink)"></span><p>Ink</p></div>
                                <div class="stylebook-color-swatch"><span style="background: var(--muted)"></span><p>Muted</p></div>
                                <div class="stylebook-color-swatch"><span style="background: var(--accent)"></span><p>Accent</p></div>
                                <div class="stylebook-color-swatch"><span style="background: var(--accent-soft)"></span><p>Accent Soft</p></div>
                                <div class="stylebook-color-swatch"><span style="background: var(--success)"></span><p>Success</p></div>
                                <div class="stylebook-color-swatch"><span style="background: var(--warning)"></span><p>Warning</p></div>
                                <div class="stylebook-color-swatch"><span style="background: var(--danger)"></span><p>Danger</p></div>
                                <div class="stylebook-color-swatch"><span style="background: var(--info)"></span><p>Info</p></div>
                            </div>
                        </article>

                        <article class="stylebook-foundation-card stack-sm" aria-labelledby="sb-fonts">
                            <h3 id="sb-fonts" class="stylebook-foundation-title">Fonts</h3>
                            <p class="stylebook-font-sample stylebook-font-heading">Heading Font: Aa Bb Cc 123</p>
                            <p class="stylebook-font-sample stylebook-font-body">Body Font: The quick brown fox jumps over the lazy dog.</p>
                            <p class="stylebook-font-sample stylebook-font-mono">Token/Code: --btn-primary-bg: var(--accent);</p>
                        </article>

                        <article class="stylebook-foundation-card stack-sm" aria-labelledby="sb-tokens">
                            <h3 id="sb-tokens" class="stylebook-foundation-title">Wichtige Tokens</h3>
                            <div class="stylebook-token-list">
                                <span class="stylebook-token-chip">--radius-md</span>
                                <span class="stylebook-token-chip">--radius-lg</span>
                                <span class="stylebook-token-chip">--shadow-sm</span>
                                <span class="stylebook-token-chip">--shadow-md</span>
                                <span class="stylebook-token-chip">--btn-primary-bg</span>
                                <span class="stylebook-token-chip">--btn-secondary-bg</span>
                                <span class="stylebook-token-chip">--focus-ring</span>
                            </div>
                        </article>
                    </div>
                </section>

                <section id="sb-typography" class="card stylebook-section stylebook-section-wide stack-md" aria-labelledby="sb-typography-title">
                    <h2 id="sb-typography-title" class="stylebook-section-title">Typografie</h2>
                    <div class="stack-sm stylebook-type-scale">
                        <h1>Heading H1 - Klarer Primartitel</h1>
                        <h2>Heading H2 - Abschnittsebene</h2>
                        <h3>Heading H3 - Detailebene</h3>
                        <p>
                            Standard-Absatztext fuer Lesbarkeit, Zeilenhoehe und Kontrast.
                            <a href="#" class="soft-link">Inline-Link Beispiel</a>
                        </p>
                        <p class="muted">Muted Text fuer sekundaire Informationen.</p>
                    </div>
                </section>

                <section id="sb-buttons" class="card stylebook-section stack-md" aria-labelledby="sb-buttons-title">
                    <h2 id="sb-buttons-title" class="stylebook-section-title">Buttons</h2>
                    <div class="stylebook-button-row">
                        <button type="button" class="btn btn-primary">Primary</button>
                        <button type="button" class="btn btn-secondary">Secondary</button>
                        <button type="button" class="btn btn-ghost">Ghost</button>
                        <button type="button" class="btn btn-primary">
                            <x-heroicon-o-arrow-right class="btn-icon" aria-hidden="true" />
                            <span>Mit Icon</span>
                        </button>
                        <button type="button" class="btn btn-secondary btn-icon-only" aria-label="Schnellaktion" title="Schnellaktion">
                            <x-heroicon-o-plus class="btn-icon" aria-hidden="true" />
                        </button>
                    </div>
                </section>

                <section id="sb-alerts" class="card stylebook-section stack-md" aria-labelledby="sb-alerts-title">
                    <h2 id="sb-alerts-title" class="stylebook-section-title">Alerts</h2>
                    <div class="stack-sm">
                        <div class="alert alert-info">Info: Hintergrundprozess wurde gestartet.</div>
                        <div class="alert alert-success">Erfolg: Aenderungen wurden gespeichert.</div>
                        <div class="alert alert-warning">Hinweis: Bitte Eingaben ueberpruefen.</div>
                        <div class="alert alert-danger">Fehler: Aktion konnte nicht abgeschlossen werden.</div>
                    </div>
                </section>

                <section id="sb-forms" class="card stylebook-section stylebook-section-wide stack-md" aria-labelledby="sb-forms-title">
                    <h2 id="sb-forms-title" class="stylebook-section-title">Formularfelder</h2>
                    <form class="stylebook-form" action="#" method="GET">
                        <div class="stylebook-form-grid">
                            <div>
                                <label for="sb-name" class="label">Name</label>
                                <input id="sb-name" class="input" type="text" value="Max Mustermann">
                                <p class="help-text">Ein einfaches Input-Feld mit Hilfetext.</p>
                            </div>

                            <div>
                                <label for="sb-role" class="label">Rolle</label>
                                <select id="sb-role" class="select">
                                    <option>Admin</option>
                                    <option>Editor</option>
                                    <option>Gast</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="sb-note" class="label">Notiz</label>
                            <textarea id="sb-note" class="textarea">Kurze Notiz als Beispielinhalt.</textarea>
                        </div>
                    </form>
                </section>

                <section id="sb-cards" class="card stylebook-section stack-md" aria-labelledby="sb-cards-title">
                    <h2 id="sb-cards-title" class="stylebook-section-title">Cards</h2>
                    <div class="grid-auto">
                        <article class="card stack-sm">
                            <h3>Card Titel</h3>
                            <p class="muted">Kompakter Inhalt in einer Standard-Card.</p>
                            <a href="#" class="soft-link">Mehr erfahren</a>
                        </article>

                        <article class="card stack-sm">
                            <h3>Status</h3>
                            <p>Cards eignen sich fuer Dashboards und Listenansichten.</p>
                            <span class="accent-badge">Aktiv</span>
                        </article>
                    </div>
                </section>

                <section id="sb-table" class="card stylebook-section stylebook-section-wide stack-md" aria-labelledby="sb-table-title">
                    <h2 id="sb-table-title" class="stylebook-section-title">Tabelle</h2>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Element</th>
                                    <th>Variante</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Button</td>
                                    <td>Primary</td>
                                    <td>Produktiv</td>
                                </tr>
                                <tr>
                                    <td>Alert</td>
                                    <td>Success</td>
                                    <td>Produktiv</td>
                                </tr>
                                <tr>
                                    <td>Form</td>
                                    <td>Input</td>
                                    <td>Produktiv</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection