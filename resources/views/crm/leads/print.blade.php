<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leads Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; font-size: 13px; }
        .report-header { text-align: center; margin-bottom: 1.5rem; border-bottom: 2px solid #2563eb; padding-bottom: 1rem; }
        .report-header h2 { font-weight: 700; color: #1e293b; margin-bottom: 0.25rem; }
        .report-header p { color: #64748b; font-size: 0.85rem; margin: 0; }
        .report-meta { font-size: 0.8rem; color: #64748b; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9 !important; color: #334155; font-weight: 600; padding: 0.5rem 0.6rem; border: 1px solid #e2e8f0; text-align: left; }
        td { padding: 0.45rem 0.6rem; border: 1px solid #e2e8f0; vertical-align: top; }
        .badge-soft { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 500; }
        .total-row td { font-weight: 700; background: #f8fafc; }
        @media print {
            body { font-size: 11px; }
            .no-print { display: none !important; }
            th, td { padding: 0.3rem 0.4rem; }
            @page { margin: 1cm; size: landscape; }
        }
    </style>
</head>
<body>
    <div class="no-print mb-3 mt-3 text-center">
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print Now</button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Go Back</a>
    </div>

    <div class="report-header">
        <h2>Sales Leads Report</h2>
        <p>Prowave ERP &mdash; Generated {{ now('Asia/Karachi')->format('M d, Y h:i A') }}</p>
    </div>

    @if(!empty(array_filter($filters)))
    <div class="report-meta">
        <strong>Filters:</strong>
        @if(!empty($filters['search'])) Search: "{{ $filters['search'] }}" &middot; @endif
        @if(!empty($filters['status'])) Status: {{ $filters['status'] }} &middot; @endif
        @if(!empty($filters['source'])) Source: {{ ucfirst($filters['source']) }} &middot; @endif
        @if(!empty($filters['priority'])) Priority: {{ ucfirst($filters['priority']) }} &middot; @endif
        @if(!empty($filters['owner_id'])) Owner: {{ \App\Models\User::find($filters['owner_id'])?->name ?? 'Unknown' }} &middot; @endif
    </div>
    @endif

    <div class="report-meta"><strong>Total Leads:</strong> {{ $leads->count() }}</div>

    <table class="table table-sm">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Company</th>
                <th>Contact</th>
                <th>Phone</th>
                <th>Source</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Budget</th>
                <th>Owner</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $i => $lead)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $lead->company_name }}</strong>
                    <div class="text-muted" style="font-size:0.75rem">{{ $lead->lead_number }}</div>
                </td>
                <td>{{ $lead->contact_person ?: '—' }}</td>
                <td>{{ $lead->phone ?: '—' }}</td>
                <td class="text-capitalize">{{ $lead->source }}</td>
                <td class="text-capitalize">{{ str_replace('_', ' ', $lead->status) }}</td>
                <td class="text-capitalize">{{ $lead->priority }}</td>
                <td>{{ $lead->estimated_budget ? number_format($lead->estimated_budget, 2) : '—' }}</td>
                <td>{{ $lead->owner?->name ?? 'Unassigned' }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center text-muted py-3">No leads found.</td></tr>
            @endforelse
            @if($leads->count())
            <tr class="total-row">
                <td colspan="7" class="text-end">Total Budget:</td>
                <td>{{ number_format($leads->sum('estimated_budget'), 2) }}</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
