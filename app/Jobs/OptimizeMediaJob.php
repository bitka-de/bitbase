<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class OptimizeMediaJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $mediaId)
    {
    }

    public function handle(): void
    {
        // Placeholder for image optimization (e.g. AVIF/WebP variants).
        Media::query()->whereKey($this->mediaId)->exists();
    }
}
