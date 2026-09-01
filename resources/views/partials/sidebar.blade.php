<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            @if(setting('general.logo'))
                <img src="{{ asset(setting('general.logo')) }}" alt="{{ setting('general.name', 'Prowave') }}">
            @elseif(setting('branding.login_logo'))
                <img src="{{ asset(setting('branding.login_logo')) }}" alt="{{ setting('general.name', 'Prowave') }}">
            @else
                <img src="{{ asset('images/logo.jpg') }}" alt="{{ setting('general.name', 'Prowave') }}" style="height: 36px;">
            @endif
            <span>
                <span class="brand-name">{{ setting('general.name', 'Prowave') }}</span>
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

            {{-- Project Management --}}
            @canany(['projects.view', 'project_tasks.view', 'time_tracking.view', 'project_reports.view', 'project_team.view'])
                <div class="nav-section">Project Management</div>
                <li class="nav-item has-submenu {{ request()->routeIs('projects.*') ? 'open' : '' }}" id="menu-projects">
                    <a href="javascript:void(0)" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        <i class="bi bi-folder-symlink"></i>
                        <span class="nav-text">Projects</span>
                        <i class="bi bi-chevron-down chevron"></i>
                    </a>
                    <ul class="nav-submenu">
                        @can('projects.view')
                            <li>
                                <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.index') || request()->routeIs('projects.show') ? 'active' : '' }}">
                                    <i class="bi bi-kanban"></i>
                                    <span class="nav-text">All Projects</span>
                                </a>
                            </li>
                        @endcan
                        @can('projects.create')
                            <li>
                                <a href="{{ route('projects.create') }}" class="nav-link {{ request()->routeIs('projects.create') ? 'active' : '' }}">
                                    <i class="bi bi-plus-circle"></i>
                                    <span class="nav-text">New Project</span>
                                </a>
                            </li>
                        @endcan
                        @can('project_tasks.view')
                            <li>
                                <a href="{{ route('projects.tasks.index') }}" class="nav-link {{ request()->routeIs('projects.tasks.index') ? 'active' : '' }}">
                                    <i class="bi bi-check2-square"></i>
                                    <span class="nav-text">Tasks List</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('projects.tasks.kanban') }}" class="nav-link {{ request()->routeIs('projects.tasks.kanban') ? 'active' : '' }}">
                                    <i class="bi bi-layout-three-columns"></i>
                                    <span class="nav-text">Kanban Board</span>
                                </a>
                            </li>
                        @endcan
                        @can('projects.view')
                            <li>
                                <a href="{{ route('projects.calendar') }}" class="nav-link {{ request()->routeIs('projects.calendar') ? 'active' : '' }}">
                                    <i class="bi bi-calendar3"></i>
                                    <span class="nav-text">Project Calendar</span>
                                </a>
                            </li>
                        @endcan
                        @can('project_reports.view')
                            <li>
                                <a href="{{ route('projects.reports.index') }}" class="nav-link {{ request()->routeIs('projects.reports.*') ? 'active' : '' }}">
                                    <i class="bi bi-bar-chart-line"></i>
                                    <span class="nav-text">Project Reports</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            @canany(['crm_dashboard.view', 'leads.view', 'clients.view', 'followups.view', 'quotations.view', 'invoices.view', 'payments.view', 'ledger.view'])
                <div class="nav-section">CRM</div>
                <li class="nav-item has-submenu {{ request()->routeIs('crm.*') ? 'open' : '' }}" id="menu-crm">
                    <a href="javascript:void(0)" class="nav-link {{ request()->routeIs('crm.*') ? 'active' : '' }}"><i class="bi bi-briefcase"></i><span class="nav-text">CRM</span><i class="bi bi-chevron-down chevron"></i></a>
                    <ul class="nav-submenu">
                        @can('crm_dashboard.view')<li><a href="{{ route('crm.dashboard') }}" class="nav-link"><i class="bi bi-speedometer2"></i><span class="nav-text">CRM Dashboard</span></a></li>@endcan
                        @can('leads.view')<li><a href="{{ route('crm.leads.index') }}" class="nav-link"><i class="bi bi-person-lines-fill"></i><span class="nav-text">Leads</span></a></li>@endcan
                        @can('clients.view')<li><a href="{{ route('crm.clients.index') }}" class="nav-link"><i class="bi bi-buildings"></i><span class="nav-text">Clients</span></a></li>@endcan
                        @can('followups.view')<li><a href="{{ route('crm.followups.index') }}" class="nav-link"><i class="bi bi-calendar-check"></i><span class="nav-text">Follow-ups</span></a></li>@endcan
                        @can('quotations.view')<li><a href="{{ route('crm.quotations.index') }}" class="nav-link"><i class="bi bi-file-earmark-text"></i><span class="nav-text">Quotations</span></a></li>@endcan
                        @can('invoices.view')<li><a href="{{ route('crm.invoices.index') }}" class="nav-link"><i class="bi bi-receipt"></i><span class="nav-text">Invoices</span></a></li>@endcan
                        @can('payments.view')<li><a href="{{ route('crm.payments.index') }}" class="nav-link"><i class="bi bi-cash-coin"></i><span class="nav-text">Payments</span></a></li>@endcan
                    </ul>
                </li>
            @endcanany

            {{-- Workspace: Calendar, Meetings --}}
            @canany(['calendar.view', 'meetings.view'])
                <div class="nav-section">Workspace</div>
                <li class="nav-item has-submenu {{ request()->routeIs(['calendar.*', 'meetings.*']) ? 'open' : '' }}" id="menu-workspace">
                    <a href="javascript:void(0)" class="nav-link {{ request()->routeIs(['calendar.*', 'meetings.*']) ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i>
                        <span class="nav-text">Workspace</span>
                        <i class="bi bi-chevron-down chevron"></i>
                    </a>
                    <ul class="nav-submenu">
                        @can('calendar.view')
                            <li>
                                <a href="{{ route('calendar.index') }}" class="nav-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                                    <i class="bi bi-calendar-event"></i>
                                    <span class="nav-text">Calendar</span>
                                </a>
                            </li>
                        @endcan
                        @can('meetings.view')
                            <li>
                                <a href="{{ route('meetings.index') }}" class="nav-link {{ request()->routeIs('meetings.*') ? 'active' : '' }}">
                                    <i class="bi bi-camera-video"></i>
                                    <span class="nav-text">Meetings</span>
                                </a>
                            </li>
                        @endcan
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

            {{-- HR --}}
            @canany(['hr_dashboard.view', 'staff.view', 'attendance.view', 'leave.view', 'tasks.view', 'daily_reports.view', 'designations.view', 'shifts.view'])
                <div class="nav-section">HR</div>
                <li class="nav-item has-submenu {{ request()->routeIs('hr.*') ? 'open' : '' }}" id="menu-hr">
                    <a href="javascript:void(0)" class="nav-link {{ request()->routeIs('hr.*') ? 'active' : '' }}">
                        <i class="bi bi-person-workspace"></i>
                        <span class="nav-text">HR Management</span>
                        <i class="bi bi-chevron-down chevron"></i>
                    </a>
                    <ul class="nav-submenu">
                        @can('hr_dashboard.view')
                            <li>
                                <a href="{{ route('hr.dashboard') }}" class="nav-link {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                                    <i class="bi bi-speedometer2"></i>
                                    <span class="nav-text">HR Dashboard</span>
                                </a>
                            </li>
                        @endcan
                        @canany(['staff.view', 'designations.view'])
                            <li class="nav-item has-submenu" id="menu-hr-staff">
                                <a href="javascript:void(0)" class="nav-link">
                                    <i class="bi bi-people"></i>
                                    <span class="nav-text">Staff</span>
                                    <i class="bi bi-chevron-down chevron"></i>
                                </a>
                                <ul class="nav-submenu">
                                    @can('staff.view')
                                        <li>
                                            <a href="{{ route('hr.staff.index') }}" class="nav-link {{ request()->routeIs('hr.staff.*') ? 'active' : '' }}">
                                                <span class="nav-text">All Staff</span>
                                            </a>
                                        </li>
                                    @endcan
                                    @can('designations.view')
                                        <li>
                                            <a href="{{ route('hr.designations.index') }}" class="nav-link {{ request()->routeIs('hr.designations.*') ? 'active' : '' }}">
                                                <span class="nav-text">Designations</span>
                                            </a>
                                        </li>
                                    @endcan
                                    @can('shifts.view')
                                        <li>
                                            <a href="{{ route('hr.shifts.index') }}" class="nav-link {{ request()->routeIs('hr.shifts.*') ? 'active' : '' }}">
                                                <span class="nav-text">Work Shifts</span>
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany
                        @can('attendance.view')
                            <li>
                                <a href="{{ route('hr.attendance.dashboard') }}" class="nav-link {{ request()->routeIs('hr.attendance.*') ? 'active' : '' }}">
                                    <i class="bi bi-calendar-check"></i>
                                    <span class="nav-text">Attendance</span>
                                </a>
                            </li>
                        @endcan
                        @canany(['leave.view', 'daily_reports.view', 'tasks.view'])
                            <li class="nav-item has-submenu" id="menu-hr-daytoday">
                                <a href="javascript:void(0)" class="nav-link">
                                    <i class="bi bi-clipboard-data"></i>
                                    <span class="nav-text">Day-to-Day</span>
                                    <i class="bi bi-chevron-down chevron"></i>
                                </a>
                                <ul class="nav-submenu">
                                    @can('leave.view')
                                        <li>
                                            <a href="{{ route('hr.leave.index') }}" class="nav-link {{ request()->routeIs('hr.leave.*') ? 'active' : '' }}">
                                                <span class="nav-text">Leave Requests</span>
                                            </a>
                                        </li>
                                    @endcan
                                    @can('tasks.view')
                                        <li>
                                            <a href="{{ route('hr.tasks.index') }}" class="nav-link {{ request()->routeIs('hr.tasks.*') ? 'active' : '' }}">
                                                <span class="nav-text">Daily Tasks</span>
                                            </a>
                                        </li>
                                    @endcan
                                    @can('daily_reports.view')
                                        <li>
                                            <a href="{{ route('hr.updates.index') }}" class="nav-link {{ request()->routeIs('hr.updates.*') ? 'active' : '' }}">
                                                <span class="nav-text">Work Updates</span>
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endcanany
                        @can('reports.view')
                            <li>
                                <a href="{{ route('hr.reports.index') }}" class="nav-link {{ request()->routeIs('hr.reports.*') ? 'active' : '' }}">
                                    <i class="bi bi-graph-up-arrow"></i>
                                    <span class="nav-text">Reports</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- Finance --}}
            @canany(['finance.view', 'expenses.view', 'income.view', 'accounts.view', 'transactions.view', 'receivables.view', 'financial_reports.view', 'vendors.view'])
                <div class="nav-section">Finance</div>
                <li class="nav-item has-submenu {{ request()->routeIs('finance.*') ? 'open' : '' }}" id="menu-finance">
                    <a href="javascript:void(0)" class="nav-link {{ request()->routeIs('finance.*') ? 'active' : '' }}">
                        <i class="bi bi-wallet2"></i>
                        <span class="nav-text">Finance</span>
                        <i class="bi bi-chevron-down chevron"></i>
                    </a>
                    <ul class="nav-submenu">
                        @can('finance.dashboard')
                        <li>
                            <a href="{{ route('finance.dashboard') }}" class="nav-link {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2"></i>
                                <span class="nav-text">Finance Dashboard</span>
                            </a>
                        </li>
                        @endcan
                        @can('expenses.view')
                        <li>
                            <a href="{{ route('finance.expenses.index') }}" class="nav-link {{ request()->routeIs('finance.expenses.*') ? 'active' : '' }}">
                                <i class="bi bi-receipt-cutoff"></i>
                                <span class="nav-text">Expenses</span>
                            </a>
                        </li>
                        @endcan
                        @can('income.view')
                        <li>
                            <a href="{{ route('finance.income.index') }}" class="nav-link {{ request()->routeIs('finance.income.*') ? 'active' : '' }}">
                                <i class="bi bi-arrow-down-circle"></i>
                                <span class="nav-text">Income</span>
                            </a>
                        </li>
                        @endcan
                        @can('accounts.view')
                        <li class="nav-item has-submenu">
                            <a href="javascript:void(0)" class="nav-link {{ request()->routeIs('finance.accounts.*') ? 'active' : '' }}">
                                <i class="bi bi-bank"></i>
                                <span class="nav-text">Accounts</span>
                                <i class="bi bi-chevron-down chevron"></i>
                            </a>
                            <ul class="nav-submenu">
                                <li><a href="{{ route('finance.accounts.cash') }}" class="nav-link {{ request()->routeIs('finance.accounts.cash*') ? 'active' : '' }}"><span class="nav-text">Cash Accounts</span></a></li>
                                <li><a href="{{ route('finance.accounts.bank') }}" class="nav-link {{ request()->routeIs('finance.accounts.bank*') ? 'active' : '' }}"><span class="nav-text">Bank Accounts</span></a></li>
                            </ul>
                        </li>
                        @endcan
                        @can('transactions.view')
                        <li>
                            <a href="{{ route('finance.transactions.index') }}" class="nav-link {{ request()->routeIs('finance.transactions.*') ? 'active' : '' }}">
                                <i class="bi bi-arrow-left-right"></i>
                                <span class="nav-text">Transactions</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('finance.transfers.index') }}" class="nav-link {{ request()->routeIs('finance.transfers.*') ? 'active' : '' }}">
                                <i class="bi bi-shuffle"></i>
                                <span class="nav-text">Transfers</span>
                            </a>
                        </li>
                        @endcan
                        @can('receivables.view')
                        <li>
                            <a href="{{ route('finance.receivables.index') }}" class="nav-link {{ request()->routeIs('finance.receivables.*') ? 'active' : '' }}">
                                <i class="bi bi-clock-history"></i>
                                <span class="nav-text">Receivables</span>
                            </a>
                        </li>
                        @endcan
                        @can('vendors.view')
                        <li>
                            <a href="{{ route('finance.vendors.index') }}" class="nav-link {{ request()->routeIs('finance.vendors.*') ? 'active' : '' }}">
                                <i class="bi bi-shop"></i>
                                <span class="nav-text">Vendors</span>
                            </a>
                        </li>
                        @endcan
                        @can('financial_reports.view')
                        <li>
                            <a href="{{ route('finance.reports.index') }}" class="nav-link {{ request()->routeIs('finance.reports.*') ? 'active' : '' }}">
                                <i class="bi bi-bar-chart-line"></i>
                                <span class="nav-text">Financial Reports</span>
                            </a>
                        </li>
                        @endcan
                        @can('financial_data.manage')
                        <li>
                            <a href="{{ route('finance.categories.index') }}" class="nav-link {{ request()->routeIs('finance.categories.*') ? 'active' : '' }}">
                                <i class="bi bi-tags"></i>
                                <span class="nav-text">Categories & Methods</span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
            @endcanany


            {{-- Reports --}}
            @canany(['reports.view', 'management.view'])
                <div class="nav-section">Insights</div>
                @can('management.view')
                    <li class="nav-item">
                        <a href="{{ route('management.summary') }}" class="nav-link {{ request()->routeIs('management.*') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span class="nav-text">Management Summary</span>
                        </a>
                    </li>
                @endcan
                @can('reports.view')
                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index', 'reports.lead-*', 'reports.client-*', 'reports.invoice-*', 'reports.task-*') ? 'active' : '' }}">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span class="nav-text">Business Reports</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('hr.reports.index') }}" class="nav-link {{ request()->routeIs('hr.reports.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span class="nav-text">HR Reports</span>
                        </a>
                    </li>
                @endcan
            @endcanany

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

                @can('settings.view')
                    <li class="nav-item">
                        <a href="{{ route('settings.general') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i>
                            <span class="nav-text">Settings</span>
                        </a>
                    </li>
                @endcan
                @can('audit.view')
                    <li class="nav-item">
                        <a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-check"></i>
                            <span class="nav-text">Audit Logs</span>
                        </a>
                    </li>
                @endcan
            @endcanany

        </ul>
    </nav>


</aside>
