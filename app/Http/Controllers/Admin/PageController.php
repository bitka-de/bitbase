<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\ContentComponent;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageRevision;
use App\Services\SeoAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * @return array<string, string>
     */
    private function templateOptions(): array
    {
        return [
            'default' => 'Standard',
            'focused' => 'Fokus',
            'story' => 'Story',
        ];
    }

    public function index(): View
    {
        $pages = Page::query()
            ->with('author')
            ->latest()
            ->paginate(10);

        return view('pages.admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        $starterData = [
            'title' => 'Beispielseite',
            'h1' => 'Beispiel Ueberschrift',
            'slug' => 'beispielseite',
            'excerpt' => 'Das ist eine kurze Beispielbeschreibung fuer den Einstieg in die Seitenerstellung.',
            'content' => "<section>\n  <h2>Einleitung</h2>\n  <p>Ersetze diesen Beispieltext mit deinem eigenen Inhalt.</p>\n</section>\n\n<section>\n  <h2>Hauptinhalt</h2>\n  <p>Hier kannst du Abschnitte, Listen und Call-to-Actions einfuegen.</p>\n</section>",
            'seo_title' => 'Beispielseite | '.config('app.name'),
            'meta_description' => 'Beispiel-Meta-Description fuer eine neue CMS-Seite.',
            'robots_index' => 'index',
            'robots_follow' => 'follow',
            'locale' => app()->getLocale(),
            'status' => 'draft',
            'template' => 'default',
            'sitemap_include' => true,
            'sitemap_priority' => 0.5,
            'sitemap_changefreq' => 'weekly',
        ];

        return view('pages.admin.pages.create', [
            'statuses' => PageStatus::cases(),
            'templates' => $this->templateOptions(),
            'starterData' => $starterData,
            'contentComponents' => ContentComponent::query()->orderBy('title')->get(['id', 'name', 'title', 'description', 'tags', 'content', 'css', 'js']),
            'mediaLibrary' => $this->mediaLibraryItems(),
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $data = $this->normalizePayload($request->validated());

        $page = Page::create($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Seite wurde erfolgreich erstellt.');
    }

    public function edit(Page $page): View
    {
        return view('pages.admin.pages.edit', [
            'page' => $page,
            'revisions' => $page->revisions()->with('user:id,name')->limit(5)->get(),
            'statuses' => PageStatus::cases(),
            'templates' => $this->templateOptions(),
            'audit' => $page->latestSeoAudit,
            'contentComponents' => ContentComponent::query()->orderBy('title')->get(['id', 'name', 'title', 'description', 'tags', 'content', 'css', 'js']),
            'mediaLibrary' => $this->mediaLibraryItems(),
        ]);
    }

    public function restoreRevision(Request $request, Page $page): RedirectResponse
    {
        $validated = $request->validate([
            'revision_id' => ['required', 'integer'],
        ]);

        $revision = $page->revisions()
            ->whereKey($validated['revision_id'])
            ->first();

        if (! $revision instanceof PageRevision) {
            return redirect()
                ->route('admin.pages.edit', $page)
                ->with('error', 'Die ausgewaehlte Version wurde nicht gefunden.');
        }

        $payload = is_array($revision->payload) ? $revision->payload : [];
        $restoreData = Arr::only($payload, $page->getFillable());

        if (empty($restoreData)) {
            return redirect()
                ->route('admin.pages.edit', $page)
                ->with('error', 'Die ausgewaehlte Version konnte nicht wiederhergestellt werden.');
        }

        $page->update($restoreData);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Version wurde erfolgreich wiederhergestellt.');
    }

    public function pruneRevisions(Page $page): RedirectResponse
    {
        $revisionIds = $page->revisions()->pluck('id');

        if ($revisionIds->count() <= 1) {
            return redirect()
                ->route('admin.pages.edit', $page)
                ->with('success', 'Es gibt keine alten Versionen zum Entfernen.');
        }

        $revisionIdsToDelete = $revisionIds->slice(1)->values();
        $deletedCount = $revisionIdsToDelete->count();

        if ($deletedCount > 0) {
            $page->revisions()
                ->whereIn('id', $revisionIdsToDelete)
                ->delete();
        }

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', $deletedCount.' alte Version(en) wurden entfernt.');
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $data = $this->normalizePayload($request->validated());

        $page->update($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Seite wurde erfolgreich aktualisiert.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Seite wurde erfolgreich geloescht.');
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function normalizePayload(array $validated): array
    {
        $status = $validated['status'] ?? PageStatus::Draft->value;
        $isPublished = $status === PageStatus::Published->value;

        $validated['is_published'] = $isPublished;
        $validated['published_at'] = $isPublished
            ? ($validated['published_at'] ?? now())
            : null;
        $validated['author_id'] ??= Auth::id();
        $validated['sitemap_include'] = (bool) ($validated['sitemap_include'] ?? false);
        $validated['h1'] = $validated['h1'] ?? $validated['title'];
        $validated['template'] = $validated['template'] ?? 'default';

        if (! empty($validated['schema_data'])) {
            $validated['schema_data'] = json_decode((string) $validated['schema_data'], true) ?: null;
        } else {
            $validated['schema_data'] = null;
        }

        if (! empty($validated['redirect_old_urls'])) {
            $validated['redirect_old_urls'] = json_decode((string) $validated['redirect_old_urls'], true) ?: null;
        } else {
            $validated['redirect_old_urls'] = null;
        }

        return $validated;
    }

    private function mediaLibraryItems()
    {
        return Media::query()
            ->latest()
            ->get()
            ->map(function (Media $media): array {
                $xsPath = data_get($media->variants, 'xs.path');

                return [
                    'id' => $media->id,
                    'name' => $media->name ?: pathinfo(basename($media->path), PATHINFO_FILENAME),
                    'filename' => basename($media->path),
                    'alt_text' => $media->alt_text,
                    'source' => $media->source,
                    'url' => $media->url,
                    'preview_url' => is_string($xsPath) && $xsPath !== ''
                        ? route('media.show', ['filename' => basename($xsPath)])
                        : $media->url,
                    'width' => $media->width,
                    'height' => $media->height,
                ];
            })
            ->values();
    }
}