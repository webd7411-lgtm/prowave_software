<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Statement — {{ $client->company_name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #0f172a !important;
            background-color: #ffffff !important;
            font-size: 13px;
        }
        .statement-card {
            background: #ffffff !important;
            border-radius: 8px;
            max-width: 900px;
            margin: 1.5rem auto;
            padding: 2rem;
            border: 1px solid #e2e8f0;
        }
        .statement-header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .company-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
        }
        .statement-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2563eb;
            text-transform: uppercase;
        }
        .summary-box {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.85rem;
            text-align: center;
        }
        .summary-box .label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .summary-box .val {
            font-size: 1.25rem;
            font-weight: 800;
            margin-top: 0.2rem;
        }
        table.statement-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        table.statement-table th {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            font-weight: 700;
            padding: 0.6rem 0.75rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            border: 1px solid #cbd5e1;
        }
        table.statement-table td {
            padding: 0.6rem 0.75rem;
            border: 1px solid #e2e8f0;
            color: #0f172a !important;
            vertical-align: middle;
        }
        table.statement-table tr:nth-child(even) {
            background-color: #f8fafc !important;
        }
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 11px;
                padding: 0 !important;
                margin: 0 !important;
            }
            .statement-card {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                max-width: 100% !important;
                padding: 1.5rem !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 1.5cm 1cm;
                size: portrait;
            }
        }
    </style>
</head>
<body>
    <div class="no-print container max-width-900 mt-3 text-center">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
            <i class="bi bi-printer me-1"></i> Print Statement Now
        </button>
        <a href="{{ route('crm.clients.show', $client) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Client
        </a>
    </div>

    <div class="statement-card">
        <!-- Header -->
        <div class="statement-header row align-items-center">
            <div class="col-7">
                <div class="company-title">{{ setting('general.company_name', 'Prowave Technologies') }}</div>
                <div class="text-muted fs-xs mt-1">
                    Client Account Ledger Statement &middot; Generated {{ now('Asia/Karachi')->format('M d, Y h:i A') }}
                </div>
            </div>
            <div class="col-5 text-end">
                <div class="statement-title">ACCOUNT STATEMENT</div>
                <div class="text-dark fw-bold fs-xs mt-1">Client: {{ $client->company_name }}</div>
                <div class="text-muted fs-xs">Client #: {{ $client->client_number }} &middot; Phone: {{ $client->phone ?: '—' }}</div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="summary-box">
                    <div class="label">Total Debits</div>
                    <div class="val text-dark">{{ number_format($client->ledger->sum('debit'), 2) }} PKR</div>
                </div>
            </div>
            <div class="col-4">
                <div class="summary-box">
                    <div class="label">Total Paid (Credits)</div>
                    <div class="val text-success">{{ number_format($client->ledger->sum('credit'), 2) }} PKR</div>
                </div>
            </div>
            <div class="col-4">
                <div class="summary-box">
                    <div class="label">Net Outstanding</div>
                    <div class="val {{ $client->outstanding() > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($client->outstanding(), 2) }} PKR</div>
                </div>
            </div>
        </div>

        <!-- Ledger Table List -->
        <table class="statement-table">
            <thead>
                <tr>
                    <th style="width: 35px;">#</th>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="text-end">Debit (+)</th>
                    <th class="text-end">Credit (-)</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $index => $entry)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $entry->transaction_date->format('M d, Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $entry->reference }}</span></td>
                        <td>{{ $entry->description }}</td>
                        <td class="text-end fw-semibold text-danger">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '—' }}</td>
                        <td class="text-end fw-semibold text-success">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '—' }}</td>
                        <td class="text-end fw-bold {{ $entry->balance > 0 ? 'text-dark' : 'text-success' }}">{{ number_format($entry->balance, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No ledger transactions found for this client.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: 700;">
                    <td colspan="6" class="text-end text-uppercase">Current Outstanding Balance:</td>
                    <td class="text-end text-primary fs-6">{{ number_format($client->outstanding(), 2) }} PKR</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
