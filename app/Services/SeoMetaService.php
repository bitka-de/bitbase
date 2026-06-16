<?php

namespace App\Services;

use App\Models\Page;

class SeoMetaService
{
    /**
     * @return array<string, mixed>
     */
    public function build(?Page $page = null): array
    {
        if ($page === null) {
            return [
                'title' => config('app.name'),
                'description' => 'Willkommen auf unserer Website.',
                'robots' => app()->environment('production') ? 'index,follow' : 'noindex,nofollow',
                'canonical' => url()->current(),
                'og' => [
                    'type' => 'website',
                    'title' => config('app.name'),
                    'description' => 'Willkommen auf unserer Website.',
                    'url' => url()->current(),
                    'image' => asset('favicon.ico'),
                ],
                'twitter' => [
                    'card' => 'summary_large_image',
                    'title' => config('app.name'),
                    'description' => 'Willkommen auf unserer Website.',
                    'image' => asset('favicon.ico'),
                ],
                'hreflang' => [],
            ];
        }

        $canonical = $page->canonical_url ?: $page->public_url;
        $title = $page->resolved_seo_title;
        $description = $page->resolved_meta_description;

        return [
            'title' => $title,
            'description' => $description,
            'robots' => $page->robots_index.','.$page->robots_follow,
            'canonical' => $canonical,
            'og' => [
                'type' => 'website',
                'title' => $page->og_title ?: $title,
                'description' => $page->og_description ?: $description,
                'url' => $canonical,
                'image' => $page->ogImage?->url ?: asset('favicon.ico'),
            ],
            'twitter' => [
                'card' => 'summary_large_image',
                'title' => $page->twitter_title ?: ($page->og_title ?: $title),
                'description' => $page->twitter_description ?: ($page->og_description ?: $description),
                'image' => $page->twitterImage?->url ?: ($page->ogImage?->url ?: asset('favicon.ico')),
            ],
            'hreflang' => $this->hreflang($page),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function hreflang(Page $page): array
    {
        if ($page->translation_group_id === null) {
            return [];
        }

        return $page->translations()
            ->get()
            ->mapWithKeys(fn (Page $variant): array => [
                $variant->locale => $variant->canonical_url ?: $variant->public_url,
            ])
            ->all();
    }
}
