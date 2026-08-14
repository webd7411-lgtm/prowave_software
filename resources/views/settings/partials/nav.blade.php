@php
    $sections = [
        ['route' => 'settings.general', 'icon' => 'bi-sliders', 'label' => 'General'],
        ['route' => 'settings.profile', 'icon' => 'bi-building', 'label' => 'Company Profile'],
        ['route' => 'settings.branding', 'icon' => 'bi-palette', 'label' => 'Branding'],
        ['route' => 'settings.email', 'icon' => 'bi-envelope-at', 'label' => 'Email Settings'],
        ['route' => 'settings.notifications', 'icon' => 'bi-bell', 'label' => 'Notifications'],
        ['route' => 'settings.preferences', 'icon' => 'bi-gear-wide-connected', 'label' => 'System Preferences'],
        ['route' => 'settings.leave-types', 'icon' => 'bi-door-open', 'label' => 'Leave Types'],
        ['route' => 'settings.security', 'icon' => 'bi-shield-lock', 'label' => 'Security'],
    ];
@endphp

<nav class="nav flex-column settings-nav gap-1">
    <a class="nav-link" href="{{ URL::previous() !== url()->current() ? URL::previous() : route('dashboard') }}">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <div class="settings-nav-divider"></div>
    @foreach ($sections as $section)
        <a class="nav-link {{ request()->routeIs($section['route']) ? 'active' : '' }}" href="{{ route($section['route']) }}">
            <i class="bi {{ $section['icon'] }}"></i> {{ $section['label'] }}
        </a>
    @endforeach
    <div class="settings-nav-divider"></div>
    <a class="nav-link" href="{{ route('admin.users.index') }}">
        <i class="bi bi-people"></i> Users & Roles
    </a>
    <a class="nav-link" href="{{ route('admin.roles.index') }}">
        <i class="bi bi-shield-check"></i> Permissions
    </a>
    <a class="nav-link" href="{{ route('admin.departments.index') }}">
        <i class="bi bi-diagram-3"></i> Departments
    </a>
    <a class="nav-link" href="{{ route('hr.designations.index') }}">
        <i class="bi bi-briefcase"></i> Designations
    </a>
    <a class="nav-link" href="{{ route('hr.shifts.index') }}">
        <i class="bi bi-clock-history"></i> Work Shifts
    </a>
    <a class="nav-link" href="{{ route('finance.categories.index') }}">
        <i class="bi bi-tags"></i> Payment Methods
    </a>
    <a class="nav-link" href="{{ route('finance.categories.index') }}">
        <i class="bi bi-receipt"></i> Expense Categories
    </a>
    <a class="nav-link" href="{{ route('finance.categories.index') }}">
        <i class="bi bi-arrow-down-circle"></i> Income Categories
    </a>
</nav>
