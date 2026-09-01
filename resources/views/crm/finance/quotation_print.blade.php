<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->quotation_number }} - {{ $quotation->client->company_name }}</title>
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
        .quotation-wrap {
            max-width: 780px;
            margin: 1.5rem auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        /* Header Band */
        .qtn-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
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
        .qtn-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0.75;
            margin-bottom: 0.2rem;
        }
        .qtn-number {
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
            justify-content: flex-end;
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
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-draft    { background: #f1f5f9; color: #64748b; }
        .status-sent     { background: #dbeafe; color: #1d4ed8; }
        .status-accepted { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-expired  { background: #fef3c7; color: #92400e; }
        /* Footer */
        .qtn-footer {
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
            .quotation-wrap {
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
    <a href="{{ route('crm.quotations.show', $quotation) }}" class="btn btn-outline-secondary btn-sm px-4">
        <i class="bi bi-arrow-left me-1"></i> Back to Quotation
    </a>
</div>

<div class="quotation-wrap">

    {{-- Header --}}
    <div class="qtn-header">
        <div>
            <div class="company-name">{{ setting('general.company_name', 'Prowave Technologies') }}</div>
            <div style="opacity:0.7; font-size:0.82rem; margin-top:0.25rem;">{{ setting('general.company_address', '') }}</div>
            @if(setting('general.company_phone'))
                <div style="opacity:0.7; font-size:0.82rem;">{{ setting('general.company_phone') }}</div>
            @endif
        </div>
        <div class="text-end">
            <div class="qtn-label">Quotation</div>
            <div class="qtn-number">{{ $quotation->quotation_number }}</div>
            <div class="mt-2">
                @php
                    $sc = ['draft'=>'status-draft','sent'=>'status-sent','accepted'=>'status-accepted','rejected'=>'status-rejected','expired'=>'status-expired'];
                @endphp
                <span class="status-badge {{ $sc[$quotation->status] ?? 'status-draft' }}">
                    {{ ucfirst($quotation->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Meta Row --}}
    <div class="meta-band">
        <div class="meta-item">
            <label>Issue Date</label>
            <span>{{ $quotation->quotation_date->format('d M, Y') }}</span>
        </div>
        @if($quotation->valid_until)
        <div class="meta-item">
            <label>Valid Until</label>
            <span>{{ $quotation->valid_until->format('d M, Y') }}</span>
        </div>
        @endif
        <div class="meta-item">
            <label>Reference</label>
            <span>{{ $quotation->quotation_number }}</span>
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
            <div class="party-name">{{ $quotation->client->company_name }}</div>
            @if($quotation->client->contact_person)
                <div class="party-detail">{{ $quotation->client->contact_person }}</div>
            @endif
            @if($quotation->client->email)
                <div class="party-detail">{{ $quotation->client->email }}</div>
            @endif
            @if($quotation->client->phone)
                <div class="party-detail">{{ $quotation->client->phone }}</div>
            @endif
            @if($quotation->client->address)
                <div class="party-detail">{{ $quotation->client->address }}</div>
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
                @forelse($quotation->items as $i => $item)
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
        <div class="totals-box">
            <div class="total-row">
                <span class="text-muted">Subtotal</span>
                <span class="fw-semibold">{{ number_format($quotation->subtotal, 2) }} PKR</span>
            </div>
            @if($quotation->discount > 0)
            <div class="total-row">
                <span class="text-muted">Discount</span>
                <span class="text-danger fw-semibold">- {{ number_format($quotation->discount, 2) }} PKR</span>
            </div>
            @endif
            @if($quotation->tax > 0)
            <div class="total-row">
                <span class="text-muted">Tax</span>
                <span class="fw-semibold">{{ number_format($quotation->tax, 2) }} PKR</span>
            </div>
            @endif
            <div class="total-row">
                <span>Grand Total</span>
                <span>{{ number_format($quotation->total, 2) }} PKR</span>
            </div>
        </div>
    </div>

    {{-- Notes / Terms --}}
    @if($quotation->notes || $quotation->terms)
    <div class="notes-section">
        @if($quotation->notes)
        <div class="note-box">
            <h6>Notes</h6>
            <p>{{ $quotation->notes }}</p>
        </div>
        @endif
        @if($quotation->terms)
        <div class="note-box">
            <h6>Terms &amp; Conditions</h6>
            <p>{{ $quotation->terms }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Footer --}}
    <div class="qtn-footer">
        Thank you for your consideration. Please contact us for any queries. &mdash;
        <strong>{{ setting('general.company_name', 'Prowave Technologies') }}</strong>
        &nbsp;|&nbsp; Generated on {{ now('Asia/Karachi')->format('d M Y, h:i A') }}
    </div>
</div>

</body>
</html>
