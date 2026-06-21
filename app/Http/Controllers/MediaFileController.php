<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaFileController extends Controller
{
    public function __invoke(string $filename): StreamedResponse
    {
        $media = Media::query()
            ->where('path', 'like', '%/'.$filename)
            ->orWhere('variants', 'like', '%'.$filename.'%')
            ->firstOrFail();

        $path = $this->resolvePath($media, $filename);
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($media->disk);

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, $filename, [
            'Content-Type' => $media->mime_type ?: ($disk->mimeType($path) ?: 'application/octet-stream'),
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    private function resolvePath(Media $media, string $filename): string
    {
        if (basename($media->path) === $filename) {
            return $media->path;
        }

        $variants = is_array($media->variants) ? $media->variants : [];

        foreach ($variants as $variant) {
            $path = data_get($variant, 'path');
            if (is_string($path) && basename($path) === $filename) {
                return $path;
            }
        }

        abort(404);
    }
}
