@php
$userName = auth()->user()->name;
$userInitials = collect(explode(' ', trim($userName)))
->filter()
->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
->take(2)
->implode('');
@endphp

<header class="admin-header">
  <div class="admin-header-left">
    <div class="admin-header-title-stack">
      <h1 class="admin-header-title">{{ $title ?? 'Dashboard' }}</h1>

      @if (! empty($subtitle))
      <p class="admin-header-subtitle">{{ $subtitle }}</p>
      @endif
    </div>

    <div class="admin-header-meta-row">
      <span class="admin-header-date">{{ now()->format('d.m.Y') }}</span>
      <span class="admin-header-meta-dot" aria-hidden="true"></span>
      <span class="admin-header-context">Adminbereich</span>
    </div>
  </div>

  <div class="admin-header-right admin-header-actions" aria-label="Header Aktionen">
    <a href="{{ route('home') }}" class="admin-header-link admin-header-icon-btn" title="Zum Frontend" aria-label="Zum Frontend">
      <x-heroicon-o-globe-alt class="admin-nav-icon" aria-hidden="true" />
    </a>

    <a href="{{ route('admin.dashboard') }}" class="admin-header-link admin-header-admin-btn" aria-current="page">
      <x-heroicon-o-squares-2x2 class="admin-nav-icon" aria-hidden="true" />
      <span>Admin</span>
    </a>

    <div class="admin-user-badge">
      <span class="admin-user-avatar" aria-hidden="true">{{ $userInitials }}</span>
      <span class="admin-user-meta">{{ $userName }}</span>
    </div>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-ghost admin-logout-btn admin-header-icon-btn" title="Logout" aria-label="Logout">
        <x-heroicon-o-arrow-left-on-rectangle class="admin-nav-icon" aria-hidden="true" />
      </button>
    </form>
  </div>
</header>