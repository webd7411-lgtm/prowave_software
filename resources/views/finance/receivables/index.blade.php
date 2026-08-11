@extends('layouts.app')
@section('title', 'Client Receivables')
@section('content')
@php
function invStatusBadge(string $status): string {
    return match($status) {
        'paid' => 'bg-success-subtle text-success',
        'partially_paid' => 'bg-warning-subtle text-warning',
        'overdue' => 'bg-danger-subtle text-danger',
        'sent' => 'bg-info-subtle text-info',
        'draft' => 'bg-secondary-subtle text-secondary',
        'cancelled' => 'bg-secondary-subtle text-secondary',
        default => 'bg-secondary-subtle text-secondary',
    };
}
@endphp
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Receivables</li>
            </ol>
        </nav>
        <h1 class="page-title mb-1">Outstanding Receivables</h1>
        <p class="text-muted mb-0">Invoices with pending client payments</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('finance.receivables.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
        <a href="{{ route('finance.receivables.index') }}?overdue_only=1" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-exclamation-triangle me-1"></i>Overdue Only
        </a>
    </div>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-receivable">
            <div class="fin-kpi-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="fin-kpi-label">Total Outstanding</div>
            <div class="fin-kpi-value">{{ setting('general.currency', 'PKR') }} {{ number_format($totalOutstanding, 0) }}</div>
            <div class="fin-kpi-sub">All unpaid invoices</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-expense">
            <div class="fin-kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="fin-kpi-label">Overdue Invoices</div>
            <div class="fin-kpi-value">{{ $overdueCount }}</div>
            <div class="fin-kpi-sub">Past due date</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-vendor">
            <div class="fin-kpi-icon"><i class="bi bi-receipt"></i></div>
            <div class="fin-kpi-label">Open Invoices</div>
            <div class="fin-kpi-value">{{ $records->total() }}</div>
            <div class="fin-kpi-sub">Current list</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="fin-kpi-card fin-income">
            <div class="fin-kpi-icon"><i class="bi bi-bell"></i></div>
            <div class="fin-kpi-label">Reminders</div>
            <div class="fin-kpi-value">Manual</div>
            <div class="fin-kpi-sub">Email / WhatsApp / SMS ready</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <select name="client" class="form-select form-select-sm">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach(['sent','partially_paid','overdue'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-4 col-md-2 col-lg-2">
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="overdue_only" value="0">
                    <input class="form-check-input" type="checkbox" name="overdue_only" value="1" id="overdue_only" {{ request('overdue_only') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="overdue_only">Overdue only</label>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2"><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm"></div>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2"><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm"></div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-1">
                <button class="btn btn-primary btn-sm w-100" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 fin-table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Client</th>
                    <th>Invoice Date</th>
                    <th>Due Date</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Outstanding</th>
                    <th class="text-end">Days Overdue</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $invoice)
                @php
                    $daysOverdue = $invoice->due_date && $invoice->due_date->isPast() ? (int) $invoice->due_date->diffInDays(now()) : 0;
                @endphp
                <tr>
                    <td data-label="Invoice"><span class="fw-semibold font-monospace small">{{ $invoice->invoice_number }}</span></td>
                    <td data-label="Client">
                        <a href="{{ route('crm.clients.show', $invoice->client) }}" class="text-decoration-none fw-medium">{{ $invoice->client?->company_name ?? '—' }}</a>
                    </td>
                    <td data-label="Invoice Date" class="text-muted small">{{ $invoice->invoice_date->format('d M Y') }}</td>
                    <td data-label="Due Date" class="text-muted small {{ $daysOverdue > 0 ? 'text-danger fw-semibold' : '' }}">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                    <td data-label="Total" class="text-end">{{ setting('general.currency','PKR') }} {{ number_format($invoice->total, 0) }}</td>
                    <td data-label="Paid" class="text-end fin-amount-in">{{ number_format($invoice->paid_amount, 0) }}</td>
                    <td data-label="Outstanding" class="text-end fin-amount-out">{{ number_format($invoice->balance(), 0) }}</td>
                    <td data-label="Days Overdue" class="text-end">
                        @if($daysOverdue > 0)
                        <span class="badge bg-danger-subtle text-danger rounded-pill">{{ $daysOverdue }}d</span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td data-label="Status"><span class="badge {{ invStatusBadge($invoice->status) }} rounded-pill">{{ ucfirst(str_replace('_',' ',$invoice->status)) }}</span></td>
                    <td data-label="Actions" class="text-end fin-cell-actions">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('crm.invoices.index') }}" class="btn btn-xs btn-outline-secondary" title="View Invoice"><i class="bi bi-eye"></i></a>
                            @can('payments.create')
                            <button class="btn btn-xs btn-outline-success" title="Record Payment"
                                data-bs-toggle="modal" data-bs-target="#paymentModal"
                                data-client="{{ $invoice->client_id }}"
                                data-client-name="{{ $invoice->client?->company_name ?? '' }}"
                                data-invoice="{{ $invoice->id }}"
                                data-invoice-number="{{ $invoice->invoice_number }}"
                                data-amount="{{ $invoice->balance() }}">
                                <i class="bi bi-cash-coin"></i>
                            </button>
                            @endcan
                            @can('receivables.view')
                            <button class="btn btn-xs btn-outline-warning" title="Send Reminder"
                                data-bs-toggle="modal" data-bs-target="#reminderModal"
                                data-invoice-url="{{ route('finance.receivables.reminder', $invoice) }}"
                                data-invoice-number="{{ $invoice->invoice_number }}"
                                data-client-name="{{ $invoice->client?->company_name ?? '' }}">
                                <i class="bi bi-bell"></i>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i class="bi bi-check2-circle fs-1 d-block mb-2 opacity-25 text-success"></i>
                        <p class="text-muted mb-0">No outstanding receivables found</p>
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

{{-- Record Payment Modal --}}
@can('payments.create')
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-2 text-success"></i>Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('crm.payments.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="client_id" id="pay-client">
                    <input type="hidden" name="invoice_id" id="pay-invoice">
                    <div class="alert alert-light border small mb-3" id="pay-summary">Select an invoice to record a payment.</div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ setting('general.currency', 'PKR') }}</span>
                                <input type="number" name="amount" id="pay-amount" step="0.01" min="0.01" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold">Method <span class="text-danger">*</span></label>
                            <select name="method" class="form-select" required>
                                @foreach(['cash','bank_transfer','jazzcash','easypaisa','card','cheque','other'] as $m)
                                <option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Cheque/Transfer no">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- Reminder Modal --}}
<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bell me-2 text-warning"></i>Send Payment Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="reminder-form">
                @csrf
                <div class="modal-body">
                    <p class="small text-muted mb-3" id="reminder-summary">Invoice reminder</p>
                    <label class="form-label fw-semibold">Reminder Message</label>
                    <textarea name="message" rows="3" class="form-control" placeholder="Dear client, your invoice is due. Please arrange payment at your earliest convenience."></textarea>
                    <div class="alert alert-light border small mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Reminders are recorded now. Email, WhatsApp and SMS delivery channels are configurable in a future release.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="bi bi-send me-1"></i>Send Reminder</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-bs-target="#paymentModal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const client = this.dataset.client;
        const invoice = this.dataset.invoice;
        const clientName = this.dataset.clientName;
        const invNumber = this.dataset.invoiceNumber;
        const amount = this.dataset.amount;
        document.getElementById('pay-client').value = client;
        document.getElementById('pay-invoice').value = invoice;
        document.getElementById('pay-amount').value = amount;
        document.getElementById('pay-summary').innerHTML =
            `Recording payment for <strong>${clientName}</strong> against invoice <strong>${invNumber}</strong> (outstanding: <strong>${amount}</strong>).`;
    });
});
document.querySelectorAll('[data-bs-target="#reminderModal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('reminder-form').action = this.dataset.invoiceUrl;
        document.getElementById('reminder-summary').textContent =
            `Sending reminder for invoice ${this.dataset.invoiceNumber} to ${this.dataset.clientName}.`;
    });
});
</script>
@endpush
