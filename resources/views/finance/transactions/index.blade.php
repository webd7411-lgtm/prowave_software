@extends('layouts.app')
@section('title', 'Transactions')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Transactions</h1>
        <p class="text-muted mb-0">Complete history of all financial movements</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('transactions.create')
            <a href="{{ route('finance.transactions.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Add Transaction
            </a>
        @endcan
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Print
        </button>
    </div>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-income">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <div class="fin-kpi-label">Total Credit (In)</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($totalCredit, 2) }}</div>
            <div class="fin-kpi-sub">Filtered results</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-expense">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div class="fin-kpi-label">Total Debit (Out)</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($totalDebit, 2) }}</div>
            <div class="fin-kpi-sub">Filtered results</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card {{ ($totalCredit - $totalDebit) >= 0 ? 'fin-profit' : 'fin-loss' }}">
            <div class="fin-kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="fin-kpi-label">Net Flow</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format(abs($totalCredit - $totalDebit), 2) }}</div>
            <div class="fin-kpi-sub">{{ ($totalCredit - $totalDebit) >= 0 ? 'Net Inflow' : 'Net Outflow' }}</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-receivable">
            <div class="fin-kpi-icon"><i class="bi bi-list-check"></i></div>
            <div class="fin-kpi-label">Records</div>
            <div class="fin-kpi-value">{{ $records->total() }}</div>
            <div class="fin-kpi-sub">Showing {{ $records->firstItem() ?? 0 }} – {{ $records->lastItem() ?? 0 }}</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach(['income','expense','transfer','payment','refund','adjustment'] as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <select name="account_type" class="form-select form-select-sm">
                    <option value="">All Accounts</option>
                    <option value="cash" {{ request('account_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank" {{ request('account_type') === 'bank' ? 'selected' : '' }}>Bank</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <select name="cash_account" class="form-select form-select-sm">
                    <option value="">All Cash Accounts</option>
                    @foreach($cashAccounts as $a)
                    <option value="{{ $a->id }}" {{ request('cash_account') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <select name="bank_account" class="form-select form-select-sm">
                    <option value="">All Bank Accounts</option>
                    @foreach($bankAccounts as $a)
                    <option value="{{ $a->id }}" {{ request('bank_account') == $a->id ? 'selected' : '' }}>{{ $a->bank_name }} – {{ $a->account_title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-2 col-lg-2"><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm" placeholder="From"></div>
            <div class="col-6 col-sm-4 col-md-2 col-lg-2"><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm" placeholder="To"></div>
            <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search reference/description...">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    @if(request()->hasAny(['type','account_type','cash_account','bank_account','from_date','to_date','search']))
                    <a href="{{ route('finance.transactions.index') }}" class="btn btn-outline-secondary">×</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 fin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th>Account</th>
                    <th>Type</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                    <th class="text-end">Balance</th>
                    <th>Created By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $txn)
                <tr>
                    <td data-label="Date" class="text-muted small">{{ $txn->transaction_date->format('d M Y') }}</td>
                    <td data-label="Reference"><span class="fw-semibold font-monospace small">{{ $txn->reference }}</span></td>
                    <td data-label="Description" class="text-muted small">{{ $txn->description ?? '—' }}</td>
                    <td data-label="Account" class="text-muted small">
                        @if($txn->account_type === 'cash' && $txn->cashAccount)
                        <span class="badge bg-cyan-soft rounded-pill">{{ $txn->cashAccount->name }}</span>
                        @elseif($txn->account_type === 'bank' && $txn->bankAccount)
                        <span class="badge bg-primary-soft rounded-pill">{{ $txn->bankAccount->bank_name }}</span>
                        @else —
                        @endif
                    </td>
                    <td data-label="Type">
                        <span class="badge bg-{{ $txn->typeBadge() }}-subtle text-{{ $txn->typeBadge() }} rounded-pill">{{ ucfirst($txn->type) }}</span>
                    </td>
                    <td data-label="Debit" class="text-end fin-amount-out">{{ $txn->debit > 0 ? number_format($txn->debit, 2) : '—' }}</td>
                    <td data-label="Credit" class="text-end fin-amount-in">{{ $txn->credit > 0 ? number_format($txn->credit, 2) : '—' }}</td>
                    <td data-label="Balance" class="text-end small text-muted">{{ number_format($txn->balance_after, 2) }}</td>
                    <td data-label="Created By" class="text-muted small">{{ $txn->createdBy?->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bi bi-arrow-left-right fs-1 d-block mb-2 opacity-25"></i>
                        <p class="text-muted mb-0">No transactions found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="card-footer">{{ $records->links() }}</div>
    @endif
</div>
@endsection
