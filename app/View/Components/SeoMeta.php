<?php

namespace App\View\Components;

use App\Models\Page;
use App\Services\SchemaService;
use App\Services\SeoMetaService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SeoMeta extends Component
{
    /**
     * @var array<string, mixed>
     */
    public array $meta;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $schemas;

    public function __construct(public ?Page $page = null)
    {
        $this->meta = app(SeoMetaService::class)->build($this->page);
        $this->schemas = $this->page ? app(SchemaService::class)->forPage($this->page) : [];
    }

    public function render(): View|Closure|string
    {
        return view('components.seo-meta');
    }
}
