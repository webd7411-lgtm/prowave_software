<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Projects Report</title>
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
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
        <a href="{{ route('crm.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    <div class="report-header">
        <h2>Projects Report</h2>
        <p>Prowave ERP — Generated {{ now('Asia/Karachi')->format('M d, Y h:i A') }}</p>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
            <strong>Total Projects:</strong> {{ $totalProjects }}
        </div>
        <div class="col-12 col-md-6 text-end">
            <strong>Status:</strong> 
            @if($completedProjects > 0)Completed, @endif 
            @if($activeProjects > 0)Running, @endif 
            @if($overdueProjects > 0)Overdue @endif
        </div>
    </div>

    <table class="table table-sm">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Project Code</th>
                <th>Project Name</th>
                <th class="text-end">Client</th>
                <th class="text-end">Progress</th>
                <th class="text-end">Status</th>
                <th class="text-end">Deadline</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $project)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $project->project_code }}</td>
                <td>
                    <a href="{{ route('projects.show', $project) }}" class="text-dark text-decoration-none">
                        {{ $project->name }}
                    </a>
                </td>
                <td class="text-end">{{ $project->client?->company_name ?? 'Internal' }}</td>
                <td class="text-end">
                    <div class="progress progress-sm mb-0" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: {{ $project->progress }}%;" aria-valuenow="{{ $project->progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    {{ $project->progress }}%
                </td>
                <td>
                    <span class="badge {{ $project->status_badge_class }} fs-xs px-2 py-1">
                        {{ ucfirst($project->status) }}
                    </span>
                </td>
                <td class="text-end">
                    <div class="text-muted small">{{ $project->deadline ? $project->deadline->format('M d, Y') : 'No deadline' }}</div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-3">No projects found.</td></tr>
            @endforelse
            @if($projects->count())
            <tr class="total-row">
                <td colspan="6" class="text-end fw-bold">Total Projects:</td>
                <td class="text-end fw-bold">{{ $projects->count() }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>