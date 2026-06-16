<?php

namespace App\Jobs;

use App\Models\Page;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckBrokenLinksJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $pageId)
    {
    }

    public function handle(): void
    {
        // Placeholder for link crawler and integrity checks.
        Page::query()->whereKey($this->pageId)->exists();
    }
}
