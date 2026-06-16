<?php

namespace App\View\Components;

use App\Models\Page;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumbs extends Component
{
    /**
     * @var array<int, Page>
     */
    public array $items;

    public function __construct(public Page $page)
    {
        $items = [];
        $cursor = $this->page;

        while ($cursor !== null) {
            $items[] = $cursor;
            $cursor = $cursor->parent;
        }

        $this->items = array_reverse($items);
    }

    public function render(): View|Closure|string
    {
        return view('components.breadcrumbs');
    }
}
