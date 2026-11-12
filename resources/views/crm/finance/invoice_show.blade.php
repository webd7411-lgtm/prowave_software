@extends('layouts.app')
@section('title', 'Invoice ' . $invoice->invoice_number)
@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('crm.invoices.index') }}">Invoices</a></li>
            <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h1 class="page-title">{{ $invoice->invoice_number }}</h1>
            <p class="text-muted mb-0">Created {{ $invoice->created_at->format('M d, Y') }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('crm.invoices.print', $invoice) }}" class="btn btn-outline-success" target="_blank">
                <i class="bi bi-printer me-1"></i>Print Slip
            </a>
            <a href="{{ route('crm.invoices.edit', $invoice) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('crm.invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('crm.invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items as $item)
                            <tr>
                                <td data-label="Description">{{ $item->description }}</td>
                                <td data-label="Qty" class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                <td data-label="Unit Price" class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td data-label="Discount" class="text-end">{{ $item->discount > 0 ? '-' . number_format($item->discount, 2) : '—' }}</td>
                                <td data-label="Tax" class="text-end">{{ $item->tax > 0 ? number_format($item->tax, 2) : '—' }}</td>
                                <td data-label="Total" class="text-end fw-semibold">{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No items</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Subtotal</td>
                                <td class="text-end fw-semibold">{{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                            @if($invoice->discount > 0)
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Discount</td>
                                <td class="text-end fw-semibold text-danger">-{{ number_format($invoice->discount, 2) }}</td>
                            </tr>
                            @endif
                            @if($invoice->tax > 0)
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Tax</td>
                                <td class="text-end fw-semibold">{{ number_format($invoice->tax, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="table-active">
                                <td colspan="5" class="text-end fw-bold fs-5">Grand Total</td>
                                <td class="text-end fw-bold fs-5">{{ number_format($invoice->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if($invoice->notes)
        <div class="card mt-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-2">Notes</h6>
                <p class="text-muted mb-0">{{ $invoice->notes }}</p>
            </div>
        </div>
        @endif
        
        <div class="card mt-4 shadow-sm border">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                <div class="min-w-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-clock-history text-primary me-2"></i>Payment History & Receipts
                    </h6>
                    <small class="text-muted">List of all payments received from client for this invoice</small>
                </div>
                @can('payments.create')
                    @if($invoice->status !== 'paid' && $invoice->balance() > 0)
                        <button type="button" class="btn btn-sm btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                            <i class="bi bi-plus-circle me-1"></i>Record Payment
                        </button>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                            <i class="bi bi-plus-circle me-1"></i>Add Additional Payment
                        </button>
                    @endif
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt #</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Reference / Trx ID</th>
                                <th>Notes</th>
                                <th>Recorded By</th>
                                <th class="text-end">Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->payments as $payment)
                            <tr>
                                <td data-label="Receipt #" class="fw-semibold text-primary">
                                    <i class="bi bi-receipt me-1"></i>{{ $payment->payment_number }}
                                </td>
                                <td data-label="Date">{{ $payment->payment_date->format('d M, Y') }}</td>
                                <td data-label="Method">
                                    @php
                                        $methodIcons = [
                                            'cash' => 'bi-cash-stack text-success',
                                            'bank_transfer' => 'bi-bank text-primary',
                                            'jazzcash' => 'bi-phone text-danger',
                                            'easypaisa' => 'bi-wallet2 text-success',
                                            'card' => 'bi-credit-card text-info',
                                            'cheque' => 'bi-card-heading text-warning',
                                            'other' => 'bi-piggy-bank text-secondary',
                                        ];
                                        $icon = $methodIcons[$payment->method] ?? 'bi-credit-card';
                                    @endphp
                                    <span class="d-inline-flex align-items-center gap-1 badge bg-light text-dark border">
                                        <i class="bi {{ $icon }}"></i>
                                        {{ ucwords(str_replace('_', ' ', $payment->method)) }}
                                    </span>
                                </td>
                                <td data-label="Reference">
                                    @if($payment->reference_number)
                                        <code class="text-dark bg-light px-2 py-1 rounded">{{ $payment->reference_number }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td data-label="Notes"><small class="text-muted">{{ $payment->notes ?: '—' }}</small></td>
                                <td data-label="Recorded By"><small class="text-muted">{{ $payment->creator?->name ?? 'System' }}</small></td>
                                <td data-label="Amount Paid" class="text-end text-success fw-bold">
                                    + {{ number_format($payment->amount, 2) }} PKR
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-receipt-cutoff fs-3 d-block mb-2 text-muted opacity-50"></i>
                                    No payments have been recorded for this invoice yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($invoice->payments->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="6" class="text-end fw-bold">Total Payments Received:</td>
                                <td class="text-end text-success fw-bold fs-6">
                                    {{ number_format($invoice->payments->sum('amount'), 2) }} PKR
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card mb-4">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">Invoice Details</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Status</span>
                    @php
                        $sc = ['unpaid'=>'danger','partially_paid'=>'warning','paid'=>'success','cancelled'=>'secondary'];
                    @endphp
                    <span class="badge badge-soft-{{ $sc[$invoice->status] ?? 'secondary' }}">
                        {{ ucwords(str_replace('_',' ',$invoice->status)) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Date</span>
                    <span>{{ $invoice->invoice_date->format('M d, Y') }}</span>
                </div>
                @if($invoice->due_date)
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Due Date</span>
                    <span>{{ $invoice->due_date->format('M d, Y') }}</span>
                </div>
                @endif
                
                <div class="border-top pt-3 mt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Amount</span>
                        <span class="fw-semibold">{{ number_format($invoice->total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Amount Paid</span>
                        <span class="text-success fw-semibold">{{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fw-bold">Balance Due</span>
                        <span class="text-danger fw-bold fs-5">{{ number_format($invoice->balance(), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3">Billed To</h6>
                <div class="mb-1 fw-semibold">{{ $invoice->client->company_name }}</div>
                @if($invoice->client->contact_person)
                    <div class="text-muted small">{{ $invoice->client->contact_person }}</div>
                @endif
                @if($invoice->client->email)
                    <div class="text-muted small">{{ $invoice->client->email }}</div>
                @endif
                @if($invoice->client->phone)
                    <div class="text-muted small">{{ $invoice->client->phone }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@can('payments.create')
{{-- Record Payment Modal --}}
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('crm.payments.store') }}">
                @csrf
                <input type="hidden" name="client_id" value="{{ $invoice->client_id }}">
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="recordPaymentModalLabel">
                        <i class="bi bi-cash-coin me-2 text-success"></i>Record Payment for {{ $invoice->invoice_number }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 mb-3 fs-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Client: <strong>{{ $invoice->client->company_name }}</strong></span>
                            <span>Balance Due: <strong class="text-danger fs-6">{{ number_format($invoice->balance(), 2) }} PKR</strong></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold required">Payment Date</label>
                        <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold required">Amount Received (PKR)</label>
                        <input type="number" name="amount" min="0.01" max="{{ max(0.01, $invoice->balance() ?: $invoice->total) }}" step="0.01" value="{{ number_format($invoice->balance() > 0 ? $invoice->balance() : $invoice->total, 2, '.', '') }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold required">Payment Method</label>
                        <select name="method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="jazzcash">JazzCash</option>
                            <option value="easypaisa">EasyPaisa</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reference / Transaction ID</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. TRX-987654 or Cheque #102">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add payment note or details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalForm = document.querySelector('#recordPaymentModal form');
    if (modalForm) {
        modalForm.addEventListener('submit', function (e) {
            const amountInput = modalForm.querySelector('input[name="amount"]');
            if (amountInput) {
                const maxVal = parseFloat(amountInput.getAttribute('max'));
                const enteredVal = parseFloat(amountInput.value);
                if (maxVal && enteredVal > maxVal) {
                    e.preventDefault();
                    alert(`Payment amount (${enteredVal.toLocaleString()} PKR) cannot exceed remaining invoice balance of ${maxVal.toLocaleString()} PKR!`);
                    amountInput.value = maxVal;
                }
            }
        });
    }
});
</script>
@endpush
@endsection
