<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} - {{ $invoice->client->company_name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            font-size: 13px;
            background: #f1f5f9;
            color: #0f172a;
        }
        .invoice-wrap {
            max-width: 780px;
            margin: 1.5rem auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        /* Header Band */
        .inv-header {
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            color: #fff;
            padding: 2rem 2.5rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }
        .company-name {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.3px;
        }
        .inv-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0.75;
            margin-bottom: 0.2rem;
        }
        .inv-number {
            font-size: 1.2rem;
            font-weight: 700;
        }
        /* Meta Row */
        .meta-band {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 2.5rem;
            display: flex;
            gap: 2.5rem;
            flex-wrap: wrap;
        }
        .meta-item label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            display: block;
            margin-bottom: 0.15rem;
        }
        .meta-item span {
            font-weight: 600;
            color: #1e293b;
        }
        /* Bill To / From */
        .parties {
            padding: 1.5rem 2.5rem;
            display: flex;
            gap: 2rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .party-box {
            flex: 1;
        }
        .party-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 0.4rem;
        }
        .party-name {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
        }
        .party-detail {
            font-size: 0.82rem;
            color: #475569;
            margin-top: 0.15rem;
        }
        /* Items Table */
        .items-section { padding: 0 2.5rem; }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }
        table.items-table thead tr {
            background: #f1f5f9;
        }
        table.items-table th {
            padding: 0.6rem 0.75rem;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #475569;
            font-weight: 700;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        table.items-table td {
            padding: 0.65rem 0.75rem;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
        }
        table.items-table tbody tr:hover { background: #fafafa; }
        /* Totals */
        .totals-section {
            padding: 0 2.5rem 1.5rem;
            display: flex;
            justify-content: space-between;
        }
        .payment-status {
            padding-top: 1rem;
            width: 250px;
        }
        .totals-box {
            width: 280px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.4rem 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 0.85rem;
        }
        .total-row:last-child {
            border-bottom: none;
            background: #eff6ff;
            padding: 0.6rem 0.75rem;
            border-radius: 6px;
            margin-top: 0.5rem;
            font-size: 1rem;
            font-weight: 800;
            color: #1e3a8a;
        }
        /* Notes / Terms */
        .notes-section {
            padding: 1rem 2.5rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 2rem;
        }
        .note-box {
            flex: 1;
        }
        .note-box h6 {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin-bottom: 0.4rem;
        }
        .note-box p {
            font-size: 0.82rem;
            color: #475569;
            margin: 0;
            white-space: pre-wrap;
        }
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.35rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid;
            transform: rotate(-10deg);
            margin-top: 1rem;
        }
        .status-unpaid         { color: #dc2626; border-color: #dc2626; }
        .status-partially_paid { color: #d97706; border-color: #d97706; }
        .status-paid           { color: #16a34a; border-color: #16a34a; }
        .status-cancelled      { color: #64748b; border-color: #64748b; }
        /* Footer */
        .inv-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 1rem 2.5rem;
            text-align: center;
            font-size: 0.78rem;
            color: #94a3b8;
        }
        /* No-print buttons */
        .no-print {
            max-width: 780px;
            margin: 1rem auto 0;
            text-align: center;
        }
        /* Print Styles */
        @media print {
            body { background: #fff !important; }
            .no-print { display: none !important; }
            .invoice-wrap {
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            @page { margin: 8mm; size: A4; }
        }
    </style>
</head>
<body>

<div class="no-print py-3">
    <button onclick="window.print()" class="btn btn-primary btn-sm px-4 me-2">
        <i class="bi bi-printer me-1"></i> Print / Save PDF
    </button>
    <a href="{{ route('crm.invoices.show', $invoice) }}" class="btn btn-outline-secondary btn-sm px-4">
        <i class="bi bi-arrow-left me-1"></i> Back to Invoice
    </a>
</div>

<div class="invoice-wrap">

    {{-- Header --}}
    <div class="inv-header">
        <div>
            <div class="company-name">{{ setting('general.company_name', 'Prowave Technologies') }}</div>
            <div style="opacity:0.7; font-size:0.82rem; margin-top:0.25rem;">{{ setting('general.company_address', '') }}</div>
            @if(setting('general.company_phone'))
                <div style="opacity:0.7; font-size:0.82rem;">{{ setting('general.company_phone') }}</div>
            @endif
        </div>
        <div class="text-end">
            <div class="inv-label">Invoice</div>
            <div class="inv-number">{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    {{-- Meta Row --}}
    <div class="meta-band">
        <div class="meta-item">
            <label>Invoice Date</label>
            <span>{{ $invoice->invoice_date->format('d M, Y') }}</span>
        </div>
        @if($invoice->due_date)
        <div class="meta-item">
            <label>Due Date</label>
            <span>{{ $invoice->due_date->format('d M, Y') }}</span>
        </div>
        @endif
        <div class="meta-item">
            <label>Reference</label>
            <span>{{ $invoice->invoice_number }}</span>
        </div>
    </div>

    {{-- Parties --}}
    <div class="parties">
        <div class="party-box">
            <div class="party-title">From</div>
            <div class="party-name">{{ setting('general.company_name', 'Prowave Technologies') }}</div>
            @if(setting('general.company_email'))
                <div class="party-detail">{{ setting('general.company_email') }}</div>
            @endif
            @if(setting('general.company_phone'))
                <div class="party-detail">{{ setting('general.company_phone') }}</div>
            @endif
        </div>
        <div class="party-box">
            <div class="party-title">Bill To (Client)</div>
            <div class="party-name">{{ $invoice->client->company_name }}</div>
            @if($invoice->client->contact_person)
                <div class="party-detail">{{ $invoice->client->contact_person }}</div>
            @endif
            @if($invoice->client->email)
                <div class="party-detail">{{ $invoice->client->email }}</div>
            @endif
            @if($invoice->client->phone)
                <div class="party-detail">{{ $invoice->client->phone }}</div>
            @endif
            @if($invoice->client->address)
                <div class="party-detail">{{ $invoice->client->address }}</div>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    <div class="items-section">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th>Description</th>
                    <th class="text-center" style="width:70px">Qty</th>
                    <th class="text-end" style="width:110px">Unit Price</th>
                    <th class="text-end" style="width:90px">Discount</th>
                    <th class="text-end" style="width:80px">Tax</th>
                    <th class="text-end" style="width:110px">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $i => $item)
                <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end text-danger">{{ $item->discount > 0 ? '- ' . number_format($item->discount, 2) : '—' }}</td>
                    <td class="text-end">{{ $item->tax > 0 ? number_format($item->tax, 2) : '—' }}</td>
                    <td class="text-end fw-semibold">{{ number_format($item->total, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">No items added.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="totals-section">
        <div class="payment-status text-center">
            <div class="status-badge status-{{ $invoice->status }}">
                {{ str_replace('_', ' ', $invoice->status) }}
            </div>
            @if($invoice->paid_amount > 0)
            <div class="mt-3 text-muted fs-6">
                <strong>Paid:</strong> {{ number_format($invoice->paid_amount, 2) }} PKR
            </div>
            <div class="text-danger fw-bold fs-6 mt-1">
                <strong>Balance Due:</strong> {{ number_format($invoice->balance(), 2) }} PKR
            </div>
            @endif
        </div>
        <div class="totals-box">
            <div class="total-row">
                <span class="text-muted">Subtotal</span>
                <span class="fw-semibold">{{ number_format($invoice->subtotal, 2) }} PKR</span>
            </div>
            @if($invoice->discount > 0)
            <div class="total-row">
                <span class="text-muted">Discount</span>
                <span class="text-danger fw-semibold">- {{ number_format($invoice->discount, 2) }} PKR</span>
            </div>
            @endif
            @if($invoice->tax > 0)
            <div class="total-row">
                <span class="text-muted">Tax</span>
                <span class="fw-semibold">{{ number_format($invoice->tax, 2) }} PKR</span>
            </div>
            @endif
            <div class="total-row">
                <span>Grand Total</span>
                <span>{{ number_format($invoice->total, 2) }} PKR</span>
            </div>
        </div>
    </div>

    {{-- Notes / Terms --}}
    @if($invoice->notes)
    <div class="notes-section">
        <div class="note-box">
            <h6>Notes</h6>
            <p>{{ $invoice->notes }}</p>
        </div>
    </div>
    @endif

    {{-- Payment History --}}
    @if($invoice->payments->count() > 0)
    <div style="padding: 1rem 2.5rem; border-top: 1px solid #e2e8f0;">
        <h6 style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 0.5rem; font-weight: 700;">
            Payment Receipts &amp; Collections
        </h6>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 0.4rem; text-align: left; color: #475569;">Receipt #</th>
                    <th style="padding: 0.4rem; text-align: left; color: #475569;">Date</th>
                    <th style="padding: 0.4rem; text-align: left; color: #475569;">Method</th>
                    <th style="padding: 0.4rem; text-align: left; color: #475569;">Reference</th>
                    <th style="padding: 0.4rem; text-align: right; color: #475569;">Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $p)
                <tr style="border-bottom: 1px dashed #f1f5f9;">
                    <td style="padding: 0.4rem;"><strong>{{ $p->payment_number }}</strong></td>
                    <td style="padding: 0.4rem;">{{ $p->payment_date->format('d M, Y') }}</td>
                    <td style="padding: 0.4rem;">{{ ucwords(str_replace('_', ' ', $p->method)) }}</td>
                    <td style="padding: 0.4rem;">{{ $p->reference_number ?: '—' }}</td>
                    <td style="padding: 0.4rem; text-align: right; color: #16a34a; font-weight: 700;">
                        {{ number_format($p->amount, 2) }} PKR
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="inv-footer">
        Thank you for your business! Please make payment by the due date. &mdash;
        <strong>{{ setting('general.company_name', 'Prowave Technologies') }}</strong>
        &nbsp;|&nbsp; Generated on {{ now('Asia/Karachi')->format('d M Y, h:i A') }}
    </div>
</div>

</body>
</html>
