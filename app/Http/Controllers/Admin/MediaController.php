<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    private ?bool $mediaTagsColumnExists = null;

    public function index(): View
    {
        $mediaQuery = Media::query()->latest();

        $totalMedia = (clone $mediaQuery)->count();
        $imagesThisMonth = Media::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $mediaItems = Media::query()
            ->latest()
            ->paginate(24);

        $availableTags = collect();

        if ($this->hasMediaTagsColumn()) {
            $availableTags = Media::query()
                ->pluck('tags')
                ->filter(fn ($tags) => is_array($tags) && $tags !== [])
                ->flatMap(fn (array $tags) => $tags)
                ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
                ->map(fn ($tag) => mb_strtolower(trim($tag)))
                ->countBy()
                ->sortKeys()
                ->map(fn ($count, $name) => [
                    'name' => $name,
                    'count' => $count,
                ])
                ->values();
        }

        return view('pages.admin.media.index', [
            'mediaItems' => $mediaItems,
            'availableTags' => $availableTags,
            'hasMediaTags' => $this->hasMediaTagsColumn(),
            'stats' => [
                'total' => $totalMedia,
                'this_month' => $imagesThisMonth,
            ],
            'defaults' => [
                'max_width' => 1920,
                'max_height' => 800,
                'xs_max_width' => 400,
                'xs_max_height' => 600,
                'quality' => 82,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'file' => ['nullable', 'file', 'image', 'max:20480'],
            'files' => ['nullable', 'array', 'min:1'],
            'files.*' => ['file', 'image', 'max:20480'],
            'name' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:320'],
            'max_width' => ['nullable', 'integer', 'min:1', 'max:8000'],
            'max_height' => ['nullable', 'integer', 'min:1', 'max:8000'],
            'xs_max_width' => ['nullable', 'integer', 'min:1', 'max:4000'],
            'xs_max_height' => ['nullable', 'integer', 'min:1', 'max:4000'],
            'quality' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $normalizedTags = $this->hasMediaTagsColumn()
            ? $this->normalizeTagsString($validated['tags'] ?? null)
            : [];

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return $this->storeErrorResponse($request, 'Bildverarbeitung ist auf dem Server nicht verfuegbar (GD/WebP fehlt).');
        }

        $maxWidth = (int) ($validated['max_width'] ?? 1920);
        $maxHeight = (int) ($validated['max_height'] ?? 800);
        $xsMaxWidth = (int) ($validated['xs_max_width'] ?? 400);
        $xsMaxHeight = (int) ($validated['xs_max_height'] ?? 600);
        $quality = (int) ($validated['quality'] ?? 82);

        $uploadedFiles = [];

        if ($request->hasFile('files')) {
            $uploadedFiles = array_values(array_filter((array) $request->file('files')));
        }

        if ($request->hasFile('file')) {
            $singleFile = $request->file('file');
            if ($singleFile !== null) {
                $uploadedFiles[] = $singleFile;
            }
        }

        if ($uploadedFiles === []) {
            return $this->storeErrorResponse($request, 'Keine Datei gefunden.');
        }

        $items = [];

        foreach ($uploadedFiles as $index => $uploadedFile) {
            $raw = @file_get_contents($uploadedFile->getRealPath());
            if ($raw === false) {
                return $this->storeErrorResponse($request, 'Die Datei konnte nicht gelesen werden.');
            }

            $resolvedName = $validated['name'] ?? null;
            if (count($uploadedFiles) > 1) {
                $resolvedName = null;
            }

            $media = $this->createMediaFromBinary($raw, [
                'name' => $resolvedName,
                'alt_text' => $validated['alt_text'] ?? null,
                'source' => $validated['source'] ?? null,
                'tags' => $normalizedTags,
                'fallback_name' => pathinfo((string) $uploadedFile->getClientOriginalName(), PATHINFO_FILENAME),
                'max_width' => $validated['max_width'] ?? null,
                'max_height' => $validated['max_height'] ?? null,
                'xs_max_width' => $validated['xs_max_width'] ?? null,
                'xs_max_height' => $validated['xs_max_height'] ?? null,
                'quality' => $validated['quality'] ?? null,
            ]);

            if ($media === null) {
                return $this->storeErrorResponse($request, 'Das Bildformat wird nicht unterstuetzt oder konnte nicht verarbeitet werden.');
            }

            $items[] = $this->mediaPayload($media);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => count($items) === 1
                    ? 'Medium erfolgreich hochgeladen und optimiert.'
                    : count($items).' Medien erfolgreich hochgeladen und optimiert.',
                'item' => $items[0] ?? null,
                'items' => $items,
            ], 201);
        }

        return redirect()
            ->route('admin.media.index')
            ->with('success', count($items) === 1
                ? 'Medium wurde erfolgreich hochgeladen und als WebP erzeugt.'
                : count($items).' Medien wurden erfolgreich hochgeladen und als WebP erzeugt.');
    }

    public function importExternal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'name' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:320'],
        ]);

        $url = trim((string) $validated['url']);
        if (! $this->isAllowedExternalUrl($url)) {
            return response()->json([
                'message' => 'Diese externe URL darf nicht importiert werden.',
            ], 422);
        }

        $response = Http::timeout(15)
            ->withHeaders([
                'Accept' => 'image/*,*/*;q=0.8',
                'User-Agent' => config('app.name', 'Laravel').' Media Importer',
            ])
            ->get($url);

        if (! $response->ok()) {
            return response()->json([
                'message' => 'Das externe Bild konnte nicht geladen werden.',
            ], 422);
        }

        $contentType = (string) $response->header('Content-Type', '');
        if ($contentType !== '' && ! str_starts_with(strtolower($contentType), 'image/')) {
            return response()->json([
                'message' => 'Die externe URL liefert kein Bild zurueck.',
            ], 422);
        }

        $fallbackName = pathinfo((string) basename(parse_url($url, PHP_URL_PATH) ?: 'external-image'), PATHINFO_FILENAME);
        $media = $this->createMediaFromBinary($response->body(), [
            'name' => $validated['name'] ?? null,
            'alt_text' => $validated['alt_text'] ?? null,
            'source' => $validated['source'] ?? null,
            'tags' => $this->hasMediaTagsColumn()
                ? $this->normalizeTagsString($validated['tags'] ?? null)
                : [],
            'fallback_name' => $fallbackName,
        ]);

        if ($media === null) {
            return response()->json([
                'message' => 'Das externe Bild konnte nicht verarbeitet werden.',
            ], 422);
        }

        return response()->json([
            'message' => 'Externes Bild wurde importiert und lokal gespeichert.',
            'item' => $this->mediaPayload($media),
        ], 201);
    }

    private function storeErrorResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 422);
        }

        return redirect()
            ->route('admin.media.index')
            ->withInput()
            ->with('error', $message);
    }

    private function mediaPayload(Media $media): array
    {
        $variants = is_array($media->variants) ? $media->variants : [];
        $xsPath = data_get($variants, 'xs.path');

        return [
            'id' => $media->id,
            'name' => $media->name ?: pathinfo(basename($media->path), PATHINFO_FILENAME),
            'filename' => basename($media->path),
            'alt_text' => $media->alt_text,
            'source' => $media->source,
            'tags' => $this->hasMediaTagsColumn() && is_array($media->tags) ? array_values($media->tags) : [],
            'url' => $media->url,
            'preview_url' => $xsPath
                ? route('media.show', ['filename' => basename($xsPath)])
                : $media->url,
            'width' => $media->width,
            'height' => $media->height,
        ];
    }

    private function createMediaFromBinary(string $raw, array $options): ?Media
    {
        $settings = $this->mediaDefaults($options);
        $sourceImage = @imagecreatefromstring($raw);
        if ($sourceImage === false) {
            return null;
        }

        $diskName = 'public';
        $disk = Storage::disk($diskName);

        $srcWidth = imagesx($sourceImage);
        $srcHeight = imagesy($sourceImage);

        $originalResult = $this->resizeToWebp($sourceImage, $srcWidth, $srcHeight, $settings['max_width'], $settings['max_height'], $settings['quality']);
        $xsResult = $this->resizeToWebp($sourceImage, $srcWidth, $srcHeight, $settings['xs_max_width'], $settings['xs_max_height'], $settings['quality']);

        imagedestroy($sourceImage);

        if ($originalResult === null || $xsResult === null) {
            return null;
        }

        $baseName = $this->resolveBaseName(
            $options['name'] ?? null,
            $options['fallback_name'] ?? null
        );

        $folder = 'media/'.now()->format('Y/m');
        $token = Str::lower(Str::random(8));
        $originalPath = $folder.'/'.$baseName.'-'.$token.'.webp';
        $xsPath = $folder.'/'.$baseName.'-'.$token.'_xs.webp';

        $disk->put($originalPath, $originalResult['binary']);
        $disk->put($xsPath, $xsResult['binary']);

        $payload = [
            'disk' => $diskName,
            'path' => $originalPath,
            'name' => $options['name'] ?? null,
            'alt_text' => $options['alt_text'] ?? null,
            'source' => $options['source'] ?? null,
            'width' => $originalResult['width'],
            'height' => $originalResult['height'],
            'mime_type' => 'image/webp',
            'file_size' => $disk->size($originalPath),
            'variants' => [
                'original' => [
                    'path' => $originalPath,
                    'width' => $originalResult['width'],
                    'height' => $originalResult['height'],
                    'max_width' => $settings['max_width'],
                    'max_height' => $settings['max_height'],
                    'quality' => $settings['quality'],
                ],
                'xs' => [
                    'path' => $xsPath,
                    'width' => $xsResult['width'],
                    'height' => $xsResult['height'],
                    'max_width' => $settings['xs_max_width'],
                    'max_height' => $settings['xs_max_height'],
                    'quality' => $settings['quality'],
                ],
            ],
        ];

        if ($this->hasMediaTagsColumn()) {
            $payload['tags'] = $this->normalizeTagsArray($options['tags'] ?? []);
        }

        return Media::query()->create($payload);
    }

    private function mediaDefaults(array $overrides = []): array
    {
        return [
            'max_width' => max(1, (int) ($overrides['max_width'] ?? 1920)),
            'max_height' => max(1, (int) ($overrides['max_height'] ?? 800)),
            'xs_max_width' => max(1, (int) ($overrides['xs_max_width'] ?? 400)),
            'xs_max_height' => max(1, (int) ($overrides['xs_max_height'] ?? 600)),
            'quality' => max(1, min(100, (int) ($overrides['quality'] ?? 82))),
        ];
    }

    private function isAllowedExternalUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        }

        return true;
    }

    public function edit(Media $media): View
    {
        return view('pages.admin.media.edit', compact('media'));
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:320'],
            'focal_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'focal_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'crop_enabled' => ['nullable', 'boolean'],
            'crop_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'crop_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'crop_width' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
            'crop_height' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
        ]);

        $name = isset($validated['name']) ? trim((string) $validated['name']) : null;
        if ($name === '') {
            $name = null;
        }

        if ($name !== null) {
            $this->renameMediaFiles($media, $name);
        }

        $focalPoint = [
            'x' => $this->clampPercentage($validated['focal_x'] ?? data_get($media->focal_point, 'x', 50)),
            'y' => $this->clampPercentage($validated['focal_y'] ?? data_get($media->focal_point, 'y', 50)),
        ];

        $variants = is_array($media->variants) ? $media->variants : [];
        $cropEnabled = $request->boolean('crop_enabled');

        if ($cropEnabled) {
            $crop = [
                'x' => $this->clampPercentage($validated['crop_x'] ?? 0),
                'y' => $this->clampPercentage($validated['crop_y'] ?? 0),
                'width' => $this->clampPercentage($validated['crop_width'] ?? 100, 0.5),
                'height' => $this->clampPercentage($validated['crop_height'] ?? 100, 0.5),
            ];

            if (($crop['x'] + $crop['width']) > 100) {
                $crop['x'] = max(0, 100 - $crop['width']);
            }

            if (($crop['y'] + $crop['height']) > 100) {
                $crop['y'] = max(0, 100 - $crop['height']);
            }

            data_set($variants, 'crop', $crop);
            $media->variants = $variants;
            $this->applyCropAndRegenerateVariants($media, $crop);
            $variants = is_array($media->variants) ? $media->variants : $variants;
        } else {
            data_forget($variants, 'crop');
        }

        $updatePayload = [
            'name' => $name,
            'alt_text' => $validated['alt_text'] ?? null,
            'source' => $validated['source'] ?? null,
            'focal_point' => $focalPoint,
            'variants' => $variants,
        ];

        if ($this->hasMediaTagsColumn()) {
            $updatePayload['tags'] = $this->normalizeTagsString($validated['tags'] ?? null);
        }

        $media->update($updatePayload);

        return redirect()
            ->route('admin.media.edit', $media)
            ->with('success', 'Medium wurde aktualisiert.');
    }

    public function attachTag(Request $request, Media $media): JsonResponse
    {
        if (! $this->hasMediaTagsColumn()) {
            return response()->json([
                'message' => 'Tags stehen erst nach Ausfuehren der Migration zur Verfuegung.',
            ], 409);
        }

        $validated = $request->validate([
            'tag' => ['required', 'string', 'max:30'],
        ]);

        $tags = $this->normalizeTagsArray([
            $validated['tag'],
        ]);

        $media->update([
            'tags' => $tags,
        ]);

        return response()->json([
            'message' => 'Tag wurde fuer das Medium ersetzt.',
            'item' => $this->mediaPayload($media->fresh()),
        ]);
    }

    public function destroy(Media $media): RedirectResponse
    {
        $this->deleteMediaRecord($media);

        return redirect()
            ->route('admin.media.index')
            ->with('success', 'Medium wurde geloescht.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'media_ids' => ['required', 'array', 'min:1'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ]);

        $mediaItems = Media::query()
            ->whereIn('id', $validated['media_ids'])
            ->get();

        foreach ($mediaItems as $media) {
            $this->deleteMediaRecord($media);
        }

        $count = $mediaItems->count();

        return redirect()
            ->route('admin.media.index')
            ->with('success', $count === 1 ? '1 Medium wurde geloescht.' : $count.' Medien wurden geloescht.');
    }

    private function deleteMediaRecord(Media $media): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($media->disk);
        $paths = collect([
            $media->path,
            data_get($media->variants, 'original.path'),
            data_get($media->variants, 'xs.path'),
        ])
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->unique();

        foreach ($paths as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }

        $media->delete();
    }

    private function renameMediaFiles(Media $media, string $name): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($media->disk);
        $baseName = $this->resolveBaseName($name);

        $currentOriginalFilename = pathinfo($media->path, PATHINFO_FILENAME);
        $token = $this->extractToken($currentOriginalFilename);
        $currentBaseName = $this->extractBaseName($currentOriginalFilename);

        if ($baseName === $currentBaseName || $token === null) {
            return;
        }

        $directory = dirname($media->path);
        $directory = $directory === '.' ? '' : $directory;
        $prefix = $directory !== '' ? $directory.'/' : '';

        $newOriginalPath = $prefix.$baseName.'-'.$token.'.webp';
        $newXsPath = $prefix.$baseName.'-'.$token.'_xs.webp';

        if ($media->path !== $newOriginalPath && $disk->exists($media->path) && ! $disk->exists($newOriginalPath)) {
            $disk->move($media->path, $newOriginalPath);
        }

        $currentXsPath = data_get($media->variants, 'xs.path');
        if (is_string($currentXsPath) && $currentXsPath !== $newXsPath && $disk->exists($currentXsPath) && ! $disk->exists($newXsPath)) {
            $disk->move($currentXsPath, $newXsPath);
        }

        $variants = is_array($media->variants) ? $media->variants : [];
        data_set($variants, 'original.path', $newOriginalPath);
        data_set($variants, 'xs.path', $newXsPath);

        $media->path = $newOriginalPath;
        $media->variants = $variants;
        $media->file_size = $disk->exists($newOriginalPath) ? $disk->size($newOriginalPath) : $media->file_size;
    }

    private function resolveBaseName(?string $preferredName, ?string $fallbackName = null): string
    {
        $baseName = Str::slug(trim((string) $preferredName));

        if ($baseName === '') {
            $baseName = Str::slug((string) $fallbackName);
        }

        return $baseName !== '' ? $baseName : 'media';
    }

    private function extractToken(string $filename): ?string
    {
        if (! preg_match('/-([a-z0-9]+?)(?:_xs)?$/', $filename, $matches)) {
            return null;
        }

        return $matches[1] ?? null;
    }

    private function extractBaseName(string $filename): string
    {
        if (! preg_match('/^(.*)-[a-z0-9]+(?:_xs)?$/', $filename, $matches)) {
            return $filename;
        }

        return $matches[1] ?? $filename;
    }

    private function clampPercentage(float|int|string|null $value, float $min = 0.0): float
    {
        $number = is_numeric($value) ? (float) $value : 0.0;

        return max($min, min(100.0, round($number, 1)));
    }

    private function normalizeTagsString(?string $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return $this->normalizeTagsArray(explode(',', $value));
    }

    private function normalizeTagsArray(array $tags): array
    {
        return collect($tags)
            ->map(fn ($tag) => is_string($tag) ? trim($tag) : '')
            ->filter(fn ($tag) => $tag !== '')
            ->map(fn ($tag) => mb_strtolower($tag))
            ->unique()
            ->take(12)
            ->values()
            ->all();
    }

    private function hasMediaTagsColumn(): bool
    {
        if ($this->mediaTagsColumnExists !== null) {
            return $this->mediaTagsColumnExists;
        }

        return $this->mediaTagsColumnExists = Schema::hasColumn('media', 'tags');
    }

    private function applyCropAndRegenerateVariants(Media $media, array $crop): void
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($media->disk);
        if (! $disk->exists($media->path)) {
            return;
        }

        $raw = $disk->get($media->path);
        $sourceImage = @imagecreatefromstring($raw);
        if ($sourceImage === false) {
            return;
        }

        $srcWidth = imagesx($sourceImage);
        $srcHeight = imagesy($sourceImage);
        if ($srcWidth <= 0 || $srcHeight <= 0) {
            imagedestroy($sourceImage);
            return;
        }

        $cropX = (int) floor(($this->clampPercentage($crop['x'] ?? 0) / 100) * $srcWidth);
        $cropY = (int) floor(($this->clampPercentage($crop['y'] ?? 0) / 100) * $srcHeight);
        $cropWidth = (int) floor(($this->clampPercentage($crop['width'] ?? 100, 0.5) / 100) * $srcWidth);
        $cropHeight = (int) floor(($this->clampPercentage($crop['height'] ?? 100, 0.5) / 100) * $srcHeight);

        $cropX = max(0, min($cropX, $srcWidth - 1));
        $cropY = max(0, min($cropY, $srcHeight - 1));
        $cropWidth = max(1, min($cropWidth, $srcWidth - $cropX));
        $cropHeight = max(1, min($cropHeight, $srcHeight - $cropY));

        $cropCanvas = imagecreatetruecolor($cropWidth, $cropHeight);
        if ($cropCanvas === false) {
            imagedestroy($sourceImage);
            return;
        }

        imagealphablending($cropCanvas, false);
        imagesavealpha($cropCanvas, true);
        $transparent = imagecolorallocatealpha($cropCanvas, 0, 0, 0, 127);
        imagefilledrectangle($cropCanvas, 0, 0, $cropWidth, $cropHeight, $transparent);

        imagecopyresampled(
            $cropCanvas,
            $sourceImage,
            0,
            0,
            $cropX,
            $cropY,
            $cropWidth,
            $cropHeight,
            $cropWidth,
            $cropHeight
        );

        imagedestroy($sourceImage);

        $originalMaxWidth = (int) data_get($media->variants, 'original.max_width', 1920);
        $originalMaxHeight = (int) data_get($media->variants, 'original.max_height', 800);
        $xsMaxWidth = (int) data_get($media->variants, 'xs.max_width', 400);
        $xsMaxHeight = (int) data_get($media->variants, 'xs.max_height', 600);
        $quality = (int) data_get($media->variants, 'original.quality', 82);

        $originalResult = $this->resizeToWebp($cropCanvas, $cropWidth, $cropHeight, $originalMaxWidth, $originalMaxHeight, $quality);
        $xsResult = $this->resizeToWebp($cropCanvas, $cropWidth, $cropHeight, $xsMaxWidth, $xsMaxHeight, $quality);

        imagedestroy($cropCanvas);

        if ($originalResult === null) {
            return;
        }

        $originalPath = (string) data_get($media->variants, 'original.path', $media->path);
        $disk->put($media->path, $originalResult['binary']);

        if ($originalPath !== '' && $originalPath !== $media->path) {
            $disk->put($originalPath, $originalResult['binary']);
        }

        $xsPath = data_get($media->variants, 'xs.path');
        if (is_string($xsPath) && $xsPath !== '' && $xsResult !== null) {
            $disk->put($xsPath, $xsResult['binary']);
        }

        $variants = is_array($media->variants) ? $media->variants : [];
        data_set($variants, 'original.path', $media->path);
        data_set($variants, 'original.width', $originalResult['width']);
        data_set($variants, 'original.height', $originalResult['height']);

        if (is_string($xsPath) && $xsPath !== '' && $xsResult !== null) {
            data_set($variants, 'xs.width', $xsResult['width']);
            data_set($variants, 'xs.height', $xsResult['height']);
        }

        $media->width = $originalResult['width'];
        $media->height = $originalResult['height'];
        $media->file_size = $disk->size($media->path);
        $media->variants = $variants;
    }

    /**
     * @return array{binary: string, width: int, height: int}|null
     */
    private function resizeToWebp(\GdImage $source, int $srcWidth, int $srcHeight, int $maxWidth, int $maxHeight, int $quality): ?array
    {
        if ($srcWidth <= 0 || $srcHeight <= 0) {
            return null;
        }

        $ratio = min($maxWidth / $srcWidth, $maxHeight / $srcHeight, 1);
        $targetWidth = max(1, (int) floor($srcWidth * $ratio));
        $targetHeight = max(1, (int) floor($srcHeight * $ratio));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($canvas === false) {
            return null;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);

        $resampled = imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $srcWidth,
            $srcHeight
        );

        if (! $resampled) {
            imagedestroy($canvas);
            return null;
        }

        ob_start();
        $encoded = imagewebp($canvas, null, $quality);
        $binary = (string) ob_get_clean();
        imagedestroy($canvas);

        if (! $encoded || $binary === '') {
            return null;
        }

        return [
            'binary' => $binary,
            'width' => $targetWidth,
            'height' => $targetHeight,
        ];
    }
}
