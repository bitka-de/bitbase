<?php

namespace App\Jobs;

use App\Services\SitemapService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class GenerateSitemapJob implements ShouldQueue
{
    use Queueable;

    public function handle(SitemapService $sitemapService): void
    {
        Cache::put('seo:sitemap:pages', $sitemapService->pagesXml(), now()->addHours(6));
    }
}
