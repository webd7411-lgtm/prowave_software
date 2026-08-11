@extends('layouts.app')
@section('title', 'Vendor Details')
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('finance.vendors.index') }}">Vendors</a></li>
            <li class="breadcrumb-item active">{{ $vendor->name }}</li>
        </ol>
    </nav>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1 class="page-title mb-0">{{ $vendor->name }}</h1>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge {{ $vendor->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-pill align-self-center">{{ ucfirst($vendor->status) }}</span>
            @can('vendors.edit')
            <a href="{{ route('finance.vendors.edit', $vendor) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endcan
            @can('vendors.delete')
            <form method="POST" action="{{ route('finance.vendors.destroy', $vendor) }}" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete {{ $vendor->name }}? Existing records will be kept.')"><i class="bi bi-trash me-1"></i>Delete</button>
            </form>
            @endcan
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Vendor Number</div>
                        <div class="fw-semibold">{{ $vendor->vendor_number }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Contact Person</div>
                        <div>{{ $vendor->contact_person ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Category</div>
                        <div>{{ $vendor->category ?? '—' }}</div>
                    </div>
                    @if($vendor->phone)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Phone</div>
                        <div><a href="tel:{{ $vendor->phone }}" class="text-decoration-none">{{ $vendor->phone }}</a></div>
                    </div>
                    @endif
                    @if($vendor->whatsapp)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">WhatsApp</div>
                        <div>{{ $vendor->whatsapp }}</div>
                    </div>
                    @endif
                    @if($vendor->email)
                    <div class="col-6 col-md-4">
                        <div class="text-muted small">Email</div>
                        <div><a href="mailto:{{ $vendor->email }}" class="text-decoration-none">{{ $vendor->email }}</a></div>
                    </div>
                    @endif
                    @if($vendor->address)
                    <div class="col-12 col-md-6">
                        <div class="text-muted small">Address</div>
                        <div>{{ $vendor->address }}</div>
                    </div>
                    @endif
                    @if($vendor->notes)
                    <div class="col-12">
                        <div class="text-muted small">Notes</div>
                        <div class="bg-light rounded p-2 small">{{ $vendor->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Expenses --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bi bi-receipt-cutoff me-2 text-danger"></i>Expenses</h6>
                <span class="badge bg-danger-subtle text-danger rounded-pill">{{ setting('general.currency','PKR') }} {{ number_format($vendor->totalExpenses(), 0) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 fin-table">
                    <thead>
                        <tr>
                            <th>Expense No</th>
                            <th>Category</th>
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
                                <span class="badge rounded-pill" style="background:{{ $expense->category?->color ?? '#6c757d' }}20;color:{{ $expense->category?->color ?? '#6c757d' }}">{{ $expense->category?->name ?? '—' }}</span>
                            </td>
                            <td data-label="Amount" class="text-end fin-amount-out">{{ number_format($expense->amount, 2) }}</td>
                            <td data-label="Date" class="text-muted small">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td data-label="Status"><span class="badge bg-{{ $expense->statusBadge() }}-subtle text-{{ $expense->statusBadge() }} rounded-pill">{{ ucfirst($expense->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No expenses recorded for this vendor</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Payments --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="bi bi-cash-coin me-2 text-success"></i>Payments Made</h6>
                <span class="badge bg-success-subtle text-success rounded-pill">{{ setting('general.currency','PKR') }} {{ number_format($vendor->totalPaid(), 0) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 fin-table">
                    <thead>
                        <tr>
                            <th>Payment No</th>
                            <th>Expense</th>
                            <th class="text-end">Amount</th>
                            <th>Date</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendor->vendorPayments as $payment)
                        <tr>
                            <td data-label="Payment"><span class="fw-semibold font-monospace small">{{ $payment->payment_number }}</span></td>
                            <td data-label="Expense">{{ $payment->expense?->expense_number ?? '—' }}</td>
                            <td data-label="Amount" class="text-end fin-amount-out">{{ number_format($payment->amount, 2) }}</td>
                            <td data-label="Date" class="text-muted small">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td data-label="Method" class="text-muted small">{{ $payment->paymentMethod?->name ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No payments recorded yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">Financial Summary</h6></div>
            <div class="card-body d-grid gap-2">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Expenses</span>
                    <span class="fw-semibold text-danger">{{ setting('general.currency','PKR') }} {{ number_format($vendor->totalExpenses(), 0) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Paid</span>
                    <span class="fw-semibold text-success">{{ setting('general.currency','PKR') }} {{ number_format($vendor->totalPaid(), 0) }}</span>
                </div>
                <hr class="my-1">
                <div class="d-flex justify-content-between">
                    <span class="fw-semibold">Outstanding</span>
                    <span class="fw-bold {{ $vendor->totalExpenses() - $vendor->totalPaid() > 0 ? 'text-danger' : 'text-success' }}">{{ setting('general.currency','PKR') }} {{ number_format(max(0, $vendor->totalExpenses() - $vendor->totalPaid()), 0) }}</span>
                </div>
            </div>
        </div>

        @can('payments.create')
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-credit-card me-2 text-primary"></i>Record Payment</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('finance.vendors.payments.store', $vendor) }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Against Expense</label>
                            <select name="expense_id" class="form-select form-select-sm">
                                <option value="">— General Payment —</option>
                                @foreach($expenses as $expense)
                                <option value="{{ $expense->id }}">{{ $expense->expense_number }} ({{ number_format($expense->amount, 0) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0.01" class="form-control form-control-sm" placeholder="0.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pay From</label>
                            <div class="d-flex gap-2 flex-wrap mb-1">
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="account_type" id="vp_cash" value="cash" class="form-check-input" checked>
                                    <label for="vp_cash" class="form-check-label small">Cash</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="account_type" id="vp_bank" value="bank" class="form-check-input">
                                    <label for="vp_bank" class="form-check-label small">Bank</label>
                                </div>
                            </div>
                            <select name="cash_account_id" class="form-select form-select-sm mb-2" id="vp-cash-select">
                                <option value="">— Select Cash Account —</option>
                                @foreach($cashAccounts as $a)
                                <option value="{{ $a->id }}">{{ $a->name }}</option>
                                @endforeach
                            </select>
                            <select name="bank_account_id" class="form-select form-select-sm" id="vp-bank-select" style="display:none">
                                <option value="">— Select Bank Account —</option>
                                @foreach($bankAccounts as $a)
                                <option value="{{ $a->id }}">{{ $a->bank_name }} – {{ $a->account_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Method</label>
                            <select name="payment_method_id" class="form-select form-select-sm">
                                <option value="">— Method —</option>
                                @foreach($paymentMethods as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference_number" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control form-control-sm"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-check-circle me-1"></i>Record Payment</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[name="account_type"]').forEach(r => {
    r.addEventListener('change', function() {
        document.getElementById('vp-cash-select').style.display = this.value === 'cash' ? '' : 'none';
        document.getElementById('vp-bank-select').style.display = this.value === 'bank' ? '' : 'none';
    });
});
</script>
@endpush
