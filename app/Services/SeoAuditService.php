<?php

namespace App\Services;

use App\Models\Page;
use App\Models\SeoAudit;
use Illuminate\Support\Str;

class SeoAuditService
{
    public function run(Page $page): SeoAudit
    {
        $issues = [];
        $score = 100;

        if (blank($page->seo_title)) {
            $issues[] = ['level' => 'yellow', 'key' => 'missing_seo_title', 'message' => 'SEO Title fehlt.'];
            $score -= 8;
        }

        $titleLength = Str::length($page->resolved_seo_title);
        if ($titleLength < 30 || $titleLength > 60) {
            $issues[] = ['level' => 'yellow', 'key' => 'seo_title_length', 'message' => 'SEO Title sollte ca. 30-60 Zeichen haben.'];
            $score -= 6;
        }

        if (blank($page->meta_description)) {
            $issues[] = ['level' => 'yellow', 'key' => 'missing_meta_description', 'message' => 'Meta Description fehlt.'];
            $score -= 8;
        }

        $descriptionLength = Str::length($page->resolved_meta_description);
        if ($descriptionLength < 70 || $descriptionLength > 160) {
            $issues[] = ['level' => 'yellow', 'key' => 'meta_description_length', 'message' => 'Meta Description sollte ca. 70-160 Zeichen haben.'];
            $score -= 6;
        }

        if (blank($page->h1)) {
            $issues[] = ['level' => 'red', 'key' => 'missing_h1', 'message' => 'H1 fehlt.'];
            $score -= 12;
        }

        if ($page->robots_index === 'noindex') {
            $issues[] = ['level' => 'yellow', 'key' => 'noindex_enabled', 'message' => 'Seite ist auf noindex gesetzt.'];
            $score -= 5;
        }

        if (blank($page->canonical_url) && $page->robots_index === 'index') {
            $issues[] = ['level' => 'yellow', 'key' => 'missing_canonical', 'message' => 'Canonical URL fehlt.'];
            $score -= 5;
        }

        $score = max(0, min(100, $score));
        $status = $score >= 80 ? 'green' : ($score >= 55 ? 'yellow' : 'red');

        return SeoAudit::query()->updateOrCreate(
            ['page_id' => $page->id],
            [
                'score' => $score,
                'status' => $status,
                'issues' => $issues,
                'checked_at' => now(),
            ]
        );
    }
}
