@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </nav>
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">Manage accounts, roles, and employee information.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('users.export')
                <a href="{{ route('admin.users.export') }}" class="btn btn-soft-success" title="Export Excel">
                    <i class="bi bi-file-earmark-excel me-1"></i>Export
                </a>
                <a href="{{ route('admin.users.export-pdf') }}" class="btn btn-soft-danger" title="Export PDF">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </a>
            @endcan
            @can('users.import')
                <a href="{{ route('admin.users.import') }}" class="btn btn-outline">
                    <i class="bi bi-upload me-1"></i>Import
                </a>
            @endcan
            @can('users.create')
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i>Add User
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <x-stat-card label="Total Users" :value="$stats['total']" icon="bi-people" variant="primary" id="stat-total" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Active" :value="$stats['active']" icon="bi-person-check" variant="success" id="stat-active" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Inactive" :value="$stats['inactive']" icon="bi-person-x" variant="secondary" id="stat-inactive" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Suspended" :value="$stats['suspended']" icon="bi-person-lock" variant="danger" id="stat-suspended" />
        </div>
    </div>

    <x-card body-class="p-3" id="usersCard">
        <form id="usersFilterForm" class="row g-2 align-items-end" method="GET" action="{{ route('admin.users.index') }}">
            <div class="col-12 col-md-3">
                <label class="form-label fs-xs text-muted mb-1">Search</label>
                <input type="search" name="search" class="form-control" id="filter-search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, email, employee ID...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Status</label>
                <select name="status" class="form-select" id="filter-status">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Department</label>
                <select name="department_id" class="form-select" id="filter-department">
                    <option value="">All Departments</option>
                    @foreach ($departments as $id => $name)
                        <option value="{{ $id }}" @selected(($filters['department_id'] ?? '') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs text-muted mb-1">Role</label>
                <select name="role" class="form-select" id="filter-role">
                    <option value="">All Roles</option>
                    @foreach ($roles as $name => $label)
                        <option value="{{ $name }}" @selected(($filters['role'] ?? '') === $name)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex align-items-center justify-content-md-end gap-2 pt-2 pt-md-0">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input me-2 cursor-pointer" type="checkbox" name="trashed" value="1" id="filter-trashed" @checked(! empty($filters['trashed']))>
                    <label class="form-check-label text-muted fs-sm cursor-pointer mb-0" for="filter-trashed">Trashed</label>
                </div>
                <button type="submit" class="btn btn-soft-primary btn-sm" id="filter-apply" title="Apply filters"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-soft-secondary btn-sm" id="filter-reset" title="Reset all filters"><i class="bi bi-x-circle me-1"></i>Reset</a>
            </div>
        </form>

        <div class="d-flex align-items-center gap-2 mb-2 mt-2">
            <form id="bulkForm" method="POST" action="{{ route('admin.users.bulk') }}">
                @csrf
                <input type="hidden" name="ids" id="bulkIds">
                <div class="d-flex gap-2 align-items-center">
                    <select name="action" class="form-select form-select-sm w-auto" required>
                        <option value="" disabled selected>Bulk actions</option>
                        <option value="activate">Activate</option>
                        <option value="inactive">Mark Inactive</option>
                        <option value="suspend">Suspend</option>
                        <option value="delete">Move to Trash</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline d-none" id="bulkSubmit">
                        Apply <span class="badge badge-soft-primary ms-1" data-bulk-count>0</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table id="usersTable" class="table table-hover align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th class="no-sort"></th>
                        <th>User</th>
                        <th>Employee ID</th>
                        <th>Roles</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Joined</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </x-card>
@endsection

@push('scripts')
    <script>
        function initUsersTable() {
            if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                setTimeout(initUsersTable, 50);
                return;
            }

            if ($.fn.DataTable.isDataTable('#usersTable')) {
                $('#usersTable').DataTable().destroy();
            }

            const table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                searching: false,
                pageLength: 15,
                lengthMenu: [10, 15, 25, 50, 100],
                order: [[7, 'desc']],
                ajax: {
                    url: '{{ route('admin.users.data') }}',
                    data: function (d) {
                        d.search_query = $('#filter-search').val();
                        d.status = $('#filter-status').val();
                        d.department_id = $('#filter-department').val();
                        d.role = $('#filter-role').val();
                        d.trashed = $('#filter-trashed').is(':checked') ? 1 : 0;
                    },
                },
                columns: [
                    { data: null, orderable: false, width: '32px', render: function (data, type, row) {
                        return '<input type="checkbox" class="form-check-input dt-checkbox" value="' + row.DT_RowId.replace('user-', '') + '">';
                    } },
                    { data: 'name' },
                    { data: 'employee_id' },
                    { data: 'roles' },
                    { data: 'department' },
                    { data: 'status' },
                    { data: 'last_login' },
                    { data: 'joined' },
                    { data: 'actions', orderable: false, searchable: false },
                ],
                error: function (xhr, error, thrown) {
                    console.error('DataTables AJAX error:', error, thrown);
                },
                language: {
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ records',
                    infoEmpty: 'No records found',
                    infoFiltered: '(filtered from _MAX_ total records)',
                    zeroRecords: 'No matching records found',
                    paginate: { first: 'First', previous: 'Prev', next: 'Next', last: 'Last' },
                },
            });

            table.on('init', function () {
                table.columns().header().to$().each(function (i) {
                    $(this).attr('data-sort', '');
                });
            });

            table.on('draw', function () {
                const checked = table.$('input.dt-checkbox:checked').length;
                toggleBulk(checked);
            });

            table.on('change', 'input.dt-checkbox', function () {
                const checked = table.$('input.dt-checkbox:checked').length;
                toggleBulk(checked);
            });

            function toggleBulk(count) {
                const btn = $('#bulkSubmit');
                if (count > 0) {
                    btn.removeClass('d-none');
                    btn.find('[data-bulk-count]').text(count);
                } else {
                    btn.addClass('d-none');
                }
            }

            $('#bulkForm').on('submit', function (e) {
                const ids = table.$('input.dt-checkbox:checked').map(function () {
                    return $(this).val();
                }).get();

                if (ids.length === 0 || !$(this).find('select').val()) {
                    e.preventDefault();
                    return;
                }

                $('#bulkIds').val(ids.join(','));
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initUsersTable);
        } else {
            initUsersTable();
        }
    </script>
@endpush
