<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Str;

class SlugService
{
    public function generate(string $value): string
    {
        return Str::slug($value);
    }

    public function uniqueForPage(Page $page, string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 2;

        while ($this->exists($page, $slug)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function exists(Page $page, string $slug): bool
    {
        return Page::query()
            ->where('slug', $slug)
            ->where('locale', $page->locale)
            ->where('parent_id', $page->parent_id)
            ->whereKeyNot($page->id)
            ->exists();
    }
}
