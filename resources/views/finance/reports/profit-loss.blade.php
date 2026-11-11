@extends('layouts.app')
@section('title', 'Profit & Loss Report')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.reports.index') }}">Financial Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profit & Loss</li>
            </ol>
        </nav>
        <h1 class="page-title mb-1">Profit & Loss</h1>
        <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('finance.reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
    </div>
</div>

{{-- Date filter --}}
<div class="card mb-4 d-print-none">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <label class="form-label small mb-1">From</label>
                <input type="date" name="from_date" value="{{ $from }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <label class="form-label small mb-1">To</label>
                <input type="date" name="to_date" value="{{ $to }}" class="form-control form-control-sm">
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                <button class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel me-1"></i>Apply</button>
            </div>
        </form>
    </div>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4 d-print-none">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-income">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <div class="fin-kpi-label">Total Income</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($totalIncome, 2) }}</div>
            <div class="fin-kpi-sub">Selected period</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-expense">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div class="fin-kpi-label">Total Expenses</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($totalExpenses, 2) }}</div>
            <div class="fin-kpi-sub">Excludes rejected</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card {{ $netProfit >= 0 ? 'fin-profit' : 'fin-loss' }}">
            <div class="fin-kpi-icon"><i class="bi {{ $netProfit >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i></div>
            <div class="fin-kpi-label">Net Profit</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format(abs($netProfit), 2) }}</div>
            <div class="fin-kpi-sub">{{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-vendor">
            <div class="fin-kpi-icon"><i class="bi bi-percent"></i></div>
            <div class="fin-kpi-label">Profit Margin</div>
            <div class="fin-kpi-value">{{ number_format($profitPct, 2) }}%</div>
            <div class="fin-kpi-sub">of total income</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8 d-print-none">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Monthly Income vs Expenses</h5>
            </div>
            <div class="card-body">
                <div class="chart-wrapper"><canvas id="pnlChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Monthly Comparison</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 fin-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Income</th>
                                <th class="text-end">Expenses</th>
                                <th class="text-end">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthly as $m)
                            <tr>
                                <td data-label="Month" class="text-muted small">{{ $m['label'] }}</td>
                                <td data-label="Income" class="text-end fin-amount-in">{{ number_format($m['income'], 0) }}</td>
                                <td data-label="Expenses" class="text-end fin-amount-out">{{ number_format($m['expenses'], 0) }}</td>
                                <td data-label="Net" class="text-end fw-semibold {{ $m['income'] - $m['expenses'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($m['income'] - $m['expenses'], 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)';
    const textColor = isDark ? '#adb5bd' : '#6c757d';
    const monthly = @json($monthly);
    new Chart(document.getElementById('pnlChart'), {
        type: 'bar',
        data: {
            labels: monthly.map(m => m.label),
            datasets: [
                { label: 'Income', data: monthly.map(m => m.income), backgroundColor: 'rgba(25,135,84,0.7)', borderRadius: 4 },
                { label: 'Expenses', data: monthly.map(m => m.expenses), backgroundColor: 'rgba(220,53,69,0.7)', borderRadius: 4 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { color: textColor } } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor } },
                y: { grid: { color: gridColor }, ticks: { color: textColor } }
            }
        }
    });
});
</script>
@endpush
