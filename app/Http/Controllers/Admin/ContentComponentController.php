<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreContentComponentRequest;
use App\Http\Requests\Admin\UpdateContentComponentRequest;
use App\Models\ContentComponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ContentComponentController extends Controller
{
    public function exportZip(Request $request): RedirectResponse|BinaryFileResponse
    {
        $validated = $request->validate([
            'component_ids' => ['required', 'array', 'min:1'],
            'component_ids.*' => ['integer', 'exists:content_components,id'],
        ]);

        if (!class_exists(ZipArchive::class)) {
            return redirect()
                ->route('admin.components.index')
                ->with('error', 'ZIP Export nicht verfuegbar: ZipArchive Erweiterung fehlt.');
        }

        $ids = collect($validated['component_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $components = ContentComponent::query()
            ->whereIn('id', $ids)
            ->orderBy('title')
            ->get(['id', 'name', 'title', 'description', 'tags', 'content', 'css', 'js']);

        if ($components->isEmpty()) {
            return redirect()
                ->route('admin.components.index')
                ->with('error', 'Keine gueltigen Komponenten fuer den ZIP Export gefunden.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'components_zip_');
        if ($zipPath === false) {
            return redirect()
                ->route('admin.components.index')
                ->with('error', 'ZIP Export fehlgeschlagen: Temporaere Datei konnte nicht erstellt werden.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);

            return redirect()
                ->route('admin.components.index')
                ->with('error', 'ZIP Export fehlgeschlagen: Archiv konnte nicht erzeugt werden.');
        }

        $manifest = [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'count' => $components->count(),
            'components' => [],
        ];

        foreach ($components as $component) {
            $payload = [
                'id' => $component->id,
                'name' => $component->name,
                'title' => $component->title,
                'description' => $component->description,
                'tags' => $component->tags ?? [],
                'content' => $component->content,
                'css' => $component->css,
                'js' => $component->js,
            ];

            $safeName = Str::slug((string) $component->name, '-');
            if ($safeName === '') {
                $safeName = 'component-'.$component->id;
            }

            $fileName = $safeName.'.json';
            $zip->addFromString($fileName, (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $manifest['components'][] = [
                'id' => $component->id,
                'name' => $component->name,
                'file' => $fileName,
            ];
        }

        $zip->addFromString('manifest.json', (string) json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $zip->close();

        $downloadName = 'bitbase-components_'.now()->format('Y-m-d').'.zip';

        return response()->download($zipPath, $downloadName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function export(): StreamedResponse
    {
        $components = ContentComponent::query()
            ->orderBy('title')
            ->get(['name', 'title', 'description', 'tags', 'content', 'css', 'js'])
            ->map(function (ContentComponent $component) {
                return [
                    'name' => $component->name,
                    'title' => $component->title,
                    'description' => $component->description,
                    'tags' => $component->tags ?? [],
                    'content' => $component->content,
                    'css' => $component->css,
                    'js' => $component->js,
                ];
            })
            ->values();

        $payload = [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'components' => $components,
        ];

        $fileName = 'bitbase-components_'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $fileName, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'components_files' => ['required', 'array', 'min:1', 'max:10'],
            'components_files.*' => ['file', 'max:10240', 'mimetypes:application/json,text/plain,application/zip,application/x-zip-compressed,multipart/x-zip'],
        ]);

        $imported = 0;
        $skipped = 0;
        $foundPayload = false;
        $zipSupported = class_exists(ZipArchive::class);

        foreach ($validated['components_files'] as $file) {
            $extension = strtolower((string) $file->getClientOriginalExtension());

            if ($extension === 'zip') {
                if (!$zipSupported) {
                    continue;
                }

                $zip = new ZipArchive();
                if ($zip->open($file->getRealPath()) !== true) {
                    $skipped += 1;
                    continue;
                }

                for ($i = 0; $i < $zip->numFiles; $i += 1) {
                    $entryName = $zip->getNameIndex($i);
                    if (!is_string($entryName) || str_ends_with($entryName, '/')) {
                        continue;
                    }

                    if (strtolower(pathinfo($entryName, PATHINFO_EXTENSION)) !== 'json') {
                        continue;
                    }

                    $raw = $zip->getFromIndex($i);
                    if (!is_string($raw)) {
                        $skipped += 1;
                        continue;
                    }

                    $decoded = json_decode($raw, true);
                    if (!is_array($decoded)) {
                        $skipped += 1;
                        continue;
                    }

                    $items = $decoded['components'] ?? $decoded;
                    if (!is_array($items)) {
                        $skipped += 1;
                        continue;
                    }

                    if ($this->looksLikeComponentPayload($items)) {
                        $items = [$items];
                    }

                    $foundPayload = true;
                    $this->importComponentItems($items, $imported, $skipped);
                }

                $zip->close();
                continue;
            }

            $raw = file_get_contents($file->getRealPath());
            $decoded = json_decode((string) $raw, true);
            if (!is_array($decoded)) {
                $skipped += 1;
                continue;
            }

            $items = $decoded['components'] ?? $decoded;
            if (!is_array($items)) {
                $skipped += 1;
                continue;
            }

            if ($this->looksLikeComponentPayload($items)) {
                $items = [$items];
            }

            $foundPayload = true;
            $this->importComponentItems($items, $imported, $skipped);
        }

        if (!$foundPayload) {
            return redirect()
                ->route('admin.components.index')
                ->with('error', 'Import fehlgeschlagen: Keine gueltigen JSON Komponenten gefunden.');
        }

        $zipHint = !$zipSupported
            ? ' Hinweis: ZIP Import wurde uebersprungen (ZipArchive Erweiterung fehlt).'
            : '';

        return redirect()
            ->route('admin.components.index')
            ->with('success', "Import abgeschlossen: {$imported} Komponente(n) importiert, {$skipped} uebersprungen.".$zipHint);
    }

    /**
     * @param array<int, mixed> $items
     */
    private function importComponentItems(array $items, int &$imported, int &$skipped): void
    {
        foreach ($items as $item) {
            if (!is_array($item)) {
                $skipped += 1;
                continue;
            }

            $name = Str::slug((string) ($item['name'] ?? ''), '-');
            $title = trim((string) ($item['title'] ?? ''));
            $content = (string) ($item['content'] ?? '');

            if ($name === '' || $title === '' || $content === '') {
                $skipped += 1;
                continue;
            }

            $tags = $item['tags'] ?? [];
            if (is_string($tags)) {
                $tags = array_values(array_filter(array_map('trim', explode(',', $tags))));
            }

            if (!is_array($tags)) {
                $tags = [];
            }

            $tags = collect($tags)
                ->filter(fn ($tag) => is_string($tag) && trim($tag) !== '')
                ->map(fn ($tag) => mb_strtolower(trim($tag)))
                ->unique()
                ->values()
                ->all();

            ContentComponent::updateOrCreate(
                ['name' => $name],
                [
                    'title' => $title,
                    'description' => isset($item['description']) ? (string) $item['description'] : null,
                    'tags' => $tags,
                    'content' => $content,
                    'css' => isset($item['css']) ? (string) $item['css'] : null,
                    'js' => isset($item['js']) ? (string) $item['js'] : null,
                ]
            );

            $imported += 1;
        }
    }

    /**
     * @param array<string, mixed>|array<int, mixed> $payload
     */
    private function looksLikeComponentPayload(array $payload): bool
    {
        return isset($payload['name'], $payload['title'], $payload['content']);
    }

    public function index(): View
    {
        $components = ContentComponent::query()
            ->orderBy('title')
            ->paginate(20);

        $availableTags = ContentComponent::query()
            ->pluck('tags')
            ->filter(fn ($tags) => is_array($tags))
            ->flatMap(fn ($tags) => $tags)
            ->filter(fn ($tag) => is_string($tag) && $tag !== '')
            ->map(fn ($tag) => mb_strtolower(trim($tag)))
            ->unique()
            ->sort()
            ->values();

        return view('pages.admin.components.index', compact('components', 'availableTags'));
    }

    public function create(): View
    {
        return view('pages.admin.components.create');
    }

    public function store(StoreContentComponentRequest $request): RedirectResponse
    {
        $component = ContentComponent::create($this->normalizePayload($request->validated()));

        return redirect()
            ->route('admin.components.edit', $component)
            ->with('success', 'Komponente wurde erfolgreich erstellt.');
    }

    public function edit(ContentComponent $component): View
    {
        return view('pages.admin.components.edit', compact('component'));
    }

    public function update(UpdateContentComponentRequest $request, ContentComponent $component): RedirectResponse
    {
        $component->update($this->normalizePayload($request->validated()));

        return redirect()
            ->route('admin.components.edit', $component)
            ->with('success', 'Komponente wurde erfolgreich aktualisiert.');
    }

    public function destroy(ContentComponent $component): RedirectResponse
    {
        $component->delete();

        return redirect()
            ->route('admin.components.index')
            ->with('success', 'Komponente wurde erfolgreich geloescht.');
    }

    private function normalizePayload(array $validated): array
    {
        $validated['name'] = Str::slug((string) ($validated['name'] ?? ''), '-');

        return $validated;
    }
}