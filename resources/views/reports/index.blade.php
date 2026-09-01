@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <div class="page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reports</li>
                </ol>
            </nav>
            <h1 class="page-title">Reports</h1>
            <p class="page-subtitle">Business intelligence across leads, clients, invoices and tasks.</p>
        </div>
    </div>

    <div class="row g-3">
        @foreach ([
            ['route' => 'reports.lead-conversion', 'icon' => 'bi-graph-up-arrow', 'title' => 'Lead Conversion', 'text' => 'Conversion rates, monthly funnel and owner performance.'],
            ['route' => 'reports.client-revenue', 'icon' => 'bi-currency-dollar', 'title' => 'Client Revenue', 'text' => 'Invoiced vs received amounts per client.'],
            ['route' => 'reports.invoice-aging', 'icon' => 'bi-hourglass-split', 'title' => 'Invoice Aging', 'text' => 'Outstanding receivables by overdue bucket.'],
            ['route' => 'reports.task-performance', 'icon' => 'bi-list-check', 'title' => 'Task Performance', 'text' => 'Team completion rates for project and daily tasks.'],
        ] as $report)
            <div class="col-md-6 col-xl-4">
                <a href="{{ route($report['route']) }}" class="text-decoration-none">
                    <div class="card h-100 report-card">
                        <div class="card-body d-flex gap-3">
                            <div class="report-icon">
                                <i class="bi {{ $report['icon'] }}"></i>
                            </div>
                            <div>
                                <h5 class="card-title">{{ $report['title'] }}</h5>
                                <p class="fs-sm text-muted mb-0">{{ $report['text'] }}</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
