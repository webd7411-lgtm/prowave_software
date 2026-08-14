@extends('layouts.app')
@section('title','Sales Leads')
@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Sales Leads</h1>
        <p class="page-subtitle">Capture, qualify and convert every opportunity.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('crm.leads.pipeline') }}" class="btn btn-soft-primary"><i class="bi bi-kanban me-1"></i>Pipeline</a>
        <a href="{{ route('crm.leads.export', request()->query()) }}" class="btn btn-soft-success"><i class="bi bi-download me-1"></i>Export</a>
        <button type="button" class="btn btn-soft-info" id="printSelectedBtn"><i class="bi bi-printer me-1"></i>Print</button>
        <button type="button" class="btn btn-soft-secondary" data-bs-toggle="modal" data-bs-target="#importLeadsModal"><i class="bi bi-upload me-1"></i>Import</button>
        @can('leads.edit')
            <button type="button" class="btn btn-soft-secondary" data-bs-toggle="modal" data-bs-target="#statusesModal"><i class="bi bi-tags me-1"></i>Statuses</button>
        @endcan
        @can('leads.create')
            <a href="{{ route('crm.leads.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Lead</a>
        @endcan
    </div>
</div>
<x-card body-class="p-3 mb-3">
    <form class="row g-2" id="leadsFilterForm">
        <div class="col-12 col-md-4">
            <input name="search" id="filter-search" value="{{ $filters['search']??'' }}" class="form-control auto-filter" placeholder="Search company, contact or phone...">
        </div>
        <div class="col-6 col-md-2">
            <select name="status" id="filter-status" class="form-select auto-filter">
                <option value="">All statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->slug }}" @selected(($filters['status']??'')===$s->slug)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="source" id="filter-source" class="form-select auto-filter">
                <option value="">All sources</option>
                @foreach($sources as $v=>$l)
                    <option value="{{ $v }}">{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="priority" id="filter-priority" class="form-select auto-filter">
                <option value="">All priority</option>
                @foreach(['low','medium','high','urgent'] as $p)
                    <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        @can('leads.view_all')
            <div class="col-6 col-md-2">
                <select name="owner_id" id="filter-owner" class="form-select auto-filter">
                    <option value="">All owners</option>
                    @foreach($owners as $id=>$n)
                        <option value="{{ $id }}" @selected(($filters['owner_id']??'')==$id)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
        @endcan
        <div class="col-6 col-md-2">
            <button type="button" class="btn btn-soft-secondary w-100" id="resetFilters"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
        </div>
    </form>
    <script>
        document.querySelectorAll('.auto-filter').forEach(function(sel) {
            if (sel.tagName === 'SELECT') {
                sel.addEventListener('change', function() {
                    document.getElementById('leadsFilterForm').submit();
                });
            } else {
                var timeout;
                sel.addEventListener('input', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(function() {
                        document.getElementById('leadsFilterForm').submit();
                    }, 500);
                });
            }
        });
        document.getElementById('resetFilters').addEventListener('click', function() {
            window.location.href = '{{ route('crm.leads.index') }}';
        });
    </script>
</x-card>
<div class="d-md-none">
    <div class="d-flex align-items-center gap-2 mb-2 px-1">
        <input type="checkbox" class="form-check-input" id="selectAllMobile">
        <label for="selectAllMobile" class="fs-xs text-muted mb-0">Select all on page</label>
    </div>
    @forelse($leads as $lead)
        <div class="card mb-2 text-reset lead-card-mobile" data-lead-id="{{ $lead->id }}">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-2 mb-2">
                    <div class="d-flex gap-2 min-w-0">
                        <input type="checkbox" class="form-check-input lead-checkbox-mobile flex-shrink-0 mt-1" value="{{ $lead->id }}">
                        <a href="{{ route('crm.leads.show',$lead) }}" class="min-w-0 text-decoration-none text-reset">
                            <div class="fw-600 text-truncate">{{ $lead->company_name }}</div>
                            <div class="fs-xs text-muted">{{ $lead->lead_number }}</div>
                        </a>
                    </div>
                    <span class="badge badge-soft-{{ $lead->priority==='urgent'?'danger':'secondary' }} flex-shrink-0 align-self-start">{{ ucfirst($lead->priority) }}</span>
                </div>
                <div class="row g-2 fs-xs text-muted mb-2">
                    <div class="col-6"><i class="bi bi-person me-1"></i>{{ $lead->contact_person ?: '—' }}</div>
                    <div class="col-6"><i class="bi bi-phone me-1"></i>{{ $lead->phone ?: '—' }}</div>
                    <div class="col-12"><i class="bi bi-person-badge me-1"></i>{{ $lead->owner?->name ?? 'Unassigned' }}</div>
                    <div class="col-12"><i class="bi bi-pin-map me-1"></i>{{ $lead->address ?: '—' }}</div>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <div class="flex-grow-1">
                        @can('leads.edit')
                            <select class="form-select form-select-sm lead-status-dropdown" data-lead-id="{{ $lead->id }}">
                                @foreach($statuses->where('slug', '!=', 'contacted') as $s)
                                    <option value="{{ $s->slug }}" @selected($lead->status===$s->slug)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <span class="badge" style="background:{{ $statusColors[$lead->status]??'#6c757d' }}">{{ $statuses->firstWhere('slug',$lead->status)?->name ?? ucwords(str_replace('_',' ',$lead->status)) }}</span>
                        @endcan
                    </div>
                    @can('leads.edit')
                        <a class="btn btn-sm btn-soft-info flex-shrink-0" href="{{ route('crm.leads.edit',$lead) }}"><i class="bi bi-pencil"></i></a>
                    @endcan
                    <a class="btn btn-sm btn-soft-primary flex-shrink-0" href="{{ route('crm.leads.show',$lead) }}"><i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    @empty
        <x-empty-state title="No leads found" text="Create a lead to get your sales pipeline moving." />
    @endforelse
</div>
<x-card class="d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="leadsTable">
            <thead>
                <tr>
                    <th style="width:30px"><input type="checkbox" class="form-check-input" id="selectAllLeads"></th>
                    <th>Company / Business</th><th>Contact Name</th><th>Phone</th><th>Address</th><th>Owner</th><th>Status</th><th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    <tr class="lead-row" data-lead-id="{{ $lead->id }}">
                        <td><input type="checkbox" class="form-check-input lead-checkbox" value="{{ $lead->id }}"></td>
                        <td data-label="Company / Business">
                            <a class="fw-600 text-decoration-none" href="{{ route('crm.leads.show',$lead) }}">{{ $lead->company_name }}</a>
                            <div class="fs-xs text-muted">{{ $lead->lead_number }}</div>
                        </td>
                        <td data-label="Contact Name">{{ $lead->contact_person }}</td>
                        <td data-label="Phone">{{ $lead->phone }}</td>
                        <td data-label="Address">{{ $lead->address ? Str::limit($lead->address, 30) : '—' }}</td>
                        <td data-label="Owner">{{ $lead->owner?->name ?? 'Unassigned' }}</td>
                        <td data-label="Status">
                            @can('leads.edit')
                                <select class="form-select form-select-sm lead-status-dropdown" data-lead-id="{{ $lead->id }}" style="width:auto;min-width:130px;">
                                    @foreach($statuses->where('slug', '!=', 'contacted') as $s)
                                        <option value="{{ $s->slug }}" @selected($lead->status===$s->slug)>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <span class="badge" style="background:{{ $statusColors[$lead->status]??'#6c757d' }}">{{ $statuses->firstWhere('slug',$lead->status)?->name ?? ucwords(str_replace('_',' ',$lead->status)) }}</span>
                            @endcan
                        </td>
                        <td data-label="" class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                @can('leads.edit')
                                    <a class="btn btn-sm btn-soft-info" href="{{ route('crm.leads.edit',$lead) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                                @endcan
                                <a class="btn btn-sm btn-soft-primary" href="{{ route('crm.leads.show',$lead) }}" title="View"><i class="bi bi-arrow-right"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-empty-state title="No leads found" text="Create a lead to get your sales pipeline moving." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>
<div class="mt-3 d-flex justify-content-end">{{ $leads->links() }}</div>
@endsection
@push('modals')
@can('leads.edit')
<div class="modal fade" id="meetingScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-event text-success me-2"></i>Schedule Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted fs-sm mb-3">Set the meeting date &amp; time for <strong id="meetingLeadName"></strong>. A reminder will be sent to you, Sales Manager and Admin.</p>
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-500">Date <span class="text-danger">*</span></label>
                        <input type="date" id="meetingFollowUpDate" class="form-control" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-500">Time <span class="text-danger">*</span></label>
                        <input type="time" id="meetingFollowUpTime" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnConfirmMeeting"><i class="bi bi-check2 me-1"></i>Schedule Meeting</button>
            </div>
        </div>
    </div>
</div>
@endcan
@endpush
@push('scripts')
@can('leads.edit')
<script>
(function() {
    var meetingModal = null;
    var pendingLeadId = null;
    var pendingSelect = null;
    var pendingOriginalValue = null;

    function getBootstrapModal(element) {
        var bs = typeof bootstrap !== 'undefined' ? bootstrap : (window.bootstrap || null);
        if (bs && bs.Modal) {
            return bs.Modal.getInstance(element) || new bs.Modal(element);
        }
        return null;
    }

    function notifyToast(type, msg) {
        if (typeof toastr !== 'undefined' && typeof toastr[type] === 'function') {
            toastr[type](msg);
        } else if (typeof window.showToast === 'function') {
            window.showToast(type, msg);
        } else {
            console.log('[' + type + '] ' + msg);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var btnConfirm = document.getElementById('btnConfirmMeeting');
        if (btnConfirm) {
            btnConfirm.addEventListener('click', function() {
                var date = document.getElementById('meetingFollowUpDate').value;
                var time = document.getElementById('meetingFollowUpTime').value;
                if (!date || !time) { notifyToast('error', 'Please select date and time.'); return; }

                if (pendingSelect) {
                    pendingSelect.value = 'meeting_scheduled';
                }
                var modalEl = document.getElementById('meetingScheduleModal');
                var modal = modalEl ? getBootstrapModal(modalEl) : null;
                if (modal) modal.hide();
                notifyToast('info', 'Scheduling meeting...');

                fetch('{{ url('/crm/leads') }}/' + pendingLeadId + '/status', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: 'meeting_scheduled', follow_up_date: date, follow_up_time: time })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        notifyToast('success', data.message || 'Meeting scheduled.');
                        pendingOriginalValue = 'meeting_scheduled';
                    } else {
                        notifyToast('error', data.message || 'Failed to schedule meeting.');
                        if (pendingSelect) pendingSelect.value = pendingOriginalValue;
                    }
                })
                .catch(function() {
                    notifyToast('error', 'Failed to schedule meeting.');
                    if (pendingSelect) pendingSelect.value = pendingOriginalValue;
                });
            });
        }
    });

    document.querySelectorAll('.lead-status-dropdown').forEach(function(select) {
        var originalValue = select.value;
        select.addEventListener('change', function() {
            var leadId = this.getAttribute('data-lead-id');
            var newStatus = this.value;

            if (newStatus === 'meeting_scheduled') {
                pendingLeadId = leadId;
                pendingSelect = this;
                pendingOriginalValue = originalValue;
                var name = this.closest('tr') ? this.closest('tr').querySelector('a.fw-600').textContent.trim() : (this.closest('.card-body') ? this.closest('.card-body').querySelector('.fw-600').textContent.trim() : 'this lead');
                document.getElementById('meetingLeadName').textContent = name;
                document.getElementById('meetingFollowUpDate').value = '';
                document.getElementById('meetingFollowUpTime').value = '';
                
                var modalEl = document.getElementById('meetingScheduleModal');
                var modal = modalEl ? getBootstrapModal(modalEl) : null;
                if (modal) modal.show();
                this.value = originalValue;
                return;
            }

            var currentSelect = this;
            fetch('{{ url('/crm/leads') }}/' + leadId + '/status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    notifyToast('success', data.message || 'Status updated.');
                    originalValue = newStatus;
                } else {
                    notifyToast('error', data.message || 'Failed to update status.');
                    currentSelect.value = originalValue;
                }
            })
            .catch(function() {
                notifyToast('error', 'Failed to update status.');
                currentSelect.value = originalValue;
            });
        });
    });
})();
</script>
<script>
(function() {
    function getSelectedIds() {
        var ids = [];
        document.querySelectorAll('.lead-checkbox:checked, .lead-checkbox-mobile:checked').forEach(function(cb) {
            ids.push(cb.value);
        });
        return ids;
    }

    function getFilterParams() {
        var params = new URLSearchParams(window.location.search);
        var result = {};
        ['search', 'status', 'source', 'priority', 'owner_id'].forEach(function(key) {
            if (params.get(key)) result[key] = params.get(key);
        });
        return result;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var selectAll = document.getElementById('selectAllLeads');
        var selectAllMobile = document.getElementById('selectAllMobile');

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.lead-checkbox').forEach(function(cb) {
                    cb.checked = selectAll.checked;
                });
            });
        }
        if (selectAllMobile) {
            selectAllMobile.addEventListener('change', function() {
                document.querySelectorAll('.lead-checkbox-mobile').forEach(function(cb) {
                    cb.checked = selectAllMobile.checked;
                });
            });
        }

        document.getElementById('printSelectedBtn').addEventListener('click', function() {
            var ids = getSelectedIds();
            var url;
            if (ids.length > 0) {
                url = '{{ route("crm.leads.print") }}?selected=' + ids.join(',');
            } else {
                var filters = getFilterParams();
                var qs = new URLSearchParams(filters).toString();
                url = '{{ route("crm.leads.print") }}' + (qs ? '?' + qs : '');
            }
            printUrlInHiddenFrame(url);
        });
    });
})();
</script>
@endcan
@endpush
@push('modals')
<div class="modal fade" id="importLeadsModal" tabindex="-1" aria-labelledby="importLeadsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('crm.leads.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importLeadsModalLabel"><i class="bi bi-upload me-1"></i>Import Leads</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">Upload a CSV file to bulk-create leads. <a href="{{ route('crm.leads.import-template') }}" class="text-decoration-none fw-semibold"><i class="bi bi-filetype-csv me-1"></i>Download template</a></p>
                    <div class="alert alert-info py-2 px-3 mb-3">
                        <small class="d-block fw-semibold mb-1">Required fields (must be filled):</small>
                        <small class="d-block">• <strong>Phone Number</strong> — contact mobile number</small>
                        <small class="d-block text-muted mt-1">All other fields are optional and will use defaults if left empty.</small>
                    </div>
                    <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@push('modals')
<div class="modal fade" id="statusesModal" tabindex="-1" aria-labelledby="statusesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusesModalLabel"><i class="bi bi-tags me-1"></i>Lead Statuses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Current statuses. Add a new one — it appears instantly in the filter and lead form.</p>
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($statuses as $s)
                    <div class="d-flex align-items-center justify-content-between p-2 rounded border">
                        <span class="badge" style="background:{{ $s->color }}">{{ $s->name }}</span>
                        <form method="POST" action="{{ route('crm.leads.statuses.destroy',$s->slug) }}" onsubmit="return confirm('Deactivate &quot;{{ $s->name }}&quot;?')" class="m-0">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-soft-danger" title="Deactivate"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                    @endforeach
                </div>
                <hr>
                <form method="POST" action="{{ route('crm.leads.statuses.store') }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-7">
                        <label class="form-label small fw-semibold">New Status Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Demo Done" required maxlength="50">
                    </div>
                    <div class="col-3">
                        <label class="form-label small fw-semibold">Color</label>
                        <input type="color" name="color" class="form-control form-control-color w-100" value="#6366f1">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush
