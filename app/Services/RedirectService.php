<?php

namespace App\Services;

use App\Models\Redirect;
use Illuminate\Support\Str;

class RedirectService
{
    public function createPermanent(string $oldUrl, string $newUrl): Redirect
    {
        return Redirect::query()->updateOrCreate(
            ['old_url' => $this->normalizePath($oldUrl)],
            [
                'new_url' => $this->normalizePath($newUrl),
                'status_code' => 301,
                'is_active' => true,
            ]
        );
    }

    public function resolve(string $path): ?Redirect
    {
        return Redirect::query()
            ->where('old_url', $this->normalizePath($path))
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return list<string>
     */
    public function detectChain(string $startPath, int $limit = 8): array
    {
        $visited = [];
        $current = $this->normalizePath($startPath);

        for ($i = 0; $i < $limit; $i++) {
            if (in_array($current, $visited, true)) {
                break;
            }

            $visited[] = $current;
            $redirect = $this->resolve($current);

            if ($redirect === null || empty($redirect->new_url)) {
                break;
            }

            $current = $this->normalizePath($redirect->new_url);
        }

        return $visited;
    }

    private function normalizePath(string $path): string
    {
        $trimmed = trim($path);

        if ($trimmed === '') {
            return '/';
        }

        if (! Str::startsWith($trimmed, '/')) {
            $trimmed = '/'.$trimmed;
        }

        return '/'.trim($trimmed, '/');
    }
}
