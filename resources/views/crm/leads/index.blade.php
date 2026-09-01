@extends('layouts.app')
@section('title','Sales Leads')
@section('content')
<style>
    /* Scoping wrapper only - no background override so dark theme is preserved */
    .leads-page-container {
        /* intentionally no background-color - dark/light theme handles it */
    }

    /* Scoped text - no forced color, theme handles it */
    .leads-page-container .leads-page-title {
        font-weight: 800 !important;
        letter-spacing: -0.5px;
    }
    .leads-page-container .leads-page-subtitle {
        font-weight: 500;
        opacity: 0.75;
    }

    /* Scoped form controls - ONLY inside leads-page-container */
    .leads-page-container .form-control,
    .leads-page-container .form-select {
        border: 1.5px solid #cbd5e1 !important;
        color: #0f172a !important;
        font-weight: 500 !important;
        background-color: #ffffff !important;
        border-radius: 8px !important;
    }
    .leads-page-container .form-control:focus,
    .leads-page-container .form-select:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2) !important;
    }
    .leads-page-container .form-label {
        color: #1e293b !important;
        font-weight: 600 !important;
    }
    .leads-page-container .form-check-input {
        width: 1.15rem !important;
        height: 1.15rem !important;
        border: 2px solid #64748b !important;
        border-radius: 4px !important;
        background-color: #ffffff !important;
        cursor: pointer;
    }
    .leads-page-container .form-check-input:checked {
        background-color: #4f46e5 !important;
        border-color: #4f46e5 !important;
    }

    /* Cards Styling - no forced background so dark theme is preserved */
    .luxury-card {
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
    }

    /* Table Styling - scoped, no forced background */
    .leads-table-wrapper {
        border: 1.5px solid #cbd5e1;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .leads-table-wrapper .leads-table th {
        background: linear-gradient(135deg, #3730a3 0%, #312e81 100%) !important;
        color: #ffffff !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.7px !important;
        text-transform: uppercase !important;
        padding: 1rem 0.85rem !important;
        border-bottom: none !important;
    }
    .leads-table-wrapper .leads-table td {
        padding: 0.9rem 0.85rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid rgba(0,0,0,0.07) !important;
        font-size: 0.875rem;
    }
    .leads-table-wrapper .leads-table tbody tr {
        transition: background-color 0.15s ease;
    }

    /* Top Action Buttons */
    .btn-top-pipeline { background: #e0e7ff; color: #3730a3; border: 1.5px solid #c7d2fe; font-weight: 600; }
    .btn-top-pipeline:hover { background: #c7d2fe; color: #312e81; }
    
    .btn-top-export { background: #dcfce7; color: #15803d; border: 1.5px solid #bbf7d0; font-weight: 600; }
    .btn-top-export:hover { background: #bbf7d0; color: #166534; }
    
    .btn-top-print { background: #ffedd5; color: #c2410c; border: 1.5px solid #fed7aa; font-weight: 600; }
    .btn-top-print:hover { background: #fed7aa; color: #9a3412; }
    
    .btn-top-import { background: #e0f2fe; color: #0369a1; border: 1.5px solid #bae6fd; font-weight: 600; }
    .btn-top-import:hover { background: #bae6fd; color: #075985; }

    .btn-top-statuses { background: #f3e8ff; color: #7e22ce; border: 1.5px solid #e9d5ff; font-weight: 600; }
    .btn-top-statuses:hover { background: #e9d5ff; color: #6b21a8; }

    .btn-luxury-new {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
    }
    .btn-luxury-new:hover {
        background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
        box-shadow: 0 6px 18px rgba(79, 70, 229, 0.45);
    }

    /* Action Icons inside Table */
    .btn-action-edit {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 0.35rem 0.55rem;
        transition: all 0.2s;
    }
    .btn-action-edit:hover { background: #dbeafe; color: #1e40af; }

    .btn-action-delete {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 0.35rem 0.55rem;
        transition: all 0.2s;
    }
    .btn-action-delete:hover { background: #fee2e2; color: #b91c1c; }

    .btn-action-view {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #4f46e5;
        color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-action-view:hover {
        background: #3730a3;
        transform: translateX(2px);
    }

    /* Pagination - scoped */
    .leads-page-container .pagination svg {
        width: 1rem !important;
        height: 1rem !important;
        vertical-align: middle;
    }
    .leads-page-container .pagination {
        margin-bottom: 0;
        gap: 2px;
    }
    .leads-page-container .pagination .page-item .page-link {
        border-radius: 6px;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        color: #334155;
        border-color: #cbd5e1;
    }
    .leads-page-container .pagination .page-item.active .page-link {
        background-color: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
    }
</style>

<div class="leads-page-container">
    <div class="page-header mb-4">
        <div>
            <h1 class="leads-page-title fs-2 mb-1">Sales Leads</h1>
            <p class="leads-page-subtitle mb-0">Capture, qualify and convert every opportunity.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('crm.leads.pipeline') }}" class="btn btn-top-pipeline px-3 rounded-3"><i class="bi bi-kanban me-1"></i>Pipeline</a>
            <a href="{{ route('crm.leads.export', request()->query()) }}" class="btn btn-top-export px-3 rounded-3"><i class="bi bi-download me-1"></i>Export</a>
            <button type="button" class="btn btn-top-print px-3 rounded-3" id="printSelectedBtn"><i class="bi bi-printer me-1"></i>Print</button>
            @can('leads.delete')
                <button type="button" class="btn btn-danger d-none px-3 rounded-3" id="bulkDeleteBtn"><i class="bi bi-trash me-1"></i>Delete Selected (<span id="selectedCount">0</span>)</button>
            @endcan
            <button type="button" class="btn btn-top-import px-3 rounded-3" data-bs-toggle="modal" data-bs-target="#importLeadsModal"><i class="bi bi-upload me-1"></i>Import</button>
            @can('leads.edit')
                <button type="button" class="btn btn-top-statuses px-3 rounded-3" data-bs-toggle="modal" data-bs-target="#statusesModal"><i class="bi bi-tags me-1"></i>Statuses</button>
            @endcan
            @can('leads.create')
                <a href="{{ route('crm.leads.create') }}" class="btn btn-luxury-new px-3 py-2"><i class="bi bi-plus-lg me-1"></i>New Lead</a>
            @endcan
        </div>
    </div>

    <!-- Filter Card -->
    <div class="luxury-card p-3 mb-4">
        <form class="row g-2 align-items-end" id="leadsFilterForm" action="{{ route('crm.leads.index') }}" method="GET" onsubmit="return false;">
            <div class="col-12 col-md-3">
                <label class="form-label fs-xs mb-1">Search</label>
                <input name="search" id="filter-search" value="{{ $filters['search']??'' }}" class="form-control auto-filter" placeholder="Search company, contact or phone...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs mb-1">Status</label>
                <select name="status" id="filter-status" class="form-select auto-filter">
                    <option value="">All statuses</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->slug }}" @selected(($filters['status']??'')===$s->slug)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs mb-1">From Date</label>
                <input type="date" name="from_date" id="filter-from-date" value="{{ $filters['from_date']??'' }}" class="form-control auto-filter">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fs-xs mb-1">To Date</label>
                <input type="date" name="to_date" id="filter-to-date" value="{{ $filters['to_date']??'' }}" class="form-control auto-filter">
            </div>
            @can('leads.view_all')
                <div class="col-6 col-md-2">
                    <label class="form-label fs-xs mb-1">Owner</label>
                    <select name="owner_id" id="filter-owner" class="form-select auto-filter">
                        <option value="">All owners</option>
                        @foreach($owners as $id=>$n)
                            <option value="{{ $id }}" @selected(($filters['owner_id']??'')==$id)>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
            @endcan
            <div class="col-6 col-md-1">
                <button type="button" class="btn btn-outline-secondary w-100 fw-semibold" id="resetFilters" style="border-color:#cbd5e1; border-radius:8px;" title="Reset Filters"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
            </div>
        </form>
    </div>

    <!-- Dynamic Container for Leads List & Table -->
    <div id="leadsTableContainer">
        <!-- Top Controls: Per-Page Dropdown & Showing items summary -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 px-1">
            <div class="d-flex align-items-center gap-2 fs-sm fw-semibold text-slate">
                <span class="text-dark">Show</span>
                <select class="form-select form-select-sm per-page-select" style="width: auto; font-weight:700;" onchange="handlePerPageChange(this.value)">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int)request('per_page', 10) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
                <span class="text-dark">entries per page</span>
            </div>
            <div class="fs-xs fw-semibold text-slate">
                Showing <strong class="text-dark">{{ $leads->firstItem() ?? 0 }}</strong> to <strong class="text-dark">{{ $leads->lastItem() ?? 0 }}</strong> of <strong class="text-dark">{{ $leads->total() }}</strong> leads
            </div>
        </div>

        <!-- Mobile View Cards -->
        <div class="d-md-none">
            <div class="d-flex align-items-center gap-2 mb-2 px-1">
                <input type="checkbox" class="form-check-input" id="selectAllMobile">
                <label for="selectAllMobile" class="fs-xs fw-bold text-dark mb-0">Select all on page</label>
            </div>
            @forelse($leads as $lead)
                <div class="luxury-card mb-3 p-3 lead-card-mobile" data-lead-id="{{ $lead->id }}">
                    <div class="d-flex justify-content-between gap-2 mb-2">
                        <div class="d-flex gap-2 min-w-0">
                            <input type="checkbox" class="form-check-input lead-checkbox-mobile flex-shrink-0 mt-1" value="{{ $lead->id }}">
                            <a href="{{ route('crm.leads.show',$lead) }}" class="min-w-0 text-decoration-none text-reset">
                                <div class="fw-bold text-indigo fs-6 text-truncate" style="color:#4f46e5;">
                                    {{ $lead->company_name }}
                                    @if($lead->converted_client_id)
                                        <span class="badge bg-success-subtle text-success fs-xs ms-1" title="Converted to Client"><i class="bi bi-person-check me-1"></i>Client</span>
                                    @endif
                                </div>
                                <div class="fs-xs fw-semibold text-primary">{{ $lead->lead_number }}</div>
                            </a>
                        </div>
                        <span class="badge bg-secondary flex-shrink-0 align-self-start">{{ ucfirst($lead->priority) }}</span>
                    </div>
                    <div class="row g-2 fs-xs fw-semibold text-slate mb-2">
                        <div class="col-6"><i class="bi bi-person text-primary me-1"></i>{{ $lead->contact_person ?: '—' }}</div>
                        <div class="col-6"><i class="bi bi-telephone-fill text-success me-1"></i>{{ $lead->phone ?: '—' }}</div>
                        <div class="col-12"><i class="bi bi-person-badge-fill text-warning me-1"></i>{{ $lead->owner?->name ?? 'Unassigned' }}</div>
                        <div class="col-12"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $lead->address ?: '—' }}</div>
                    </div>
                    <div class="d-flex gap-2 mt-2 align-items-start">
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
                            @if(in_array($lead->status, ['meeting_scheduled', 'call_again']))
                                @php
                                    $fu = $lead->followups->firstWhere('status', 'scheduled') ?? $lead->followups->first();
                                @endphp
                                @if($fu && $fu->follow_up_date)
                                    <div class="fs-xs fw-semibold text-dark mt-1 lead-schedule-info">
                                        <i class="bi bi-clock me-1 text-primary"></i>{{ \Carbon\Carbon::parse($fu->follow_up_date)->format('M d, Y') }}
                                        @if($fu->follow_up_time)
                                            {{ \Carbon\Carbon::parse($fu->follow_up_time)->format('h:i A') }}
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                        @can('leads.edit')
                            <a class="btn btn-action-edit" href="{{ route('crm.leads.edit',$lead) }}"><i class="bi bi-pencil"></i></a>
                        @endcan
                        @can('leads.delete')
                            <form method="POST" action="{{ route('crm.leads.destroy', $lead) }}" onsubmit="return confirm('Are you sure you want to delete this lead?')" class="d-inline m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-action-delete" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        @endcan
                        <a class="btn btn-action-view" href="{{ route('crm.leads.show',$lead) }}"><i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            @empty
                <x-empty-state title="No leads found" text="Create a lead to get your sales pipeline moving." />
            @endforelse
        </div>

        <!-- Desktop View Table -->
        <div class="leads-table-wrapper d-none d-md-block">
            <div class="table-responsive">
                <table class="table leads-table align-middle mb-0" id="leadsTable">
                    <thead>
                        <tr>
                            <th style="width:36px"><input type="checkbox" class="form-check-input" id="selectAllLeads"></th>
                            <th>Company / Business</th>
                            <th>Contact Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th class="text-end" style="padding-right: 1.25rem !important;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr class="lead-row" data-lead-id="{{ $lead->id }}">
                                <td><input type="checkbox" class="form-check-input lead-checkbox" value="{{ $lead->id }}"></td>
                                <td data-label="Company / Business">
                                    <a class="fw-bold text-decoration-none fs-6" style="color:#4f46e5;" href="{{ route('crm.leads.show',$lead) }}">{{ $lead->company_name }}</a>
                                    @if($lead->converted_client_id)
                                        <span class="badge bg-success text-white fs-xs ms-1" title="Converted to Client"><i class="bi bi-person-check me-1"></i>Client</span>
                                    @endif
                                    <div class="fs-xs fw-bold text-slate mt-1" style="color:#64748b;">{{ $lead->lead_number }}</div>
                                </td>
                                <td data-label="Contact Name" class="fw-semibold text-dark">{{ $lead->contact_person ?: '—' }}</td>
                                <td data-label="Phone">
                                    @if($lead->phone)
                                        <div class="d-flex align-items-center gap-1 fw-semibold text-dark">
                                            <i class="bi bi-telephone-fill text-indigo fs-xs" style="color:#4f46e5;"></i>
                                            <span>{{ $lead->phone }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td data-label="Address">
                                    @if($lead->address)
                                        <div class="d-flex align-items-center gap-1 fw-medium text-slate">
                                            <i class="bi bi-geo-alt-fill text-muted fs-xs"></i>
                                            <span>{{ Str::limit($lead->address, 30) }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td data-label="Owner" class="fw-semibold text-dark">{{ $lead->owner?->name ?? 'Unassigned' }}</td>
                                <td data-label="Status" class="status-cell">
                                    @can('leads.edit')
                                        <select class="form-select form-select-sm lead-status-dropdown fw-bold" data-lead-id="{{ $lead->id }}" style="width:auto;min-width:130px; border-color:#cbd5e1;">
                                            @foreach($statuses->where('slug', '!=', 'contacted') as $s)
                                                <option value="{{ $s->slug }}" @selected($lead->status===$s->slug)>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <span class="badge" style="background:{{ $statusColors[$lead->status]??'#6c757d' }}">{{ $statuses->firstWhere('slug',$lead->status)?->name ?? ucwords(str_replace('_',' ',$lead->status)) }}</span>
                                    @endcan
                                    @if(in_array($lead->status, ['meeting_scheduled', 'call_again']))
                                        @php
                                            $fu = $lead->followups->firstWhere('status', 'scheduled') ?? $lead->followups->first();
                                        @endphp
                                        @if($fu && $fu->follow_up_date)
                                            <div class="fs-xs fw-semibold text-dark mt-1 lead-schedule-info">
                                                <i class="bi bi-clock-fill me-1 text-primary"></i>{{ \Carbon\Carbon::parse($fu->follow_up_date)->format('M d, Y') }}
                                                @if($fu->follow_up_time)
                                                    {{ \Carbon\Carbon::parse($fu->follow_up_time)->format('h:i A') }}
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td data-label="Actions" class="text-end" style="padding-right: 1.25rem !important;">
                                    <div class="d-flex gap-1 justify-content-end align-items-center">
                                        @can('leads.edit')
                                            <a class="btn btn-action-edit" href="{{ route('crm.leads.edit',$lead) }}" title="Edit"><i class="bi bi-pencil"></i></a>
                                        @endcan
                                        @can('leads.delete')
                                            <form method="POST" action="{{ route('crm.leads.destroy', $lead) }}" onsubmit="return confirm('Are you sure you want to delete this lead?')" class="d-inline m-0">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-action-delete" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endcan
                                        <a class="btn btn-action-view" href="{{ route('crm.leads.show',$lead) }}" title="View"><i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><x-empty-state title="No leads found" text="Create a lead to get your sales pipeline moving." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Pagination Controls Card -->
        <div class="mt-3 luxury-card p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="fs-xs fw-semibold text-slate">
                    Showing <span class="fw-bold text-dark">{{ $leads->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $leads->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $leads->total() }}</span> leads
                    @if($leads->lastPage() > 1)
                        <span class="ms-1">(Page <span class="fw-bold text-dark">{{ $leads->currentPage() }}</span> of <span class="fw-bold text-dark">{{ $leads->lastPage() }}</span>)</span>
                    @endif
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="d-flex align-items-center gap-1 fs-xs fw-semibold text-slate">
                        <span>Show:</span>
                        <select class="form-select form-select-sm per-page-select" style="width: auto; font-weight:700;" onchange="handlePerPageChange(this.value)">
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int)request('per_page', 10) === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span>per page</span>
                    </div>

                    @if($leads->lastPage() > 1)
                        <form method="GET" action="{{ route('crm.leads.index') }}" class="d-flex align-items-center gap-1 fs-xs fw-semibold text-slate ms-md-2" id="leadsJumpForm">
                            @foreach(request()->except(['page']) as $k => $v)
                                @if(is_array($v))
                                    @foreach($v as $arrVal)
                                        <input type="hidden" name="{{ $k }}[]" value="{{ $arrVal }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endif
                            @endforeach
                            <span>Go to page:</span>
                            <input type="number" name="page" min="1" max="{{ $leads->lastPage() }}" value="{{ $leads->currentPage() }}" class="form-control form-control-sm text-center px-1 fw-bold" style="width: 60px;" required>
                            <button type="submit" class="btn btn-sm btn-primary px-2 py-0 fs-xs fw-bold" style="height:28px;">Go</button>
                        </form>
                    @endif

                    <div class="ms-md-2">
                        {{ $leads->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Scroll to Top Button -->
<button id="scrollToTopBtn" type="button" class="btn btn-primary rounded-circle shadow-lg" style="position: fixed; bottom: 25px; right: 25px; width: 44px; height: 44px; display: none; justify-content: center; align-items: center; z-index: 9999; border: none; background: #4f46e5;" title="Scroll to Top">
    <i class="bi bi-arrow-up fs-5"></i>
</button>

<script>
    // AJAX Page Loader (No Full Page Reload)
    function loadLeadsAjax(url) {
        var container = document.getElementById('leadsTableContainer');
        if (!container) {
            window.location.href = url;
            return;
        }
        container.style.opacity = '0.5';
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.text(); })
        .then(function(html) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');
            var newContent = doc.getElementById('leadsTableContainer');
            if (newContent) {
                container.innerHTML = newContent.innerHTML;
                container.style.opacity = '1';
                history.pushState(null, '', url);
                bindEvents();
            } else {
                window.location.href = url;
            }
        })
        .catch(function(err) {
            container.style.opacity = '1';
            window.location.href = url;
        });
    }

    function handlePerPageChange(val) {
        var url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        loadLeadsAjax(url.toString());
    }

    function validatePageJump(form, maxPage) {
        var input = form.querySelector('input[name="page"]');
        var val = parseInt(input.value, 10);
        if (isNaN(val) || val < 1 || val > maxPage) {
            alert('Please enter a valid page number between 1 and ' + maxPage);
            return false;
        }
        return true;
    }

    // Scroll Position Persistence
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem('leads_page_scroll_y', window.scrollY);
    });

    document.addEventListener('DOMContentLoaded', function() {
        var savedY = sessionStorage.getItem('leads_page_scroll_y');
        if (savedY !== null) {
            window.scrollTo({ top: parseInt(savedY, 10), behavior: 'instant' });
        }

        // Scroll to top floating button logic
        var topBtn = document.getElementById('scrollToTopBtn');
        if (topBtn) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 250) {
                    topBtn.style.display = 'flex';
                } else {
                    topBtn.style.display = 'none';
                }
            });
            topBtn.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // Filters Auto Submit via AJAX
        var filterForm = document.getElementById('leadsFilterForm');
        var filterTimeout;
        document.querySelectorAll('.auto-filter').forEach(function(sel) {
            if (sel.tagName === 'SELECT') {
                sel.addEventListener('change', function() {
                    triggerFilterAjax();
                });
            } else {
                sel.addEventListener('input', function() {
                    clearTimeout(filterTimeout);
                    filterTimeout = setTimeout(function() {
                        triggerFilterAjax();
                    }, 400);
                });
            }
        });

        function triggerFilterAjax() {
            var params = new URLSearchParams(new FormData(filterForm));
            var currentParams = new URLSearchParams(window.location.search);
            if (currentParams.has('per_page')) {
                params.set('per_page', currentParams.get('per_page'));
            }
            params.set('page', '1');
            loadLeadsAjax(window.location.pathname + '?' + params.toString());
        }

        document.getElementById('resetFilters').addEventListener('click', function() {
            loadLeadsAjax('{{ route('crm.leads.index') }}');
        });

        // Intercept Pagination Links Click for AJAX
        document.addEventListener('click', function(e) {
            var pageLink = e.target.closest('#leadsTableContainer .pagination a');
            if (pageLink && pageLink.href) {
                e.preventDefault();
                loadLeadsAjax(pageLink.href);
            }
        });

        // Intercept Page Jump Submit
        document.addEventListener('submit', function(e) {
            var jumpForm = e.target.closest('#leadsJumpForm');
            if (jumpForm) {
                e.preventDefault();
                var maxPage = {{ $leads->lastPage() }};
                if (!validatePageJump(jumpForm, maxPage)) return;
                var pageInput = jumpForm.querySelector('input[name="page"]');
                var url = new URL(window.location.href);
                url.searchParams.set('page', pageInput.value);
                loadLeadsAjax(url.toString());
            }
        });

        // Handle Browser Back/Forward buttons
        window.addEventListener('popstate', function() {
            loadLeadsAjax(window.location.href);
        });

        bindEvents();
    });

    // Helper to re-bind checkboxes and dropdown handlers after AJAX table update
    function bindEvents() {
        var selectAll = document.getElementById('selectAllLeads');
        var selectAllMobile = document.getElementById('selectAllMobile');

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                document.querySelectorAll('.lead-checkbox').forEach(function(cb) {
                    cb.checked = selectAll.checked;
                });
                updateBulkDeleteState();
            });
        }
        if (selectAllMobile) {
            selectAllMobile.addEventListener('change', function() {
                document.querySelectorAll('.lead-checkbox-mobile').forEach(function(cb) {
                    cb.checked = selectAllMobile.checked;
                });
                updateBulkDeleteState();
            });
        }

        document.querySelectorAll('.lead-checkbox, .lead-checkbox-mobile').forEach(function(cb) {
            cb.addEventListener('change', updateBulkDeleteState);
        });

        if (typeof bindStatusDropdowns === 'function') {
            bindStatusDropdowns();
        }
    }

    function updateBulkDeleteState() {
        var ids = [];
        document.querySelectorAll('.lead-checkbox:checked, .lead-checkbox-mobile:checked').forEach(function(cb) {
            ids.push(cb.value);
        });
        var btn = document.getElementById('bulkDeleteBtn');
        var countSpan = document.getElementById('selectedCount');
        if (btn && countSpan) {
            countSpan.textContent = ids.length;
            if (ids.length > 0) {
                btn.classList.remove('d-none');
            } else {
                btn.classList.add('d-none');
            }
        }
    }
</script>
@endsection

@push('modals')
@can('leads.edit')
<div class="modal fade" id="meetingScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalTitle"><i class="bi bi-calendar-event text-success me-2" id="scheduleModalIcon"></i><span id="scheduleModalTitleText">Schedule Meeting</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted fs-sm mb-3">Set date &amp; time for <strong id="meetingLeadName"></strong>. A notification will be sent to the owner, Sales Manager, and Admin.</p>
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-500">Date <span class="text-danger">*</span></label>
                        <input type="date" id="meetingFollowUpDate" class="form-control" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-500">Time <span class="text-muted fs-xs">(Optional)</span></label>
                        <input type="time" id="meetingFollowUpTime" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnConfirmMeeting"><i class="bi bi-check2 me-1"></i><span id="scheduleModalBtnText">Schedule Meeting</span></button>
            </div>
        </div>
    </div>
</div>
@endcan
@endpush

@push('scripts')
@can('leads.edit')
<script>
var bindStatusDropdowns;
(function() {
    var pendingLeadId = null;
    var pendingSelect = null;
    var pendingOriginalValue = null;
    var pendingNewStatus = null;

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

    function formatTime12h(timeStr) {
        if (!timeStr) return '';
        var parts = timeStr.split(':');
        var hours = parseInt(parts[0], 10);
        var minutes = parts[1];
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        return (hours < 10 ? '0' + hours : hours) + ':' + minutes + ' ' + ampm;
    }

    function formatDateFormatted(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr + 'T00:00:00');
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return months[d.getMonth()] + ' ' + (d.getDate() < 10 ? '0' + d.getDate() : d.getDate()) + ', ' + d.getFullYear();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var btnConfirm = document.getElementById('btnConfirmMeeting');
        if (btnConfirm) {
            btnConfirm.addEventListener('click', function() {
                var date = document.getElementById('meetingFollowUpDate').value;
                var time = document.getElementById('meetingFollowUpTime').value;
                if (!date) { notifyToast('error', 'Please select date.'); return; }

                if (pendingSelect) {
                    pendingSelect.value = pendingNewStatus;
                }
                var modalEl = document.getElementById('meetingScheduleModal');
                var modal = modalEl ? getBootstrapModal(modalEl) : null;
                if (modal) modal.hide();
                notifyToast('info', 'Saving schedule...');

                fetch('{{ url('/crm/leads') }}/' + pendingLeadId + '/status', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: pendingNewStatus, follow_up_date: date, follow_up_time: time })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        notifyToast('success', data.message || 'Status updated successfully.');
                        pendingOriginalValue = pendingNewStatus;

                        if (pendingSelect) {
                            var parentDiv = pendingSelect.closest('.flex-grow-1') || pendingSelect.closest('td') || pendingSelect.parentElement;
                            var infoEl = parentDiv.querySelector('.lead-schedule-info');
                            var formattedText = formatDateFormatted(date) + (time ? ' ' + formatTime12h(time) : '');
                            if (infoEl) {
                                infoEl.innerHTML = '<i class="bi bi-clock me-1 text-primary"></i>' + formattedText;
                            } else {
                                var newDiv = document.createElement('div');
                                newDiv.className = 'fs-xs text-muted mt-1 lead-schedule-info';
                                newDiv.innerHTML = '<i class="bi bi-clock me-1 text-primary"></i>' + formattedText;
                                parentDiv.appendChild(newDiv);
                            }
                        }
                    } else {
                        notifyToast('error', data.message || 'Failed to update status.');
                        if (pendingSelect) pendingSelect.value = pendingOriginalValue;
                    }
                })
                .catch(function() {
                    notifyToast('error', 'Failed to update status.');
                    if (pendingSelect) pendingSelect.value = pendingOriginalValue;
                });
            });
        }
    });

    bindStatusDropdowns = function() {
        document.querySelectorAll('.lead-status-dropdown').forEach(function(select) {
            if (select.dataset.bound) return;
            select.dataset.bound = "true";
            var originalValue = select.value;
            select.addEventListener('change', function() {
                var leadId = this.getAttribute('data-lead-id');
                var newStatus = this.value;

                if (newStatus === 'meeting_scheduled' || newStatus === 'call_again') {
                    pendingLeadId = leadId;
                    pendingSelect = this;
                    pendingOriginalValue = originalValue;
                    pendingNewStatus = newStatus;

                    var isCall = newStatus === 'call_again';
                    document.getElementById('scheduleModalTitleText').textContent = isCall ? 'Schedule Call Again' : 'Schedule Meeting';
                    document.getElementById('scheduleModalBtnText').textContent = isCall ? 'Schedule Call' : 'Schedule Meeting';
                    document.getElementById('scheduleModalIcon').className = isCall ? 'bi bi-telephone-outbound text-warning me-2' : 'bi bi-calendar-event text-success me-2';

                    var name = this.closest('tr') ? this.closest('tr').querySelector('a').textContent.trim() : (this.closest('.card-body') ? this.closest('.card-body').querySelector('.fw-600').textContent.trim() : 'this lead');
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
                        var parentDiv = currentSelect.closest('.flex-grow-1') || currentSelect.closest('td') || currentSelect.parentElement;
                        var infoEl = parentDiv.querySelector('.lead-schedule-info');
                        if (infoEl) infoEl.remove();
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
    };
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
        ['search', 'status', 'from_date', 'to_date', 'owner_id'].forEach(function(key) {
            if (params.get(key)) result[key] = params.get(key);
        });
        return result;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var printBtn = document.getElementById('printSelectedBtn');
        if (printBtn) {
            printBtn.addEventListener('click', function() {
                var ids = getSelectedIds();
                var url;
                if (ids.length > 0) {
                    url = '{{ route("crm.leads.print") }}?selected=' + ids.join(',');
                } else {
                    var filters = getFilterParams();
                    var qs = new URLSearchParams(filters).toString();
                    url = '{{ route("crm.leads.print") }}' + (qs ? '?' + qs : '');
                }
                if (typeof printUrlInHiddenFrame === 'function') {
                    printUrlInHiddenFrame(url);
                } else {
                    window.open(url, '_blank');
                }
            });
        }

        var bulkBtn = document.getElementById('bulkDeleteBtn');
        if (bulkBtn) {
            bulkBtn.addEventListener('click', function() {
                var ids = getSelectedIds();
                if (ids.length === 0) {
                    alert('Please select at least one lead to delete.');
                    return;
                }
                if (confirm('Are you sure you want to delete ' + ids.length + ' selected lead(s)?')) {
                    var container = document.getElementById('bulkDeleteInputs');
                    container.innerHTML = '';
                    ids.forEach(function(id) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        container.appendChild(input);
                    });
                    document.getElementById('bulkDeleteForm').submit();
                }
            });
        }
    });
})();
</script>
@can('leads.delete')
<form id="bulkDeleteForm" method="POST" action="{{ route('crm.leads.bulk-delete') }}" class="d-none">
    @csrf
    <div id="bulkDeleteInputs"></div>
</form>
@endcan
@endcan
@push('modals')
<div class="modal fade" id="importLeadsModal" tabindex="-1" aria-labelledby="importLeadsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="POST" action="{{ route('crm.leads.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="importLeadsModalLabel"><i class="bi bi-upload me-1 text-primary"></i>Import Leads</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">Upload an Excel (.xlsx, .xls) or CSV file to bulk-create leads. <a href="{{ route('crm.leads.import-template') }}" class="text-decoration-none fw-semibold text-primary"><i class="bi bi-filetype-csv me-1"></i>Download template</a></p>
                    <div class="alert alert-info py-2 px-3 mb-3 border-0 bg-info-subtle text-info-emphasis rounded-3">
                        <small class="d-block fw-semibold mb-1">Required fields (must be filled):</small>
                        <small class="d-block">• <strong>Phone Number</strong> — contact mobile number</small>
                        <small class="d-block text-muted mt-1">All other fields are optional and will use defaults if left empty.</small>
                    </div>
                    <input type="file" name="file" class="form-control rounded-3" accept=".xlsx,.xls,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-upload me-1"></i>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@push('modals')
<div class="modal fade" id="statusesModal" tabindex="-1" aria-labelledby="statusesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="statusesModalLabel"><i class="bi bi-tags me-1 text-primary"></i>Lead Statuses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Current statuses. Add a new one — it appears instantly in the filter and lead form.</p>
                <div class="d-flex flex-column gap-2 mb-3">
                    @foreach($statuses as $s)
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3 border">
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
                        <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Demo Done" required maxlength="50">
                    </div>
                    <div class="col-3">
                        <label class="form-label small fw-semibold">Color</label>
                        <input type="color" name="color" class="form-control form-control-color w-100 rounded-3" value="#6366f1">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-3"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush
