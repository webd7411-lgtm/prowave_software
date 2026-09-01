@extends('layouts.app')
@section('title', 'Payments')
@section('content')
<div class="page-header mb-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Payments</li>
            </ol>
        </nav>
        <h1 class="page-title">Payments</h1>
        <p class="page-subtitle">Record payments securely and keep balances automatically current.</p>
    </div>
    <a href="{{ route('crm.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to CRM
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Error:</strong> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-4">
        <x-card title="Record Payment" body-class="p-3">
            <form method="POST" action="{{ route('crm.payments.store') }}" id="paymentForm">
                @csrf
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Client *</label>
                    <select name="client_id" id="clientSelect" class="form-select" required>
                        <option value="">Select Client *</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Linked Invoice (Optional)</label>
                    <select name="invoice_id" id="invoiceSelect" class="form-select">
                        <option value="" data-balance="" data-client="">No linked invoice</option>
                        @foreach($invoices as $invoice)
                            @if($invoice->balance() > 0)
                            <option value="{{ $invoice->id }}" 
                                    data-balance="{{ number_format($invoice->balance(), 2, '.', '') }}" 
                                    data-client="{{ $invoice->client_id }}"
                                    {{ old('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                {{ $invoice->invoice_number }} — {{ $invoice->client->company_name }} (Due: {{ number_format($invoice->balance(), 2) }} PKR)
                            </option>
                            @endif
                        @endforeach
                    </select>
                    <div id="balanceNotice" class="form-text text-primary fw-semibold mt-1 d-none"></div>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Payment Date *</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Amount (PKR) *</label>
                    <input type="number" name="amount" id="amountInput" min="0.01" step="0.01" value="{{ old('amount') }}" class="form-control" placeholder="Amount" required>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-semibold">Payment Method *</label>
                    <select name="method" class="form-select" required>
                        @foreach(['cash','bank_transfer','jazzcash','easypaisa','card','cheque','other'] as $method)
                            <option value="{{ $method }}" {{ old('method') == $method ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_',' ',$method)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Reference / Trx ID</label>
                    <input name="reference_number" value="{{ old('reference_number') }}" class="form-control" placeholder="Reference number or Cheque #">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle me-1"></i>Record Payment
                </button>
            </form>
        </x-card>
    </div>

    <div class="col-lg-8">
        <x-card body-class="p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt #</th>
                            <th>Client</th>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td class="fw-semibold text-primary">
                                <i class="bi bi-receipt me-1"></i>{{ $record->payment_number }}
                            </td>
                            <td>{{ $record->client->company_name }}</td>
                            <td>
                                @if($record->invoice)
                                    <a href="{{ route('crm.invoices.show', $record->invoice) }}" class="text-decoration-none fw-semibold">
                                        {{ $record->invoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $record->payment_date->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucwords(str_replace('_',' ',$record->method)) }}
                                </span>
                            </td>
                            <td class="text-end text-success fw-bold">{{ number_format($record->amount, 2) }} PKR</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No payments recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($records->hasPages())
                <div class="card-footer">
                    {{ $records->links() }}
                </div>
            @endif
        </x-card>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const invoiceSelect = document.getElementById('invoiceSelect');
    const clientSelect = document.getElementById('clientSelect');
    const amountInput = document.getElementById('amountInput');
    const balanceNotice = document.getElementById('balanceNotice');
    const paymentForm = document.getElementById('paymentForm');

    function handleInvoiceChange() {
        if (!invoiceSelect) return;
        const selectedOption = invoiceSelect.options[invoiceSelect.selectedIndex];
        const maxBalance = selectedOption.getAttribute('data-balance');
        const clientId = selectedOption.getAttribute('data-client');

        if (clientId && clientSelect) {
            clientSelect.value = clientId;
        }

        if (maxBalance && parseFloat(maxBalance) > 0) {
            const balanceVal = parseFloat(maxBalance);
            amountInput.max = balanceVal;
            if (!amountInput.value || parseFloat(amountInput.value) > balanceVal) {
                amountInput.value = balanceVal;
            }
            balanceNotice.textContent = `Invoice Remaining Balance: ${balanceVal.toLocaleString('en-US', {minimumFractionDigits: 2})} PKR (Cannot exceed this limit)`;
            balanceNotice.classList.remove('d-none');
        } else {
            amountInput.removeAttribute('max');
            balanceNotice.classList.add('d-none');
            balanceNotice.textContent = '';
        }
    }

    if (invoiceSelect) {
        invoiceSelect.addEventListener('change', handleInvoiceChange);
        if (invoiceSelect.value) {
            handleInvoiceChange();
        }
    }

    if (paymentForm) {
        paymentForm.addEventListener('submit', function (e) {
            const selectedOption = invoiceSelect.options[invoiceSelect.selectedIndex];
            const maxBalance = selectedOption.getAttribute('data-balance');

            if (maxBalance && parseFloat(maxBalance) > 0) {
                const balanceVal = parseFloat(maxBalance);
                const enteredAmount = parseFloat(amountInput.value);
                if (enteredAmount > balanceVal) {
                    e.preventDefault();
                    alert(`Payment amount (${enteredAmount.toLocaleString()} PKR) cannot exceed the remaining invoice balance of ${balanceVal.toLocaleString()} PKR!`);
                    amountInput.value = balanceVal;
                }
            }
        });
    }
});
</script>
@endpush
@endsection

