<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <span class="logo-fallback">
                <i class="bi bi-water"></i>
            </span>
            <span>
                <span class="brand-name">{{ setting('company.name', 'Prowave') }}</span>
                <span class="brand-sub">Management System</span>
            </span>
        </a>
    </div>

    <div class="sidebar-search">
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" class="form-control" placeholder="Search menu..." data-sidebar-search aria-label="Search menu">
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav flex-column">

            @can('dashboard.view')
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
            @endcan

            {{-- Projects --}}
            @canany(['projects.view', 'tasks.view'])
                <div class="nav-section">Projects</div>
                <li class="nav-item has-submenu {{ request()->routeIs(['projects.*', 'tasks.*']) ? 'open' : '' }}" id="menu-projects">
                    <a href="javascript:void(0)" class="nav-link {{ request()->routeIs(['projects.*', 'tasks.*']) ? 'active' : '' }}">
                        <i class="bi bi-kanban"></i>
                        <span class="nav-text">Projects</span>
                        <i class="bi bi-chevron-down chevron"></i>
                    </a>
                    <ul class="nav-submenu">
                        <li>
                            <a href="javascript:void(0)" class="nav-link {{ request()->routeIs('projects.index') ? 'active' : '' }}">
                                <i class="bi bi-folder2-open"></i>
                                <span class="nav-text">All Projects</span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                                <i class="bi bi-list-task"></i>
                                <span class="nav-text">Task Board</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endcanany

            {{-- Organization / People --}}
            @canany(['users.view', 'departments.view'])
                <div class="nav-section">Organization</div>
                <li class="nav-item has-submenu {{ request()->routeIs(['admin.users.*', 'admin.departments.*']) ? 'open' : '' }}" id="menu-org">
                    <a href="javascript:void(0)" class="nav-link {{ request()->routeIs(['admin.users.*', 'admin.departments.*']) ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span class="nav-text">People</span>
                        <i class="bi bi-chevron-down chevron"></i>
                    </a>
                    <ul class="nav-submenu">
                        @can('users.view')
                            <li>
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                    <i class="bi bi-person-badge"></i>
                                    <span class="nav-text">All Users</span>
                                </a>
                            </li>
                        @endcan
                        @can('departments.view')
                            <li>
                                <a href="{{ route('admin.departments.index') }}" class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                                    <i class="bi bi-building"></i>
                                    <span class="nav-text">Departments</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- Finance --}}
            @canany(['invoices.view', 'expenses.view'])
                <div class="nav-section">Finance</div>
                <li class="nav-item has-submenu" id="menu-finance">
                    <a href="javascript:void(0)" class="nav-link">
                        <i class="bi bi-wallet2"></i>
                        <span class="nav-text">Finance</span>
                        <i class="bi bi-chevron-down chevron"></i>
                    </a>
                    <ul class="nav-submenu">
                        <li>
                            <a href="javascript:void(0)" class="nav-link">
                                <i class="bi bi-receipt"></i>
                                <span class="nav-text">Invoices</span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="nav-link">
                                <i class="bi bi-credit-card-2-front"></i>
                                <span class="nav-text">Expenses</span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" class="nav-link">
                                <i class="bi bi-cash-stack"></i>
                                <span class="nav-text">Budgets</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endcanany

            {{-- Reports --}}
            @can('reports.view')
                <div class="nav-section">Insights</div>
                <li class="nav-item">
                    <a href="javascript:void(0)" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span class="nav-text">Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0)" class="nav-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i>
                        <span class="nav-text">Analytics</span>
                    </a>
                </li>
            @endcan

            {{-- System --}}
            @canany(['roles.view', 'permissions.view', 'audit.view', 'settings.view'])
                <div class="nav-section">System</div>

                @canany(['roles.view', 'permissions.view'])
                    <li class="nav-item has-submenu {{ request()->routeIs(['admin.roles.*', 'admin.permissions.*']) ? 'open' : '' }}" id="menu-access">
                        <a href="javascript:void(0)" class="nav-link {{ request()->routeIs(['admin.roles.*', 'admin.permissions.*']) ? 'active' : '' }}">
                            <i class="bi bi-shield-lock"></i>
                            <span class="nav-text">Access Control</span>
                            <i class="bi bi-chevron-down chevron"></i>
                        </a>
                        <ul class="nav-submenu">
                            @can('roles.view')
                                <li>
                                    <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                        <i class="bi bi-shield-check"></i>
                                        <span class="nav-text">Roles</span>
                                    </a>
                                </li>
                            @endcan
                            @can('permissions.view')
                                <li>
                                    <a href="{{ route('admin.permissions.index') }}" class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                        <i class="bi bi-person-lock"></i>
                                        <span class="nav-text">Permissions</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                @can('audit.view')
                    <li class="nav-item">
                        <a href="javascript:void(0)" class="nav-link">
                            <i class="bi bi-journal-text"></i>
                            <span class="nav-text">Audit Logs</span>
                        </a>
                    </li>
                @endcan

                @can('settings.view')
                    <li class="nav-item">
                        <a href="{{ route('settings.general') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i>
                            <span class="nav-text">Settings</span>
                        </a>
                    </li>
                @endcan
            @endcanany

        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="upgrade-card">
            <h6 class="mb-1">Need help?</h6>
            <p>Visit our docs or contact support.</p>
            <a href="mailto:{{ setting('general.email', 'support@prowave.com') }}" class="btn btn-sm btn-light w-100">
                <i class="bi bi-headset me-1"></i> Contact Support
            </a>
        </div>
    </div>
</aside>
