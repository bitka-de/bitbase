<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\RedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageShowController extends Controller
{
    /**
     * @return array<string, string>
     */
    private function templateViewMap(): array
    {
        return [
            'default' => 'pages.templates.default',
            'focused' => 'pages.templates.focused',
            'story' => 'pages.templates.story',
        ];
    }

    public function __invoke(Request $request, string $slugPath, RedirectService $redirectService): View|RedirectResponse
    {
        $path = '/'.trim($slugPath, '/');
        $redirect = $redirectService->resolve($path);

        if ($redirect !== null) {
            $target = $redirect->new_url ?: '/';

            return redirect($target, $redirect->status_code);
        }

        $segments = explode('/', trim($slugPath, '/'));
        $slug = (string) end($segments);

        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        abort_if($page->status->value !== 'published', 404);

        $templates = $this->templateViewMap();
        $templateKey = (string) ($page->template ?: 'default');
        $view = $templates[$templateKey] ?? $templates['default'];

        return view($view, compact('page'));
    }
}
