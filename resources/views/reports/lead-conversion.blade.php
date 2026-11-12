@extends('layouts.app')

@section('title', 'Lead Conversion Report')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lead Conversion</li>
                </ol>
            </nav>
            <h1 class="page-title">Lead Conversion</h1>
            <p class="page-subtitle">Lead funnel health from {{ format_date($from?->toDateString()) }} to {{ format_date($to?->toDateString()) }}.</p>
        </div>
        <div>
            <a href="{{ route('reports.export', ['report' => 'lead-conversion', 'from' => $from?->toDateString(), 'to' => $to?->toDateString()]) }}"
               class="btn btn-soft-primary">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.lead-conversion') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="{{ $from?->toDateString() }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="{{ $to?->toDateString() }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
                <a href="{{ route('reports.lead-conversion') }}" class="btn btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Total Leads</div>
                    <div class="stat-value">{{ number_format($totals->total) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Won / Converted</div>
                    <div class="stat-value text-success">{{ number_format($totals->won) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Lost</div>
                    <div class="stat-value text-danger">{{ number_format($totals->lost) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Conversion Rate</div>
                    <div class="stat-value text-primary">{{ $conversionRate }}%</div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar" style="width: {{ min($conversionRate, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <x-card title="Monthly Conversion" subtitle="Leads created per month in the selected range" icon="bi-bar-chart">
                @if ($monthly->count())
                    <div class="report-bar-chart d-flex align-items-end gap-2" style="height: 220px;">
                        @foreach ($monthly as $row)
                            @php
                                $height = $totals->total > 0 ? max(($row->total / $totals->total) * 100, 4) : 4;
                                $pct = $row->total > 0 ? round(($row->won / $row->total) * 100) : 0;
                            @endphp
                            <div class="flex-grow-1 d-flex flex-column justify-content-end text-center report-bar-col">
                                <span class="fs-xs text-muted mb-1">{{ $row->total }}</span>
                                <div class="report-bar rounded-top mx-auto" style="height: {{ $height }}%; min-height: 8px;"></div>
                                <div class="mt-1 fs-xs fw-medium">{{ \Illuminate\Support\Carbon::parse($row->month . '-01')->format('M y') }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state icon="bi-inbox" title="No leads in this period" />
                @endif
            </x-card>
        </div>
        <div class="col-lg-5">
            <x-card title="Owner Performance" subtitle="Conversion by lead owner" icon="bi-person-badge">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Owner</th>
                            <th class="text-center">Leads</th>
                            <th class="text-center">Won</th>
                            <th class="text-end">Rate</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($ownerPerformance as $row)
                            <tr>
                                <td data-label="Owner" class="fw-medium">{{ $row->owner?->name ?? 'Unassigned' }}</td>
                                <td data-label="Leads" class="text-center">{{ $row->total }}</td>
                                <td data-label="Won" class="text-center text-success">{{ $row->won }}</td>
                                <td data-label="Rate" class="text-end fw-semibold">{{ $row->total > 0 ? round(($row->won / $row->total) * 100, 1).'%' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No leads in this period.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection
