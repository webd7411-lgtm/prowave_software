<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Slip - {{ $project->project_code }} - {{ $project->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #0f172a !important;
            background-color: #f1f5f9 !important;
            font-size: 12px;
        }
        .bill-card {
            background: #ffffff !important;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            max-width: 480px;
            margin: 1.5rem auto;
            padding: 1.25rem;
            border: 1px solid #cbd5e1;
        }
        .bill-header {
            text-align: center;
            border-bottom: 2px dashed #cbd5e1;
            padding-bottom: 0.85rem;
            margin-bottom: 0.85rem;
        }
        .company-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
        }
        .slip-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 0.2rem;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 0.85rem;
            font-size: 0.82rem;
        }
        .meta-table td {
            padding: 0.2rem 0;
            vertical-align: top;
        }
        .meta-label {
            font-weight: 600;
            color: #64748b;
            width: 38%;
        }
        .meta-val {
            font-weight: 700;
            color: #0f172a;
        }
        .fin-box {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.6rem 0.75rem;
            margin-bottom: 0.85rem;
        }
        .fin-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            padding: 0.25rem 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .fin-row:last-child {
            border-bottom: none;
            padding-top: 0.4rem;
        }
        table.pay-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            margin-bottom: 0.85rem;
        }
        table.pay-table th {
            background: #f1f5f9 !important;
            color: #334155 !important;
            font-weight: 700;
            padding: 0.35rem 0.5rem;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        table.pay-table td {
            padding: 0.35rem 0.5rem;
            border-bottom: 1px solid #f1f5f9;
            color: #0f172a !important;
        }
        .signature-section {
            margin-top: 1.5rem;
            padding-top: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .sig-line {
            width: 42%;
            border-top: 1px dashed #94a3b8;
            text-align: center;
            padding-top: 0.25rem;
            font-weight: 600;
            color: #64748b;
            font-size: 0.75rem;
        }
        .thank-you {
            text-align: center;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.85rem;
            border-top: 1px dashed #cbd5e1;
            padding-top: 0.5rem;
        }
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 11px;
                margin: 0;
                padding: 0;
            }
            .bill-card {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 0.5rem !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 5mm;
                size: auto;
            }
        }
    </style>
</head>
<body>
    <div class="no-print container mt-2 mb-2 text-center" style="max-width: 480px;">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
            <i class="bi bi-printer me-1"></i> Print Bill Slip
        </button>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Project
        </a>
    </div>

    <div class="bill-card">
        <!-- Header -->
        <div class="bill-header">
            <div class="company-title">{{ setting('general.company_name', 'Prowave Technologies') }}</div>
            <div class="slip-title">PROJECT BILL SLIP</div>
            <div class="text-muted fs-xs mt-1">
                Ref: <strong>SLIP-{{ $project->project_code }}</strong> &middot; Date: <strong>{{ now('Asia/Karachi')->format('d M Y') }}</strong>
            </div>
        </div>

        <!-- Meta Table -->
        <table class="meta-table">
            <tr>
                <td class="meta-label">Client Name:</td>
                <td class="meta-val">{{ $project->client?->company_name ?? 'Direct / Unassigned' }}</td>
            </tr>
            @if($project->client?->phone)
            <tr>
                <td class="meta-label">Phone:</td>
                <td class="meta-val">{{ $project->client->phone }}</td>
            </tr>
            @endif
            <tr>
                <td class="meta-label">Project Code:</td>
                <td class="meta-val">{{ $project->project_code }}</td>
            </tr>
            <tr>
                <td class="meta-label">Project Name:</td>
                <td class="meta-val">{{ $project->name }}</td>
            </tr>
            <tr>
                <td class="meta-label">Status:</td>
                <td class="meta-val text-uppercase">{{ $project->status }}</td>
            </tr>
        </table>

        <!-- Financial Overview Box -->
        <div class="fin-box">
            <div class="fin-row">
                <span class="fw-semibold text-muted">Total Project Budget:</span>
                <span class="fw-bold text-dark">{{ number_format($project->budget, 2) }} {{ $project->currency ?: 'PKR' }}</span>
            </div>
            <div class="fin-row">
                <span class="fw-semibold text-muted">Total Amount Paid:</span>
                <span class="fw-bold text-success">{{ number_format($project->paid_amount, 2) }} {{ $project->currency ?: 'PKR' }}</span>
            </div>
            <div class="fin-row fw-bold">
                <span class="text-danger">Remaining Balance Due:</span>
                <span class="text-danger fs-6">{{ number_format($project->remaining_balance, 2) }} {{ $project->currency ?: 'PKR' }}</span>
            </div>
        </div>

        <!-- Payment History -->
        <div class="fw-bold text-uppercase fs-xs mb-1 text-muted">Payment History & Receipts</div>
        <table class="pay-table">
            <thead>
                <tr>
                    <th style="width: 25px;">#</th>
                    <th>Date</th>
                    <th>Ref / Receipt</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->payments as $index => $payment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '—' }}</td>
                        <td>
                            <div class="fw-semibold">{{ $payment->payment_number }}</div>
                            <div class="text-muted fs-xs">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</div>
                        </td>
                        <td class="text-end fw-bold text-success">{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-2">No payments recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="sig-line">Client Signature</div>
            <div class="sig-line">Authorized Sign & Stamp</div>
        </div>

        <div class="thank-you">
            Thank you for doing business with us!
        </div>
    </div>
</body>
</html>
