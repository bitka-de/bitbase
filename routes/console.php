<?php

use App\Jobs\CheckBrokenLinksJob;
use App\Jobs\GenerateSitemapJob;
use App\Jobs\OptimizeMediaJob;
use App\Jobs\RunSeoAuditJob;
use App\Models\Media;
use App\Models\Page;
use App\Services\RedirectService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('seo:audit {pageId?}', function (?int $pageId = null) {
    $ids = $pageId ? [$pageId] : Page::query()->pluck('id')->all();

    foreach ($ids as $id) {
        RunSeoAuditJob::dispatchSync((int) $id);
    }

    $this->info('SEO audits completed.');
})->purpose('Run SEO audits for one or all pages');

Artisan::command('sitemap:generate', function () {
    GenerateSitemapJob::dispatchSync();
    $this->info('Sitemap cache generated.');
})->purpose('Generate sitemap xml cache');

Artisan::command('redirects:check {path}', function (string $path) {
    $chain = app(RedirectService::class)->detectChain($path);
    $this->info('Redirect chain: '.implode(' -> ', $chain));
})->purpose('Check redirect chain for an URL path');

Artisan::command('media:optimize {mediaId?}', function (?int $mediaId = null) {
    $ids = $mediaId ? [$mediaId] : Media::query()->pluck('id')->all();

    foreach ($ids as $id) {
        OptimizeMediaJob::dispatchSync((int) $id);
    }

    $this->info('Media optimization jobs completed.');
})->purpose('Optimize one or all media files');

Artisan::command('links:check {pageId?}', function (?int $pageId = null) {
    $ids = $pageId ? [$pageId] : Page::query()->pluck('id')->all();

    foreach ($ids as $id) {
        CheckBrokenLinksJob::dispatchSync((int) $id);
    }

    $this->info('Internal link checks completed.');
})->purpose('Run internal link checks for one or all pages');
