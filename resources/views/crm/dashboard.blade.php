@extends('layouts.app')
@section('title', 'CRM Dashboard')

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">CRM Dashboard</h1>
        <p class="page-subtitle text-muted mb-0">
            @if(! $canViewAll || $ownerId)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-xs">
                    <i class="bi bi-person-badge me-1"></i>My Lead Workspace
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-xs">
                    <i class="bi bi-globe me-1"></i>All Organization Leads
                </span>
            @endif
            Overview of your lead pipeline, follow-ups, and sales conversions.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @can('leads.create')
            <a href="{{ route('crm.leads.create') }}" class="btn btn-primary shadow-sm btn-sm">
                <i class="bi bi-plus-lg me-1"></i>New Lead
            </a>
        @endcan
    </div>
</div>

{{-- Filters Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('crm.dashboard') }}" id="crm-filter-form" class="row g-2 align-items-end">
            @if($canViewAll)
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fs-xs text-muted mb-1 fw-semibold"><i class="bi bi-person me-1"></i>Sales Agent</label>
                    <select name="owner_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Sales Agents</option>
                        @foreach($salesAgents as $id => $name)
                            <option value="{{ $id }}" {{ (string)$ownerId === (string)$id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-6 col-sm-6 col-md-3">
                <label class="form-label fs-xs text-muted mb-1 fw-semibold"><i class="bi bi-clock-history me-1"></i>Time Period</label>
                <select name="period" class="form-select form-select-sm" id="period-select" onchange="toggleCustomDates(this.value); this.form.submit();">
                    <option value="all" {{ $period === 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="this_quarter" {{ $period === 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div class="col-6 col-sm-6 col-md-2 custom-date-col {{ $period === 'custom' ? '' : 'd-none' }}">
                <label class="form-label fs-xs text-muted mb-1 fw-semibold">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm">
            </div>

            <div class="col-6 col-sm-6 col-md-2 custom-date-col {{ $period === 'custom' ? '' : 'd-none' }}">
                <label class="form-label fs-xs text-muted mb-1 fw-semibold">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm">
            </div>

            <div class="col-12 col-sm-auto d-flex flex-wrap gap-1 ms-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary px-3 flex-fill flex-sm-grow-0">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                @if(request()->hasAny(['period', 'from_date', 'to_date', 'owner_id']))
                    <a href="{{ route('crm.dashboard') }}" class="btn btn-sm btn-light border flex-fill flex-sm-grow-0" title="Reset Filters">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- KPI Statistics Cards --}}
<div class="row g-3 mb-4">
    {{-- Total Leads --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fs-xs text-muted text-uppercase fw-semibold">Total Leads</span>
                <span class="badge bg-primary-subtle text-primary p-2 rounded-circle"><i class="bi bi-funnel fs-6"></i></span>
            </div>
            <div class="fs-3 fw-bold text-dark">{{ number_format($stats['total_leads']) }}</div>
            <div class="fs-xs text-muted mt-1">Total in scope</div>
        </div>
    </div>

    {{-- New Leads --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fs-xs text-muted text-uppercase fw-semibold">New Leads</span>
                <span class="badge bg-info-subtle text-info p-2 rounded-circle"><i class="bi bi-stars fs-6"></i></span>
            </div>
            <div class="fs-3 fw-bold text-info">{{ number_format($stats['new_leads']) }}</div>
            <div class="fs-xs text-muted mt-1">Pending first contact</div>
        </div>
    </div>

    {{-- Contacted Leads (NEW CARD REQUESTED BY USER) --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white border-start border-3 border-primary">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fs-xs text-primary text-uppercase fw-bold">Contacted</span>
                <span class="badge bg-primary text-white p-2 rounded-circle"><i class="bi bi-telephone-outbound fs-6"></i></span>
            </div>
            <div class="fs-3 fw-bold text-primary">{{ number_format($stats['contacted_leads']) }}</div>
            <div class="fs-xs text-muted mt-1">In communication</div>
        </div>
    </div>

    {{-- Qualified Leads --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fs-xs text-muted text-uppercase fw-semibold">Qualified</span>
                <span class="badge bg-warning-subtle text-warning p-2 rounded-circle"><i class="bi bi-check2-circle fs-6"></i></span>
            </div>
            <div class="fs-3 fw-bold text-warning">{{ number_format($stats['qualified_leads']) }}</div>
            <div class="fs-xs text-muted mt-1">Ready for proposal</div>
        </div>
    </div>

    {{-- Won / Converted --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fs-xs text-muted text-uppercase fw-semibold">Won / Converted</span>
                <span class="badge bg-success-subtle text-success p-2 rounded-circle"><i class="bi bi-trophy fs-6"></i></span>
            </div>
            <div class="fs-3 fw-bold text-success">{{ number_format($stats['won_leads']) }}</div>
            <div class="fs-xs text-muted mt-1">Successful deals</div>
        </div>
    </div>

    {{-- Conversion Rate --}}
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fs-xs text-muted text-uppercase fw-semibold">Win Rate</span>
                <span class="badge bg-dark-subtle text-dark p-2 rounded-circle"><i class="bi bi-graph-up-arrow fs-6"></i></span>
            </div>
            <div class="fs-3 fw-bold text-dark">{{ $stats['conversion_rate'] }}%</div>
            <div class="fs-xs text-muted mt-1">Lead to client ratio</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Dynamic Sales Pipeline Chart / Progress Bars --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-diagram-3 text-primary me-2"></i>Sales Pipeline Stages
                </h5>
                <span class="badge bg-light text-dark border">{{ count($pipelineData) }} Dynamic Statuses</span>
            </div>
            <div class="card-body pt-1">
                @php $maxCount = max(array_column($pipelineData, 'count') ?: [1]); @endphp
                <div class="vstack gap-3">
                    @forelse($pipelineData as $item)
                        @php 
                            $percent = $stats['total_leads'] > 0 ? round(($item['count'] / $stats['total_leads']) * 100, 1) : 0; 
                        @endphp
                        <div>
                            <div class="d-flex justify-content-between align-items-center fs-sm mb-1">
                                <span class="fw-semibold text-dark">
                                    <span class="d-inline-block rounded-circle me-1" style="width:10px; height:10px; background-color: {{ $item['color'] }}"></span>
                                    {{ $item['name'] }}
                                </span>
                                <div>
                                    <span class="fw-bold me-2">{{ number_format($item['count']) }}</span>
                                    <span class="fs-xs text-muted">({{ $percent }}%)</span>
                                </div>
                            </div>
                            <div class="progress" style="height: 9px; background-color: #f1f5f9;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $percent }}%; background-color: {{ $item['color'] }};" 
                                     aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">No active lead statuses found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary Stats & Quick Overview --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-speedometer2 text-success me-2"></i>Sales Performance Summary</h5>
            </div>
            <div class="card-body vstack gap-3 pt-0">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 rounded-3 bg-light">
                    <div class="min-w-0">
                        <div class="fs-xs text-muted text-uppercase fw-semibold">Pending Follow-ups</div>
                        <div class="fs-4 fw-bold text-warning mb-0">{{ number_format($stats['pending_followups']) }}</div>
                    </div>
                    @if($stats['overdue_followups'] > 0)
                        <span class="badge bg-danger text-white px-2 py-1 fs-xs">
                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $stats['overdue_followups'] }} Overdue
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success px-2 py-1 fs-xs"><i class="bi bi-check-all me-1"></i>On Track</span>
                    @endif
                </div>

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 rounded-3 bg-light">
                    <div class="min-w-0">
                        <div class="fs-xs text-muted text-uppercase fw-semibold">Total Accounts / Clients</div>
                        <div class="fs-4 fw-bold text-dark mb-0">{{ number_format($stats['total_clients']) }}</div>
                    </div>
                    <a href="{{ route('crm.clients.index') }}" class="btn btn-sm btn-outline-primary fs-xs">View Clients</a>
                </div>

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 rounded-3 bg-light">
                    <div class="min-w-0">
                        <div class="fs-xs text-muted text-uppercase fw-semibold">Outstanding Client Payments</div>
                        <div class="fs-4 fw-bold text-danger mb-0 text-break">{{ number_format($stats['outstanding_payments'], 2) }} <span class="fs-xs text-muted fw-normal">PKR</span></div>
                    </div>
                    <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-xs">Receivables</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Leads & Upcoming Followups --}}
<div class="row g-3 mt-1 mb-4">
    {{-- Recent Leads Table --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-person-lines-fill text-info me-2"></i>My Recent Leads</h5>
                <a href="{{ route('crm.leads.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">View All Leads &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 fs-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Lead Number</th>
                            <th>Company / Contact</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeads as $lead)
                            <tr>
                                <td data-label="Lead #" class="fw-semibold text-primary">{{ $lead->lead_number }}</td>
                                <td data-label="Company / Contact">
                                    <div class="fw-bold text-dark">{{ $lead->company_name ?: 'N/A' }}</div>
                                    <div class="fs-xs text-muted">{{ $lead->contact_person ?: $lead->phone }}</div>
                                </td>
                                <td data-label="Owner">{{ $lead->owner?->name ?? 'Unassigned' }}</td>
                                <td data-label="Status">
                                    <span class="badge bg-secondary-subtle text-dark border px-2 py-1 text-capitalize">
                                        {{ str_replace('_', ' ', $lead->status) }}
                                    </span>
                                </td>
                                <td data-label="Action" class="text-end fin-cell-actions">
                                    <a href="{{ route('crm.leads.show', $lead) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i> View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No leads found in this filter range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Upcoming Follow-ups --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-event text-warning me-2"></i>Upcoming Follow-ups</h5>
                <a href="{{ route('crm.followups.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">View Schedule &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush border-top-0">
                    @forelse($recentFollowups as $fu)
                        <div class="list-group-item p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-warning-subtle text-warning border fw-semibold text-uppercase fs-xs">
                                    <i class="bi bi-telephone me-1"></i>{{ $fu->type }}
                                </span>
                                <span class="fs-xs text-muted fw-semibold">
                                    {{ $fu->follow_up_date->format('M d, Y') }} {{ $fu->follow_up_time ? '· ' . date('h:i A', strtotime($fu->follow_up_time)) : '' }}
                                </span>
                            </div>
                            <div class="fw-bold text-dark">{{ $fu->lead?->company_name ?: 'Lead #'.$fu->lead_id }}</div>
                            @if($fu->notes)
                                <div class="fs-xs text-muted text-truncate mt-1">{{ $fu->notes }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">No upcoming follow-ups scheduled.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleCustomDates(val) {
        const cols = document.querySelectorAll('.custom-date-col');
        cols.forEach(el => {
            if (val === 'custom') {
                el.classList.remove('d-none');
            } else {
                el.classList.add('d-none');
            }
        });
    }
</script>
@endsection
