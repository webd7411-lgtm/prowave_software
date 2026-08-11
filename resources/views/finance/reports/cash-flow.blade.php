@extends('layouts.app')
@section('title', 'Cash Flow Report')
@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.reports.index') }}">Financial Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">Cash Flow</li>
            </ol>
        </nav>
        <h1 class="page-title mb-1">Cash Flow</h1>
        <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('finance.reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
    </div>
</div>

{{-- Date filter --}}
<div class="card mb-4">
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
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card fin-income">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <div class="fin-kpi-label">Money In</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($totalIn, 0) }}</div>
            <div class="fin-kpi-sub">Credits</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card fin-expense">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div class="fin-kpi-label">Money Out</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($totalOut, 0) }}</div>
            <div class="fin-kpi-sub">Debits</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card {{ $net >= 0 ? 'fin-profit' : 'fin-loss' }}">
            <div class="fin-kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="fin-kpi-label">Net Flow</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format(abs($net), 0) }}</div>
            <div class="fin-kpi-sub">{{ $net >= 0 ? 'Inflow' : 'Outflow' }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card fin-cash">
            <div class="fin-kpi-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="fin-kpi-label">Cash Balance</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($cashBalance, 0) }}</div>
            <div class="fin-kpi-sub">All cash accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card fin-bank">
            <div class="fin-kpi-icon"><i class="bi bi-bank2"></i></div>
            <div class="fin-kpi-label">Bank Balance</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($bankBalance, 0) }}</div>
            <div class="fin-kpi-sub">Active accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card fin-vendor">
            <div class="fin-kpi-icon"><i class="bi bi-list-check"></i></div>
            <div class="fin-kpi-label">Transactions</div>
            <div class="fin-kpi-value">{{ $transactions->count() }}</div>
            <div class="fin-kpi-sub">Selected period</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Monthly In vs Out</h5></div>
            <div class="card-body">
                @php
                    $monthly = $transactions->groupBy(fn($t) => $t->transaction_date->format('M Y'))
                        ->map(fn($g) => ['in' => (float) $g->sum('credit'), 'out' => (float) $g->sum('debit')])
                        ->sortKeys();
                @endphp
                @if($monthly->count() > 0)
                <div class="chart-wrapper-sm w-100"><canvas id="flowChart"></canvas></div>
                @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bar-chart fs-1 d-block mb-2 opacity-25"></i>
                    No transactions in selected period
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-list-ul me-2 text-primary"></i>Transaction History</h5></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 fin-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th class="text-end">In</th>
                            <th class="text-end">Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                        <tr>
                            <td data-label="Date" class="text-muted small">{{ $txn->transaction_date->format('d M Y') }}</td>
                            <td data-label="Reference"><span class="font-monospace small">{{ $txn->reference }}</span></td>
                            <td data-label="Description" class="text-muted small">{{ $txn->description ?? '—' }}</td>
                            <td data-label="Account" class="text-muted small">
                                @if($txn->account_type === 'cash' && $txn->cashAccount) {{ $txn->cashAccount->name }}
                                @elseif($txn->account_type === 'bank' && $txn->bankAccount) {{ $txn->bankAccount->bank_name }}
                                @else — @endif
                            </td>
                            <td data-label="In" class="text-end fin-amount-in">{{ $txn->credit > 0 ? number_format($txn->credit, 0) : '—' }}</td>
                            <td data-label="Out" class="text-end fin-amount-out">{{ $txn->debit > 0 ? number_format($txn->debit, 0) : '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5">
                            <i class="bi bi-arrow-left-right fs-1 d-block mb-2 opacity-25"></i>
                            <p class="text-muted mb-0">No transactions in this period</p>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
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
    if (Object.keys(monthly).length && document.getElementById('flowChart')) {
        const labels = Object.keys(monthly);
        const inflow = labels.map(l => monthly[l].in);
        const outflow = labels.map(l => monthly[l].out);
        new Chart(document.getElementById('flowChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'In', data: inflow, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.12)', fill: true, tension: 0.4, pointRadius: 3 },
                    { label: 'Out', data: outflow, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.12)', fill: true, tension: 0.4, pointRadius: 3 },
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
    }
});
</script>
@endpush
