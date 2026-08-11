@extends('layouts.app')
@section('title', 'Bank Accounts')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="page-title mb-1">Bank Accounts</h1>
        <p class="text-muted mb-0">Manage company bank accounts and balances</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('accounts.create')
        <a href="{{ route('finance.accounts.bank.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Add Bank Account
        </a>
        @endcan
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-bank">
            <div class="fin-kpi-icon"><i class="bi bi-bank2"></i></div>
            <div class="fin-kpi-label">Total Bank Balance</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($accounts->where('status','active')->sum('current_balance'), 2) }}</div>
            <div class="fin-kpi-sub">{{ $accounts->count() }} account(s)</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-income">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
            <div class="fin-kpi-label">Total Deposits</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($accounts->sum(fn($a) => $a->totalIncome()), 2) }}</div>
            <div class="fin-kpi-sub">All transactions</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-expense">
            <div class="fin-kpi-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
            <div class="fin-kpi-label">Total Withdrawals</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($accounts->sum(fn($a) => $a->totalExpense()), 2) }}</div>
            <div class="fin-kpi-sub">All transactions</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-vendor">
            <div class="fin-kpi-icon"><i class="bi bi-patch-check"></i></div>
            <div class="fin-kpi-label">Active Accounts</div>
            <div class="fin-kpi-value">{{ $accounts->where('status','active')->count() }}</div>
            <div class="fin-kpi-sub">of {{ $accounts->count() }} total</div>
        </div>
    </div>
</div>

<div class="row g-3">
    @forelse($accounts as $account)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card fin-account-card">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="fin-account-icon"><i class="bi bi-bank"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate">{{ $account->bank_name }}</div>
                        <div class="text-muted small text-truncate">{{ $account->account_title }}</div>
                    </div>
                    <span class="badge {{ $account->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill">{{ ucfirst($account->status) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fin-account-label">Current Balance</span>
                    <span class="fin-account-balance {{ $account->current_balance < 0 ? 'text-danger' : '' }}">{{ setting('general.currency','PKR') }} {{ number_format($account->current_balance, 0) }}</span>
                </div>
                <div class="row g-2 text-center mb-3">
                    <div class="col-4">
                        <div class="fin-account-label">Deposits</div>
                        <div class="fw-bold text-success">{{ number_format($account->totalIncome(), 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="fin-account-label">Withdrawals</div>
                        <div class="fw-bold text-danger">{{ number_format($account->totalExpense(), 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="fin-account-label">Currency</div>
                        <div class="fw-semibold">{{ $account->currency }}</div>
                    </div>
                </div>
                <div class="small text-muted border-top pt-2">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Account No</span>
                        <span class="font-monospace">
                            @can('financial_data.manage')
                            {{ $account->account_number ?? '—' }}
                            @else
                            {{ $account->maskedAccountNumber() }}
                            @endcan
                        </span>
                    </div>
                    @can('financial_data.manage')
                    @if($account->iban)
                    <div class="d-flex justify-content-between">
                        <span>IBAN</span>
                        <span class="font-monospace">{{ $account->iban }}</span>
                    </div>
                    @endif
                    @endcan
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div class="text-muted small">Branch: {{ $account->branch ?? '—' }}</div>
                    @can('accounts.edit')
                    <a href="{{ route('finance.accounts.bank.edit', $account) }}" class="btn btn-xs btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-bank2 fs-1 d-block mb-2 opacity-25"></i>
                <p class="text-muted mb-0">No bank accounts yet</p>
                @can('accounts.create')
                <a href="{{ route('finance.accounts.bank.create') }}" class="btn btn-sm btn-primary mt-2">Add First Bank Account</a>
                @endcan
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
