@extends('layouts.app')
@section('title', 'Finance Dashboard')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Finance Dashboard</h1>
        <p class="text-muted mb-0">Financial overview for {{ \Carbon\Carbon::parse($stats['start'])->format('d M') }} – {{ \Carbon\Carbon::parse($stats['end'])->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('finance.expenses.create') }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Add Expense
        </a>
        <a href="{{ route('finance.income.create') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Add Income
        </a>
        <a href="{{ route('finance.reports.profit-loss') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-bar-chart-line me-1"></i>P&L Report
        </a>
    </div>
</div>

{{-- KPI Row 1 --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card fin-income">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <div class="fin-kpi-label">Total Income</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($stats['totalIncome'], 0) }}</div>
            <div class="fin-kpi-sub">This month</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card fin-expense">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div class="fin-kpi-label">Total Expenses</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($stats['totalExpenses'], 0) }}</div>
            <div class="fin-kpi-sub">This month</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card {{ $stats['netProfit'] >= 0 ? 'fin-profit' : 'fin-loss' }}">
            <div class="fin-kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="fin-kpi-label">Net Profit</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format(abs($stats['netProfit']), 0) }}</div>
            <div class="fin-kpi-sub">{{ $stats['netProfit'] >= 0 ? 'Profit' : 'Loss' }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card fin-receivable">
            <div class="fin-kpi-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="fin-kpi-label">Receivables</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($stats['invoiceStats']->pending_receivables ?? 0, 0) }}</div>
            <div class="fin-kpi-sub">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card fin-cash">
            <div class="fin-kpi-icon"><i class="bi bi-cash-coin"></i></div>
            <div class="fin-kpi-label">Cash Balance</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($stats['cashBalance'], 0) }}</div>
            <div class="fin-kpi-sub">All accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="fin-kpi-card fin-bank">
            <div class="fin-kpi-icon"><i class="bi bi-bank2"></i></div>
            <div class="fin-kpi-label">Bank Balance</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($stats['bankBalance'], 0) }}</div>
            <div class="fin-kpi-sub">All accounts</div>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Income vs Expenses (12 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="incomeExpenseChart" style="max-height:280px"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart me-2 text-warning"></i>Expense Breakdown</h5>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if(count($breakdown) > 0)
                <canvas id="expenseBreakdown" style="max-height:280px"></canvas>
                @else
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bar-chart-line fs-1 d-block mb-2 opacity-25"></i>
                    No expense data for this month
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Invoice stats + accounts + recent --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card text-center">
            <div class="card-body py-4">
                <div class="fs-1 fw-bold text-success">{{ $stats['invoiceStats']->paid_count ?? 0 }}</div>
                <div class="text-muted">Paid Invoices</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card text-center">
            <div class="card-body py-4">
                <div class="fs-1 fw-bold text-warning">{{ $stats['invoiceStats']->unpaid_count ?? 0 }}</div>
                <div class="text-muted">Unpaid Invoices</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card text-center">
            <div class="card-body py-4">
                <div class="fs-2 fw-bold text-primary">{{ setting('general.currency', 'PKR') }} {{ number_format($stats['invoiceStats']->paid_total ?? 0, 0) }}</div>
                <div class="text-muted">Collected</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card text-center">
            <div class="card-body py-4">
                <div class="fs-2 fw-bold text-danger">{{ setting('general.currency', 'PKR') }} {{ number_format($stats['invoiceStats']->pending_receivables ?? 0, 0) }}</div>
                <div class="text-muted">Pending</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Recent Expenses --}}
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="bi bi-receipt-cutoff me-2 text-danger"></i>Recent Expenses</h5>
                <a href="{{ route('finance.expenses.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <tbody>
                        @forelse($recentExpenses as $exp)
                        <tr>
                            <td>
                                <span class="badge" style="background:{{ $exp->category?->color ?? '#6c757d' }}20;color:{{ $exp->category?->color ?? '#6c757d' }}">{{ $exp->category?->name ?? 'Uncategorized' }}</span>
                            </td>
                            <td class="text-muted small">{{ $exp->expense_date->format('d M') }}</td>
                            <td class="fw-semibold text-danger">{{ number_format($exp->amount, 0) }}</td>
                            <td><span class="badge bg-{{ $exp->statusBadge() }}-subtle text-{{ $exp->statusBadge() }}">{{ ucfirst($exp->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No expenses yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Overdue Invoices --}}
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Overdue Invoices</h5>
                <a href="{{ route('finance.receivables.index') }}?overdue_only=1" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <tbody>
                        @forelse($overdueInvoices as $inv)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $inv->client?->company_name }}</div>
                                <div class="text-muted small">{{ $inv->invoice_number }}</div>
                            </td>
                            <td class="text-danger small">{{ $inv->due_date?->format('d M Y') }}</td>
                            <td class="fw-semibold">{{ setting('general.currency', 'PKR') }} {{ number_format($inv->balance(), 0) }}</td>
                            <td>
                                <a href="{{ route('finance.receivables.index') }}" class="btn btn-xs btn-outline-warning py-0 px-1">
                                    <i class="bi bi-bell"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-success py-3"><i class="bi bi-check-circle me-1"></i>No overdue invoices</td></tr>
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

    const monthly = @json($monthlyChart);
    const labels  = monthly.map(m => m.label);
    const income  = monthly.map(m => m.income);
    const expense = monthly.map(m => m.expenses);

    // Income vs Expense Chart
    new Chart(document.getElementById('incomeExpenseChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Income', data: income, backgroundColor: 'rgba(25,135,84,0.7)', borderRadius: 4 },
                { label: 'Expenses', data: expense, backgroundColor: 'rgba(220,53,69,0.7)', borderRadius: 4 },
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

    // Expense Breakdown Doughnut
    const breakdown = @json($breakdown);
    if (breakdown.length && document.getElementById('expenseBreakdown')) {
        new Chart(document.getElementById('expenseBreakdown'), {
            type: 'doughnut',
            data: {
                labels: breakdown.map(b => b.label),
                datasets: [{
                    data: breakdown.map(b => b.total),
                    backgroundColor: breakdown.map(b => b.color),
                    borderWidth: 0,
                }]
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
