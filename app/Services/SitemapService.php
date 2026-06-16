<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Carbon;

class SitemapService
{
    public function pagesXml(): string
    {
        $pages = Page::query()
            ->published()
            ->indexable()
            ->where('status', 'published')
            ->get();

        $items = $pages->map(function (Page $page): string {
            $lastmod = $page->updated_content_at ?: $page->updated_at ?: Carbon::now();

            return '<url>'
                .'<loc>'.e($page->canonical_url ?: $page->public_url).'</loc>'
                .'<lastmod>'.$lastmod->toAtomString().'</lastmod>'
                .'<changefreq>'.e($page->sitemap_changefreq ?: 'weekly').'</changefreq>'
                .'<priority>'.number_format((float) ($page->sitemap_priority ?? 0.5), 1).'</priority>'
                .'</url>';
        })->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .$items
            .'</urlset>';
    }
}
