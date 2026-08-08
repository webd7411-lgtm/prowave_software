<header class="navbar-app">
    <button type="button" class="icon-btn" data-sidebar-toggle aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
    </button>

    <div class="navbar-search position-relative">
        <i class="bi bi-search search-icon"></i>
        <input type="search" class="form-control" placeholder="Search anything..." aria-label="Search">
    </div>

    <div class="nav-actions">

        <div class="dropdown">
            <button type="button" class="icon-btn" data-bs-toggle="dropdown" aria-label="Notifications">
                <i class="bi bi-bell"></i>
                <span class="dot"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end" style="min-width: 320px;">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    Notifications
                    <span class="badge badge-soft-primary">3</span>
                </div>
                <a class="dropdown-item" href="javascript:void(0)">
                    <div>
                        <div class="fw-500">New project assigned</div>
                        <small class="text-muted">5 minutes ago</small>
                    </div>
                </a>
                <a class="dropdown-item" href="javascript:void(0)">
                    <div>
                        <div class="fw-500">Invoice #INV-0012 paid</div>
                        <small class="text-muted">1 hour ago</small>
                    </div>
                </a>
                <a class="dropdown-item" href="javascript:void(0)">
                    <div>
                        <div class="fw-500">Team meeting at 3:00 PM</div>
                        <small class="text-muted">2 hours ago</small>
                    </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item justify-content-center" href="javascript:void(0)">View all notifications</a>
            </div>
        </div>

        <div class="dropdown">
            <button type="button" class="icon-btn" data-bs-toggle="dropdown" aria-label="Messages">
                <i class="bi bi-chat-dots"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" style="min-width: 320px;">
                <div class="dropdown-header">Messages</div>
                <a class="dropdown-item" href="javascript:void(0)">
                    <span class="avatar-fallback sm me-2">JD</span>
                    <span class="d-flex flex-column">
                        <span class="fw-500">John Doe</span>
                        <small class="text-muted">Can you review the proposal?</small>
                    </span>
                </a>
                <a class="dropdown-item" href="javascript:void(0)">
                    <span class="avatar-fallback sm me-2">SM</span>
                    <span class="d-flex flex-column">
                        <span class="fw-500">Sarah Miller</span>
                        <small class="text-muted">Sprint planning tomorrow</small>
                    </span>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item justify-content-center" href="javascript:void(0)">Open inbox</a>
            </div>
        </div>

        <button type="button" class="icon-btn" data-theme-toggle="dark" aria-label="Toggle dark mode">
            <i class="bi bi-moon-stars d-block"></i>
            <i class="bi bi-sun d-none"></i>
        </button>

        <button type="button" class="icon-btn" data-fullscreen aria-label="Toggle fullscreen">
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
