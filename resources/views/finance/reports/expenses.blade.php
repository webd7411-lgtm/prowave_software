@extends('layouts.app')
@section('title', 'Expense Report')
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
                <li class="breadcrumb-item active" aria-current="page">Expense Report</li>
            </ol>
        </nav>
        <h1 class="page-title mb-1">Expense Report</h1>
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
    <div class="col-6 col-md-4">
        <div class="fin-kpi-card fin-expense">
            <div class="fin-kpi-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="fin-kpi-label">Total Expenses</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($total, 2) }}</div>
            <div class="fin-kpi-sub">Selected period</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="fin-kpi-card fin-vendor">
            <div class="fin-kpi-icon"><i class="bi bi-list-check"></i></div>
            <div class="fin-kpi-label">Records</div>
            <div class="fin-kpi-value">{{ $expenses->count() }}</div>
            <div class="fin-kpi-sub">Expense entries</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="fin-kpi-card fin-receivable">
            <div class="fin-kpi-icon"><i class="bi bi-tags"></i></div>
            <div class="fin-kpi-label">Categories</div>
            <div class="fin-kpi-value">{{ $byCategory->count() }}</div>
            <div class="fin-kpi-sub">Distinct categories</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-pie-chart me-2 text-danger"></i>By Category</h5></div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if($byCategory->count() > 0)
                <div class="chart-wrapper-sm w-100"><canvas id="expensePie"></canvas></div>
                @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-pie-chart fs-1 d-block mb-2 opacity-25"></i>
                    No expense data in selected period
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 fin-table">
                    <thead>
                        <tr>
                            <th>Expense No</th>
                            <th>Category</th>
                            <th>Vendor/Project</th>
                            <th class="text-end">Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td data-label="Expense"><a href="{{ route('finance.expenses.show', $expense) }}" class="text-decoration-none">{{ $expense->expense_number }}</a></td>
                            <td data-label="Category">
                                <span class="badge rounded-pill" style="background:{{ $expense->category?->color ?? '#6c757d' }}20;color:{{ $expense->category?->color ?? '#6c757d' }}">{{ $expense->category?->name ?? 'Uncategorized' }}</span>
                            </td>
                            <td data-label="Vendor/Project" class="text-muted small">
                                @if($expense->vendor)<div>{{ $expense->vendor->name }}</div>@endif
                                @if($expense->project)<div class="text-primary">{{ $expense->project->name }}</div>@endif
                                @if(!$expense->vendor && !$expense->project) — @endif
                            </td>
                            <td data-label="Amount" class="text-end fin-amount-out">{{ number_format($expense->amount, 2) }}</td>
                            <td data-label="Date" class="text-muted small">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td data-label="Status"><span class="badge bg-{{ $expense->statusBadge() }}-subtle text-{{ $expense->statusBadge() }} rounded-pill">{{ ucfirst($expense->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5">
                            <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 opacity-25"></i>
                            <p class="text-muted mb-0">No expenses in this period</p>
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
    const textColor = isDark ? '#adb5bd' : '#6c757d';
    const labels = @json($byCategory->keys());
    const values = @json($byCategory->values()->map(fn($v) => (float) $v));
    if (labels.length && document.getElementById('expensePie')) {
        const palette = ['#ef4444','#f59e0b','#0ea5e9','#10b981','#6366f1','#ec4899','#f97316','#84cc16','#06b6d4','#8b5cf6','#14b8a6','#d946ef'];
        new Chart(document.getElementById('expensePie'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data: values, backgroundColor: labels.map((_, i) => palette[i % palette.length]), borderWidth: 0 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: textColor, boxWidth: 12, padding: 10 } } }
            }
        });
    }
});
</script>
@endpush
