<header class="navbar-app">
    <button type="button" class="icon-btn" data-sidebar-toggle aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
    </button>

    <button type="button" class="navbar-search-trigger" data-search-open aria-label="Open search">
        <i class="bi bi-search me-1 me-md-2"></i>
        <span class="d-none d-sm-inline">Search anything...</span>
        <kbd class="search-kbd d-none d-md-inline-block"><i class="bi bi-command"></i>K</kbd>
    </button>

    <div class="nav-actions">

        <div class="dropdown">
            <button type="button" class="icon-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                @if ($unreadNotifications > 0)
                    <span class="notif-badge">{{ $unreadNotifications > 99 ? '99+' : ($unreadNotifications > 9 ? '9+' : $unreadNotifications) }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end notif-menu">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <span class="fw-600">Notifications</span>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="notif-sound-toggle icon-btn-sm" id="notifSoundToggle" title="Toggle notification sound" aria-label="Toggle sound">
                            <i class="bi bi-volume-up"></i>
                        </button>
                        @if ($unreadNotifications > 0)
                            <a href="{{ route('notifications.read-all') }}" class="fs-xs text-primary text-decoration-none">Mark all read</a>
                        @endif
                    </div>
                </div>
                <div class="notif-scroll">
                    @forelse ($recentNotifications as $notification)
                        @php
                            $data = (array) $notification->data;
                            $color = $data['color'] ?? 'primary';
                            $icon = $data['icon'] ?? 'bi-bell';
                        @endphp
                        <a class="dropdown-item notif-item {{ $notification->read_at ? '' : 'unread' }}"
                           href="{{ route('notifications.read', $notification->id) }}">
                            <span class="notif-icon bg-{{ $color }}-soft text-{{ $color }}">
                                <i class="bi {{ $icon }}"></i>
                            </span>
                            <span class="d-flex flex-column min-w-0">
                                <span class="fw-500 text-body text-truncate">{{ $data['title'] ?? 'Notification' }}</span>
                                @if (! empty($data['message']))
                                    <small class="text-muted text-truncate">{{ $data['message'] }}</small>
                                @endif
                                <small class="text-muted fs-xs">{{ $notification->created_at->diffForHumans() }}</small>
                            </span>
                        </a>
                    @empty
                        <div class="text-center py-4 px-3">
                            <i class="bi bi-bell-slash fs-3 text-muted d-block mb-2"></i>
                            <span class="text-muted fs-sm">No notifications yet</span>
                        </div>
                    @endforelse
                </div>
                <div class="dropdown-divider m-0"></div>
                <a class="dropdown-item justify-content-center py-2 fw-500 text-primary" href="{{ route('notifications.index') }}">
                    <i class="bi bi-list-check me-1"></i> View all notifications
                </a>
            </div>
        </div>

        <button type="button" class="icon-btn" data-theme-toggle="toggle" aria-label="Toggle theme" title="Toggle Light/Dark Theme">
            <i class="bi bi-sun-fill theme-icon-light"></i>
            <i class="bi bi-moon-stars-fill theme-icon-dark"></i>
        </button>

        <button type="button" class="icon-btn d-none d-sm-inline-flex" data-fullscreen aria-label="Toggle fullscreen">
            <i class="bi bi-arrows-fullscreen"></i>
        </button>

        <div class="dropdown">
            <button type="button" class="profile-trigger" data-bs-toggle="dropdown" aria-label="Account menu">
                @if ($avatar = auth()->user()->avatar_url)
                    <img src="{{ $avatar }}" class="avatar-sm" alt="{{ auth()->user()->name }}">
                @else
                    <span class="avatar-fallback sm">{{ initials(auth()->user()->name) }}</span>
                @endif
                <span class="d-none d-md-block profile-meta">
                    <span class="profile-name d-block">{{ auth()->user()->name }}</span>
                    <span class="profile-role d-block">{{ auth()->user()->roles->first()?->name ?? 'Member' }}</span>
                </span>
                <i class="bi bi-chevron-down fs-xs text-muted d-none d-md-block"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" style="min-width: 220px;">
                <div class="dropdown-header px-3 py-2">
                    <div class="fw-600 text-body">{{ auth()->user()->name }}</div>
                    <small class="text-muted">{{ auth()->user()->email }}</small>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('profile.show') }}">
                    <i class="bi bi-person"></i> My Profile
                </a>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="bi bi-sliders"></i> Account Settings
                </a>
                <a class="dropdown-item" href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell"></i> Notifications
                    @if ($unreadNotifications > 0)
                        <span class="badge badge-soft-primary ms-1">{{ $unreadNotifications }}</span>
                    @endif
                </a>
                @can('settings.edit')
                    <a class="dropdown-item" href="{{ route('settings.general') }}">
                        <i class="bi bi-gear"></i> System Settings
                    </a>
                @endcan
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger w-100">
                        <i class="bi bi-box-arrow-right"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
