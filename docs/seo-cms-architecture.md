# SEO CMS Architecture (Laravel)

Stand: 2026-06-16

## Ist-Analyse

- Stack: Laravel 13, Blade, Vite, Fortify, klassisches MVC.
- Vorhanden: Basis-`pages` CRUD im Admin, keine vollstaendige SEO-Domain, keine Redirect-Historie, keine Audit-Schicht.
- Routing: Adminbereich vorhanden, Public Seitenrouting bisher statisch.
- Rendering: Meta-Tags waren bisher layout-basiert und nicht zentral servicegesteuert.

## Zielbild

- SEO-faehiges, hierarchisches, mehrsprachig vorbereitbares Seiten-CMS.
- Klare Trennung in Domain-Modelle, Services, Observer/Jobs, Policies, Requests.
- Public-Ausgabe mit konsistenten Meta-Tags, JSON-LD, Canonical, hreflang.
- Operative SEO-Prozesse ueber Artisan Commands und Jobs.

## Datenmodell

### Tabellen

- `pages`: erweitert um SEO-, Publishing-, Hierarchie- und Sitemap-Felder.
- `page_revisions`: Versionierung / Aenderungsverlauf.
- `redirects`: URL-Historie, 301/302/410, Chain-Pruefung.
- `media`: Bild-Metadaten und Varianten.
- `seo_audits`: Score + Issues pro Seite.
- `translation_groups`: Verknuepfung von Sprachvarianten.

### Wichtige Felder in `pages`

- Inhalt: `title`, `slug`, `h1`, `excerpt`, `content`
- Status: `status`, `published_at`, `updated_content_at`
- SEO: `seo_title`, `meta_description`, `canonical_url`, `robots_index`, `robots_follow`
- Social: `og_*`, `twitter_*`
- Schema: `schema_type`, `schema_data`
- I18n/Struktur: `locale`, `translation_group_id`, `parent_id`
- CMS: `template`, `sort_order`, `author_id`, `reviewer_id`
- Redirect/Sitemap: `redirect_old_urls`, `sitemap_include`, `sitemap_priority`, `sitemap_changefreq`

## Services

- `SlugService`: Slug-Generierung + Eindeutigkeit je `locale + parent_id`.
- `RedirectService`: Redirect-Erstellung, Aufloesung, Chain-Erkennung.
- `SeoMetaService`: zentrale Meta-Tag-Erzeugung mit Fallbacks.
- `SchemaService`: JSON-LD inkl. BreadcrumbList.
- `SitemapService`: XML aus indexierbaren, publizierten Seiten.
- `RobotsService`: dynamische robots.txt inkl. Environment-Block.
- `SeoAuditService`: Score/Ampel und Issues.

## Observer und Jobs

- `PageObserver`: Slug-Handling, Redirect bei Slugwechsel, Revisionen, Cache-Invalidierung.
- `MediaObserver`: Dateimetadaten (mime/file_size).
- Jobs: `RunSeoAuditJob`, `GenerateSitemapJob`, `OptimizeMediaJob`, `CheckBrokenLinksJob`.

## UI / Components

- Head-Komponente: `<x-seo-meta :page="$page" />`
- Breadcrumbs: `<x-breadcrumbs :page="$page" />`
- Bilder: `<x-responsive-image :media="$media" />`
- Admin-Form Tabs: Inhalt, SEO, Social, Schema, Medien, Veroeffentlichung, Weiterleitungen, Vorschau, SEO-Check

## Sicherheit und Indexierung

- Drafts sind nicht oeffentlich sichtbar.
- `robots.txt` blockt Nicht-Production per Default.
- Adminbereich bleibt `noindex,nofollow`.
- Redirects koennen 410 liefern (Soft-Delete/Content-Retirement).

## Tests (abgedeckt)

- Meta-Tag-Ausgabe
- Sitemap-Filter (published + indexable)
- Redirect nach Slug-Aenderung
- Draft nicht oeffentlich erreichbar

## Empfohlene naechste Ausbaustufen

1. Echte Redirect-Chain-Validation als Report + UI-Hinweis.
2. FAQ-Schema nur bei nachweisbaren FAQ-Blcken ausgeben.
3. Translation-UI mit x-default/hreflang-Management.
4. Medienpipeline mit AVIF/WebP-Generierung und responsive srcset.
5. Full Page Cache fuer Published-Pages + gezielte Invalidierung.
6. Linkgraph-Analyse fuer orphan pages / broken internal links.
7. Freigabe-Workflow (Reviewer Approve/Reject) und geplante Depublizierung.
