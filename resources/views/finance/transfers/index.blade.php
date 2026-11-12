@extends('layouts.app')
@section('title', 'Account Transfers')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Account Transfers</li>
            </ol>
        </nav>
        <h1 class="page-title mb-1">Account Transfers</h1>
        <p class="text-muted mb-0">Move money between cash and bank accounts</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('finance.transactions.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        @can('transactions.create')
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#transferModal">
            <i class="bi bi-arrow-left-right me-1"></i>New Transfer
        </button>
        @endcan
    </div>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="fin-kpi-card fin-cash">
            <div class="fin-kpi-icon"><i class="bi bi-shuffle"></i></div>
            <div class="fin-kpi-label">Total Transfers</div>
            <div class="fin-kpi-value">{{ $records->total() }}</div>
            <div class="fin-kpi-sub">Recorded movements</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="fin-kpi-card fin-bank">
            <div class="fin-kpi-icon"><i class="bi bi-bank2"></i></div>
            <div class="fin-kpi-label">Total Transferred</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($records->sum('amount'), 2) }}</div>
            <div class="fin-kpi-sub">Current page</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="fin-kpi-card fin-vendor">
            <div class="fin-kpi-icon"><i class="bi bi-check2-circle"></i></div>
            <div class="fin-kpi-label">Latest Transfer</div>
            <div class="fin-kpi-value">{{ $records->first()?->transfer_number ?? '—' }}</div>
            <div class="fin-kpi-sub">{{ $records->first()?->transfer_date?->format('d M Y') ?? 'No transfers yet' }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 fin-table table-cards-lg">
            <thead>
                <tr>
                    <th>Transfer No</th>
                    <th>From</th>
                    <th>To</th>
                    <th class="text-end">Amount</th>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Created By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $transfer)
                <tr>
                    <td data-label="Transfer No"><span class="fw-semibold font-monospace small">{{ $transfer->transfer_number }}</span></td>
                    <td data-label="From">
                        <span class="badge bg-cyan-soft rounded-pill"><i class="bi {{ $transfer->from_account_type === 'cash' ? 'bi-cash-coin' : 'bi-bank' }} me-1"></i>{{ $transfer->fromLabel() }}</span>
                    </td>
                    <td data-label="To">
                        <span class="badge bg-primary-soft rounded-pill"><i class="bi {{ $transfer->to_account_type === 'cash' ? 'bi-cash-coin' : 'bi-bank' }} me-1"></i>{{ $transfer->toLabel() }}</span>
                    </td>
                    <td data-label="Amount" class="text-end fw-bold">{{ setting('general.currency', 'PKR') }} {{ number_format($transfer->amount, 2) }}</td>
                    <td data-label="Date" class="text-muted small">{{ $transfer->transfer_date->format('d M Y') }}</td>
                    <td data-label="Reference" class="text-muted small">{{ $transfer->reference ?? '—' }}</td>
                    <td data-label="Created By" class="text-muted small">{{ $transfer->createdBy?->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-shuffle fs-1 d-block mb-2 opacity-25"></i>
                        <p class="text-muted mb-0">No transfers recorded yet</p>
                        @can('transactions.create')
                        <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#transferModal">Record First Transfer</button>
                        @endcan
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

{{-- New Transfer Modal --}}
@can('transactions.create')
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2 text-primary"></i>New Account Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('finance.transfers.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">From Account <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 flex-wrap mb-2">
                                <div class="form-check">
                                    <input type="radio" name="from_account_type" id="from_cash" value="cash" class="form-check-input" checked>
                                    <label for="from_cash" class="form-check-label">Cash</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="from_account_type" id="from_bank" value="bank" class="form-check-input">
                                    <label for="from_bank" class="form-check-label">Bank</label>
                                </div>
                            </div>
                            <select name="from_cash_account_id" class="form-select mb-2" id="from-cash-select">
                                <option value="">— Select Cash Account —</option>
                                @foreach($cashAccounts as $a)
                                <option value="{{ $a->id }}">{{ $a->name }} (Balance: {{ number_format($a->current_balance,2) }})</option>
                                @endforeach
                            </select>
                            <select name="from_bank_account_id" class="form-select" id="from-bank-select" style="display:none">
                                <option value="">— Select Bank Account —</option>
                                @foreach($bankAccounts as $a)
                                <option value="{{ $a->id }}">{{ $a->bank_name }} – {{ $a->account_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">To Account <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 flex-wrap mb-2">
                                <div class="form-check">
                                    <input type="radio" name="to_account_type" id="to_cash" value="cash" class="form-check-input" checked>
                                    <label for="to_cash" class="form-check-label">Cash</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="to_account_type" id="to_bank" value="bank" class="form-check-input">
                                    <label for="to_bank" class="form-check-label">Bank</label>
                                </div>
                            </div>
                            <select name="to_cash_account_id" class="form-select mb-2" id="to-cash-select">
                                <option value="">— Select Cash Account —</option>
                                @foreach($cashAccounts as $a)
                                <option value="{{ $a->id }}">{{ $a->name }} (Balance: {{ number_format($a->current_balance,2) }})</option>
                                @endforeach
                            </select>
                            <select name="to_bank_account_id" class="form-select" id="to-bank-select" style="display:none">
                                <option value="">— Select Bank Account —</option>
                                @foreach($bankAccounts as $a)
                                <option value="{{ $a->id }}">{{ $a->bank_name }} – {{ $a->account_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ setting('general.currency', 'PKR') }}</span>
                                <input type="number" name="amount" step="0.01" min="0.01" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Transfer Date <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference" class="form-control" placeholder="Optional reference">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2" class="form-control" placeholder="Optional note..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Record Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
@push('scripts')
<script>
function bindAccountToggles() {
    const pairs = [
        { type: 'from_account_type', cash: 'from-cash-select', bank: 'from-bank-select' },
        { type: 'to_account_type', cash: 'to-cash-select', bank: 'to-bank-select' },
    ];
    pairs.forEach(({ type, cash, bank }) => {
        document.querySelectorAll(`[name="${type}"]`).forEach(r => {
            r.addEventListener('change', () => {
                document.getElementById(cash).style.display = r.value === 'cash' ? '' : 'none';
                document.getElementById(bank).style.display = r.value === 'bank' ? '' : 'none';
            });
        });
    });
}
bindAccountToggles();
</script>
@endpush
