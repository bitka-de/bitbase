<?php

namespace App\Services;

use App\Models\Page;

class SchemaService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function forPage(Page $page): array
    {
        $schemas = [];
        $type = $page->schema_type ?: 'WebPage';

        $base = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'name' => $page->title,
            'url' => $page->canonical_url ?: $page->public_url,
            'description' => $page->resolved_meta_description,
            'inLanguage' => $page->locale,
        ];

        $schemas[] = array_merge($base, $page->schema_data ?? []);
        $schemas[] = $this->breadcrumbSchema($page);

        return $schemas;
    }

    /**
     * @return array<string, mixed>
     */
    public function breadcrumbSchema(Page $page): array
    {
        $trail = [];
        $cursor = $page;

        while ($cursor !== null) {
            $trail[] = $cursor;
            $cursor = $cursor->parent;
        }

        $trail = array_reverse($trail);
        $items = [];

        foreach ($trail as $index => $node) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $node->title,
                'item' => $node->canonical_url ?: $node->public_url,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}
