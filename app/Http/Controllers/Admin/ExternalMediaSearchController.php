<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExternalMediaSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:120'],
            'provider' => ['nullable', 'string', 'in:openverse,wikimedia,unsplash'],
            'page' => ['nullable', 'integer', 'min:1', 'max:20'],
            'per_page' => ['nullable', 'integer', 'min:6', 'max:24'],
        ]);

        $query = trim((string) $validated['q']);
        $provider = (string) ($validated['provider'] ?? 'openverse');
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 12);

        try {
            return response()->json(match ($provider) {
                'unsplash' => $this->searchUnsplash($query, $page, $perPage),
                'wikimedia' => $this->searchWikimediaCommons($query, $page, $perPage),
                default => $this->searchOpenverse($query, $page, $perPage),
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'provider' => $provider,
                'items' => [],
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                ],
                'error' => 'Die externe Bildsuche ist aktuell nicht erreichbar.',
            ], 502);
        }
    }

    private function searchOpenverse(string $query, int $page, int $perPage): array
    {
        $response = Http::timeout(10)
            ->acceptJson()
            ->get('https://api.openverse.org/v1/images/', [
                'q' => $query,
                'page' => $page,
                'page_size' => $perPage,
            ]);

        if (! $response->ok()) {
            return [
                'provider' => 'openverse',
                'items' => [],
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                ],
                'error' => 'Openverse konnte keine Ergebnisse liefern.',
            ];
        }

        $payload = $response->json();
        $results = collect(data_get($payload, 'results', []))
            ->filter(fn ($item) => is_array($item) && ! empty($item['url']))
            ->map(function (array $item): array {
                $creator = is_string(data_get($item, 'creator')) ? trim((string) data_get($item, 'creator')) : '';
                $license = is_string(data_get($item, 'license')) ? trim((string) data_get($item, 'license')) : '';

                return [
                    'id' => (string) (data_get($item, 'id') ?? ''),
                    'title' => is_string(data_get($item, 'title')) && data_get($item, 'title') !== ''
                        ? (string) data_get($item, 'title')
                        : ($creator !== '' ? 'Foto von '.$creator : 'Openverse Bild'),
                    'alt_text' => is_string(data_get($item, 'title')) ? (string) data_get($item, 'title') : '',
                    'url' => (string) data_get($item, 'url'),
                    'preview_url' => (string) (data_get($item, 'thumbnail') ?: data_get($item, 'url')),
                    'width' => is_numeric(data_get($item, 'width')) ? (int) data_get($item, 'width') : null,
                    'height' => is_numeric(data_get($item, 'height')) ? (int) data_get($item, 'height') : null,
                    'source' => 'Openverse'.($creator !== '' ? ' · '.$creator : ''),
                    'author' => $creator,
                    'license' => $license,
                    'attribution_url' => is_string(data_get($item, 'foreign_landing_url')) ? (string) data_get($item, 'foreign_landing_url') : '',
                ];
            })
            ->values();

        return [
            'provider' => 'openverse',
            'items' => $results,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int) data_get($payload, 'result_count', $results->count()),
            ],
            'error' => null,
        ];
    }

    private function searchUnsplash(string $query, int $page, int $perPage): array
    {
        $accessKey = (string) config('services.unsplash.access_key', '');
        if ($accessKey === '') {
            return [
                'provider' => 'unsplash',
                'items' => [],
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                ],
                'error' => 'Unsplash ist noch nicht konfiguriert. Bitte UNSPLASH_ACCESS_KEY in der .env setzen.',
            ];
        }

        $response = Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Client-ID '.$accessKey,
                'Accept-Version' => 'v1',
            ])
            ->get('https://api.unsplash.com/search/photos', [
                'query' => $query,
                'page' => $page,
                'per_page' => $perPage,
                'content_filter' => 'high',
            ]);

        if (! $response->ok()) {
            return [
                'provider' => 'unsplash',
                'items' => [],
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                ],
                'error' => 'Unsplash konnte keine Ergebnisse liefern.',
            ];
        }

        $payload = $response->json();
        $utmSource = rawurlencode((string) config('services.unsplash.app_name', config('app.name', 'Laravel')));

        $results = collect(data_get($payload, 'results', []))
            ->filter(fn ($item) => is_array($item) && is_string(data_get($item, 'urls.regular')))
            ->map(function (array $item) use ($utmSource): array {
                $author = trim((string) data_get($item, 'user.name', ''));
                $description = trim((string) (data_get($item, 'alt_description') ?: data_get($item, 'description') ?: ''));
                $landingUrl = trim((string) data_get($item, 'links.html', ''));
                $attributionUrl = $landingUrl !== ''
                    ? $landingUrl.(str_contains($landingUrl, '?') ? '&' : '?').'utm_source='.$utmSource.'&utm_medium=referral'
                    : '';

                return [
                    'id' => (string) (data_get($item, 'id') ?? ''),
                    'title' => $description !== ''
                        ? $description
                        : ($author !== '' ? 'Foto von '.$author : 'Unsplash Bild'),
                    'alt_text' => $description,
                    'url' => (string) data_get($item, 'urls.regular'),
                    'preview_url' => (string) (data_get($item, 'urls.small') ?: data_get($item, 'urls.thumb') ?: data_get($item, 'urls.regular')),
                    'width' => is_numeric(data_get($item, 'width')) ? (int) data_get($item, 'width') : null,
                    'height' => is_numeric(data_get($item, 'height')) ? (int) data_get($item, 'height') : null,
                    'source' => 'Unsplash'.($author !== '' ? ' · '.$author : ''),
                    'author' => $author,
                    'license' => 'Unsplash License',
                    'attribution_url' => $attributionUrl,
                ];
            })
            ->values();

        return [
            'provider' => 'unsplash',
            'items' => $results,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int) data_get($payload, 'total', $results->count()),
            ],
            'error' => null,
        ];
    }

    private function searchWikimediaCommons(string $query, int $page, int $perPage): array
    {
        $response = Http::timeout(10)
            ->acceptJson()
            ->get('https://commons.wikimedia.org/w/api.php', [
                'action' => 'query',
                'format' => 'json',
                'generator' => 'search',
                'gsrsearch' => $query,
                'gsrnamespace' => 6,
                'gsrlimit' => $perPage,
                'gsroffset' => max(0, ($page - 1) * $perPage),
                'prop' => 'imageinfo',
                'iiprop' => 'url|size|mime|extmetadata',
                'iiurlwidth' => 640,
            ]);

        if (! $response->ok()) {
            return [
                'provider' => 'wikimedia',
                'items' => [],
                'meta' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                ],
                'error' => 'Wikimedia Commons konnte keine Ergebnisse liefern.',
            ];
        }

        $payload = $response->json();
        $pages = collect(data_get($payload, 'query.pages', []));

        $results = $pages
            ->filter(fn ($item) => is_array($item) && is_array(data_get($item, 'imageinfo.0')))
            ->map(function (array $item): array {
                $imageInfo = data_get($item, 'imageinfo.0', []);
                $metadata = is_array(data_get($imageInfo, 'extmetadata')) ? data_get($imageInfo, 'extmetadata') : [];
                $author = $this->cleanWikimediaMetadata(data_get($metadata, 'Artist.value', ''));
                $license = $this->cleanWikimediaMetadata(data_get($metadata, 'LicenseShortName.value', ''));
                $title = $this->cleanWikimediaMetadata(data_get($metadata, 'ObjectName.value', ''));
                $description = $this->cleanWikimediaMetadata(data_get($metadata, 'ImageDescription.value', ''));
                $fileTitle = is_string(data_get($item, 'title')) ? (string) data_get($item, 'title') : 'Wikimedia Commons Bild';
                $displayTitle = $title !== '' ? $title : preg_replace('/^File:/', '', $fileTitle);

                return [
                    'id' => (string) (data_get($item, 'pageid') ?? data_get($item, 'title') ?? ''),
                    'title' => $displayTitle !== '' ? $displayTitle : 'Wikimedia Commons Bild',
                    'alt_text' => $description !== '' ? $description : $displayTitle,
                    'url' => (string) data_get($imageInfo, 'url'),
                    'preview_url' => (string) (data_get($imageInfo, 'thumburl') ?: data_get($imageInfo, 'url')),
                    'width' => is_numeric(data_get($imageInfo, 'width')) ? (int) data_get($imageInfo, 'width') : null,
                    'height' => is_numeric(data_get($imageInfo, 'height')) ? (int) data_get($imageInfo, 'height') : null,
                    'source' => 'Wikimedia Commons'.($author !== '' ? ' · '.$author : ''),
                    'author' => $author,
                    'license' => $license,
                    'attribution_url' => 'https://commons.wikimedia.org/wiki/'.rawurlencode($fileTitle),
                ];
            })
            ->values();

        return [
            'provider' => 'wikimedia',
            'items' => $results,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $results->count(),
            ],
            'error' => null,
        ];
    }

    private function cleanWikimediaMetadata(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }
}
