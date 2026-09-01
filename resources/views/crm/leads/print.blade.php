<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Leads Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, Arial, sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #fff;
        }
        .report-header {
            text-align: center;
            margin-bottom: 1.25rem;
            border-bottom: 2.5px solid #3730a3;
            padding-bottom: 0.9rem;
        }
        .report-header h2 {
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.25rem;
            font-size: 1.4rem;
        }
        .report-header p {
            color: #64748b;
            font-size: 0.82rem;
            margin: 0;
        }
        .report-meta {
            font-size: 0.8rem;
            color: #475569;
            margin-bottom: 0.65rem;
        }
        .report-meta strong { color: #1e293b; }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        colgroup col.col-no      { width: 4%; }
        colgroup col.col-company { width: 22%; }
        colgroup col.col-contact { width: 14%; }
        colgroup col.col-phone   { width: 14%; }
        colgroup col.col-address { width: 18%; }
        colgroup col.col-owner   { width: 13%; }
        colgroup col.col-status  { width: 15%; }

        thead th {
            background: #312e81 !important;
            color: #fff !important;
            font-weight: 700;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.55rem 0.6rem;
            border: 1px solid #4338ca;
            text-align: left;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        tbody td {
            padding: 0.45rem 0.6rem;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            word-break: break-word;
            font-size: 0.82rem;
        }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        tbody tr:last-child td { border-bottom: 2px solid #cbd5e1; }

        .lead-number { font-size: 0.7rem; color: #64748b; margin-top: 2px; }
        .lead-company { font-weight: 700; color: #1e293b; }
        .schedule-info { font-size: 0.7rem; color: #4f46e5; margin-top: 3px; }
        .status-text { font-weight: 600; color: #1e293b; }

        .total-bar {
            margin-top: 0.75rem;
            font-size: 0.82rem;
            color: #475569;
        }
        .total-bar strong { color: #1e293b; }

        @media print {
            body { font-size: 10.5px; }
            .no-print { display: none !important; }
            thead th { padding: 0.35rem 0.45rem; font-size: 0.65rem; }
            tbody td { padding: 0.3rem 0.45rem; font-size: 0.75rem; }
            @page { margin: 1cm 0.8cm; size: landscape; }
        }
    </style>
</head>
<body>
    <!-- Action Buttons (hidden on print) -->
    <div class="no-print d-flex gap-2 justify-content-center my-3">
        <button class="btn btn-primary btn-sm px-4" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Print Now
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i>Go Back
        </a>
    </div>

    <!-- Report Header -->
    <div class="report-header">
        <h2><i class="bi bi-people-fill me-2"></i>Sales Leads Report</h2>
        <p>{{ setting('general.company_name', 'Prowave Technologies') }} &mdash; Generated {{ now('Asia/Karachi')->format('M d, Y h:i A') }}</p>
    </div>

    <!-- Active Filters -->
    @if(!empty(array_filter($filters)))
    <div class="report-meta">
        <strong><i class="bi bi-funnel me-1"></i>Filters Applied:</strong>
        @if(!empty($filters['search'])) Search: &ldquo;{{ $filters['search'] }}&rdquo; &middot; @endif
        @if(!empty($filters['status'])) Status: {{ ucwords(str_replace('_', ' ', $filters['status'])) }} &middot; @endif
        @if(!empty($filters['from_date'])) From: {{ \Carbon\Carbon::parse($filters['from_date'])->format('M d, Y') }} &middot; @endif
        @if(!empty($filters['to_date'])) To: {{ \Carbon\Carbon::parse($filters['to_date'])->format('M d, Y') }} &middot; @endif
        @if(!empty($filters['owner_id'])) Owner: {{ \App\Models\User::find($filters['owner_id'])?->name ?? 'Unknown' }} @endif
    </div>
    @endif

    <div class="report-meta total-bar">
        <strong>Total Leads:</strong> {{ $leads->count() }}
    </div>

    <!-- Leads Table -->
    <table>
        <colgroup>
            <col class="col-no">
            <col class="col-company">
            <col class="col-contact">
            <col class="col-phone">
            <col class="col-address">
            <col class="col-owner">
            <col class="col-status">
        </colgroup>
        <thead>
            <tr>
                <th>#</th>
                <th>Company / Business</th>
                <th>Contact Name</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Owner</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $i => $lead)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <div class="lead-company">{{ $lead->company_name ?: '—' }}</div>
                    <div class="lead-number">{{ $lead->lead_number }}</div>
                </td>
                <td>{{ $lead->contact_person ?: '—' }}</td>
                <td>{{ $lead->phone ?: '—' }}</td>
                <td>{{ $lead->address ? Str::limit($lead->address, 38) : '—' }}</td>
                <td>{{ $lead->owner?->name ?? 'Unassigned' }}</td>
                <td>
                    <span class="status-text">{{ ucwords(str_replace('_', ' ', $lead->status)) }}</span>
                    @if(in_array($lead->status, ['meeting_scheduled', 'call_again']) && $lead->relationLoaded('followups'))
                        @php
                            $fu = $lead->followups->firstWhere('status', 'scheduled') ?? $lead->followups->first();
                        @endphp
                        @if($fu && $fu->follow_up_date)
                            <div class="schedule-info">
                                <i class="bi bi-clock"></i>
                                {{ \Carbon\Carbon::parse($fu->follow_up_date)->format('M d, Y') }}
                                @if($fu->follow_up_time)
                                    {{ \Carbon\Carbon::parse($fu->follow_up_time)->format('h:i A') }}
                                @endif
                            </div>
                        @endif
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="bi bi-inbox me-1"></i>No leads found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="report-meta total-bar mt-2 text-end no-print">
        <strong>{{ $leads->count() }}</strong> lead(s) listed
    </div>
</body>
</html>
