@extends('layouts.app')
@section('title', 'Office Expense Report')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.reports.index') }}">Financial Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">Office Expenses</li>
            </ol>
        </nav>
        <h1 class="page-title mb-1">Office Expense Report</h1>
        <p class="text-muted mb-0">Non-project expenses of the office</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('finance.reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4 d-print-none">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
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
        <div class="fin-kpi-card fin-expense">
            <div class="fin-kpi-icon"><i class="bi bi-building"></i></div>
            <div class="fin-kpi-label">Current Month</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($currentMonth, 2) }}</div>
            <div class="fin-kpi-sub">{{ now()->format('F Y') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-receivable">
            <div class="fin-kpi-icon"><i class="bi bi-calendar3"></i></div>
            <div class="fin-kpi-label">Previous Month</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($prevMonth, 2) }}</div>
            <div class="fin-kpi-sub">{{ now()->subMonth()->format('F Y') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        @php $changePct = $prevMonth > 0 ? (($currentMonth - $prevMonth) / $prevMonth) * 100 : null; @endphp
        <div class="fin-kpi-card {{ $changePct === null || $changePct >= 0 ? 'fin-expense' : 'fin-profit' }}">
            <div class="fin-kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="fin-kpi-label">vs Last Month</div>
            <div class="fin-kpi-value {{ $changePct !== null && $changePct < 0 ? 'text-success' : '' }}">{{ $changePct !== null ? number_format(abs($changePct), 1).'%' : '—' }}</div>
            <div class="fin-kpi-sub">{{ $changePct === null ? 'No previous data' : ($changePct >= 0 ? 'Increase' : 'Decrease') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-vendor">
            <div class="fin-kpi-icon"><i class="bi bi-list-check"></i></div>
            <div class="fin-kpi-label">Selected Period</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($total, 2) }}</div>
            <div class="fin-kpi-sub">{{ $expenses->count() }} records</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-5 d-print-none">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-pie-chart me-2 text-warning"></i>Category Breakdown</h5></div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if($byCategory->count() > 0)
                <div class="chart-wrapper-sm w-100"><canvas id="officePie"></canvas></div>
                @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-pie-chart fs-1 d-block mb-2 opacity-25"></i>
                    No office expenses in selected period
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-7 d-print-none">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Largest Expenses</h5></div>
            <div class="card-body">
                @php $top = $expenses->sortByDesc('amount')->take(5)->values(); @endphp
                @forelse($top as $i => $expense)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-light-subtle text-muted rounded-circle" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center">{{ $i + 1 }}</span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="text-truncate fw-medium small">{{ $expense->description ?: $expense->expense_number }}</div>
                        <div class="text-muted small">{{ $expense->category?->name ?? 'Uncategorized' }} · {{ $expense->expense_date->format('d M Y') }}</div>
                    </div>
                    <span class="fw-bold text-danger">{{ setting('general.currency','PKR') }} {{ number_format($expense->amount, 0) }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4">No expenses recorded</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-list-ul me-2 text-danger"></i>All Office Expenses</h5></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 fin-table">
            <thead>
                <tr>
                    <th>Expense No</th>
                    <th>Category</th>
                    <th>Vendor</th>
                    <th class="text-end">Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Added By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td data-label="Expense"><a href="{{ route('finance.expenses.show', $expense) }}" class="text-decoration-none">{{ $expense->expense_number }}</a></td>
                    <td data-label="Category">
                        <span class="badge rounded-pill" style="background:{{ $expense->category?->color ?? '#6c757d' }}20;color:{{ $expense->category?->color ?? '#6c757d' }}">{{ $expense->category?->name ?? 'Uncategorized' }}</span>
                    </td>
                    <td data-label="Vendor" class="text-muted small">{{ $expense->vendor?->name ?? '—' }}</td>
                    <td data-label="Amount" class="text-end fin-amount-out">{{ number_format($expense->amount, 2) }}</td>
                    <td data-label="Date" class="text-muted small">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td data-label="Status"><span class="badge bg-{{ $expense->statusBadge() }}-subtle text-{{ $expense->statusBadge() }} rounded-pill">{{ ucfirst($expense->status) }}</span></td>
                    <td data-label="Added By" class="text-muted small">{{ $expense->addedBy?->name ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5">
                    <i class="bi bi-building fs-1 d-block mb-2 opacity-25"></i>
                    <p class="text-muted mb-0">No office expenses in this period</p>
                </td></tr>
                @endforelse
            </tbody>
        </table>
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
    if (labels.length && document.getElementById('officePie')) {
        const palette = ['#f59e0b','#ef4444','#0ea5e9','#10b981','#6366f1','#ec4899','#f97316','#84cc16','#8b5cf6','#14b8a6'];
        new Chart(document.getElementById('officePie'), {
            type: 'pie',
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
