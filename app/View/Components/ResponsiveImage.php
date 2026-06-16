<?php

namespace App\View\Components;

use App\Models\Media;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ResponsiveImage extends Component
{
    public function __construct(
        public Media $media,
        public string $class = '',
        public string $loading = 'lazy'
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('components.responsive-image');
    }
}
