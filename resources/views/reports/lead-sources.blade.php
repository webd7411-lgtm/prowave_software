@extends('layouts.app')

@section('title', 'Lead Sources Report')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lead Sources</li>
                </ol>
            </nav>
            <h1 class="page-title">Lead Sources</h1>
            <p class="page-subtitle">Where your leads come from, and which channels convert best.</p>
        </div>
        <div>
            <a href="{{ route('reports.export', ['report' => 'lead-sources', 'from' => $from?->toDateString(), 'to' => $to?->toDateString()]) }}"
               class="btn btn-soft-primary">
                <i class="bi bi-download me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('reports.lead-sources') }}" class="card card-body mb-3">
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
                <a href="{{ route('reports.lead-sources') }}" class="btn btn-outline">Reset</a>
            </div>
        </div>
    </form>

    <x-card title="Sources Breakdown" subtitle="Performance by acquisition channel" icon="bi-tag">
        @if ($sources->count())
            @php
                $max = $sources->max('total');
                $grand = $sources->sum('total');
            @endphp
            <div class="table-responsive">
                <table class="table align-middle mb-4 table-cards-lg">
                    <thead>
                    <tr>
                        <th>Source</th>
                        <th style="width: 35%;">Share</th>
                        <th class="text-center">Leads</th>
                        <th class="text-center">Won</th>
                        <th class="text-center">Lost</th>
                        <th class="text-end">Conversion</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($sources as $source)
                        <tr>
                            <td data-label="Source" class="fw-medium">{{ ucfirst($source->source) }}</td>
                            <td data-label="Share">
                                <div class="d-flex align-items-center gap-2 w-100" style="max-width: 220px;">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar" style="width: {{ $max > 0 ? ($source->total / $max) * 100 : 0 }}%"></div>
                                    </div>
                                    <span class="fs-xs text-muted">{{ $grand > 0 ? round(($source->total / $grand) * 100, 1) : 0 }}%</span>
                                </div>
                            </td>
                            <td data-label="Leads" class="text-center fw-semibold">{{ $source->total }}</td>
                            <td data-label="Won" class="text-center text-success">{{ $source->won }}</td>
                            <td data-label="Lost" class="text-center text-danger">{{ $source->lost }}</td>
                            <td data-label="Conversion" class="text-end fw-semibold">{{ $source->total > 0 ? round(($source->won / $source->total) * 100, 1).'%' : '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state icon="bi-tag" title="No leads in this period" subtitle="Adjust the date range or add leads with source values." />
        @endif
    </x-card>
@endsection
