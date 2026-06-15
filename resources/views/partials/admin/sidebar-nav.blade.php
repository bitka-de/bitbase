<nav class="admin-sidebar-nav">
    <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
        <x-heroicon-o-squares-2x2 class="admin-nav-icon" aria-hidden="true" />
        <span>Dashboard</span>
    </a>
    <a href="#" class="admin-sidebar-link">
        <x-heroicon-o-cube class="admin-nav-icon" aria-hidden="true" />
        <span>Komponenten</span>
    </a>
    <a href="#" class="admin-sidebar-link">
        <x-heroicon-o-document-duplicate class="admin-nav-icon" aria-hidden="true" />
        <span>Seiten</span>
    </a>
    <a href="#" class="admin-sidebar-link">
        <x-heroicon-o-photo class="admin-nav-icon" aria-hidden="true" />
        <span>Medien</span>
    </a>
    <a href="#" class="admin-sidebar-link">
        <x-heroicon-o-users class="admin-nav-icon" aria-hidden="true" />
        <span>Benutzer</span>
    </a>
    <a href="#" class="admin-sidebar-link">
        <x-heroicon-o-cog-6-tooth class="admin-nav-icon" aria-hidden="true" />
        <span>Einstellungen</span>
    </a>
</nav>