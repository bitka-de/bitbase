<?php

namespace App\Observers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaObserver
{
    public function saving(Media $media): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($media->disk);

        if (! $disk->exists($media->path)) {
            return;
        }

        $media->file_size = $media->file_size ?: $disk->size($media->path);
        $media->mime_type = $media->mime_type ?: $disk->mimeType($media->path);
    }
}
