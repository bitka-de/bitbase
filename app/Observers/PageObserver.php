<?php

namespace App\Observers;

use App\Enums\PageStatus;
use App\Jobs\GenerateSitemapJob;
use App\Jobs\RunSeoAuditJob;
use App\Models\Page;
use App\Models\PageRevision;
use App\Services\RedirectService;
use App\Services\SlugService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PageObserver
{
    public function creating(Page $page): void
    {
        $this->ensureSlug($page);
        $this->syncPublishState($page);
        $page->updated_content_at = now();
        $page->author_id ??= Auth::id();
    }

    public function updating(Page $page): void
    {
        $oldSlugPath = $page->getOriginal('slug');

        $this->ensureSlug($page);
        $this->syncPublishState($page);

        if ($page->isDirty(['title', 'h1', 'excerpt', 'content'])) {
            $page->updated_content_at = now();
        }

        if ($page->isDirty('slug') && ! empty($oldSlugPath)) {
            app(RedirectService::class)->createPermanent($oldSlugPath, $page->slug);
        }
    }

    public function saved(Page $page): void
    {
        PageRevision::query()->create([
            'page_id' => $page->id,
            'user_id' => Auth::id(),
            'change_type' => $page->wasRecentlyCreated ? 'create' : 'update',
            'payload' => $page->fresh()?->toArray() ?? [],
        ]);

        $staleRevisionIds = $page->revisions()
            ->select('id')
            ->pluck('id')
            ->slice(5)
            ->values();

        if ($staleRevisionIds->isNotEmpty()) {
            PageRevision::query()
                ->whereIn('id', $staleRevisionIds)
                ->delete();
        }

        Cache::forget('seo:sitemap:pages');

        RunSeoAuditJob::dispatch($page->id);
        GenerateSitemapJob::dispatch();
    }

    private function ensureSlug(Page $page): void
    {
        $slugService = app(SlugService::class);

        $baseSlug = $page->slug ?: $slugService->generate($page->title);
        $page->slug = $slugService->uniqueForPage($page, $baseSlug);
    }

    private function syncPublishState(Page $page): void
    {
        $isPublished = $page->status === PageStatus::Published;
        $page->is_published = $isPublished;

        if ($isPublished && empty($page->published_at)) {
            $page->published_at = now();
        }
    }
}
