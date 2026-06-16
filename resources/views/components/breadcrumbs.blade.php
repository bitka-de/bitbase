<nav aria-label="Breadcrumb">
    <ol style="display: inline-flex; flex-wrap: wrap; gap: 0.5rem; list-style: none; padding: 0; margin: 0 0 0.8rem 0; color: var(--muted); font-size: 0.9rem;">
        @foreach ($items as $item)
            <li>
                <a href="{{ $item->public_url }}" class="soft-link">{{ $item->title }}</a>
            </li>
            @if (! $loop->last)
                <li aria-hidden="true">/</li>
            @endif
        @endforeach
    </ol>
</nav>
