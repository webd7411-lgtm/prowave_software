<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Analytics Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 13px;
            color: #0f172a;
            background-color: #ffffff;
            padding: 1.5rem;
        }

        .report-header {
            text-align: center;
            margin-bottom: 1.5rem;
            border-bottom: 2.5px solid #2563eb;
            padding-bottom: 1rem;
        }

        .report-header h2 {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
            font-size: 1.75rem;
        }

        .report-header p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }

        .report-meta {
            font-size: 0.9rem;
            color: #475569;
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #f1f5f9 !important;
            color: #1e293b;
            font-weight: 700;
            padding: 0.65rem 0.75rem;
            border: 1px solid #cbd5e1;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.03em;
        }

        td {
            padding: 0.65rem 0.75rem;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 0.85rem;
        }

        .total-row td {
            font-weight: 700;
            background-color: #f8fafc;
            font-size: 0.9rem;
            padding: 0.75rem;
        }

        .badge-status {
            display: inline-block;
            padding: 0.25rem 0.65rem;
            border-radius: 50rem;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .bg-planning { background-color: #e0f2fe; color: #0369a1; }
        .bg-in-progress { background-color: #dbeafe; color: #1d4ed8; }
        .bg-on-hold { background-color: #fef3c7; color: #b45309; }
        .bg-completed { background-color: #dcfce7; color: #15803d; }
        .bg-overdue { background-color: #fee2e2; color: #b91c1c; }

        .progress-bar-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 100px;
        }

        .progress-bar-bg {
            flex-grow: 1;
            height: 7px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: #2563eb;
            border-radius: 4px;
        }

        @media print {
            .no-print { display: none !important; }
            body {
                padding: 0 !important;
                font-size: 12px !important;
            }
            th, td {
                padding: 0.5rem 0.6rem !important;
            }
            @page {
                size: landscape;
                margin: 1cm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print mb-4 text-center">
        <button class="btn btn-primary btn-sm px-4 me-2" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print Now</button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm px-4"><i class="bi bi-arrow-left me-1"></i> Go Back</a>
    </div>

    <div class="report-header">
        <h2>Project Analytics & Financial Report</h2>
        <p>Prowave ERP &mdash; Generated {{ now('Asia/Karachi')->format('M d, Y h:i A') }}</p>
    </div>

    <div class="d-flex justify-content-between align-items-center report-meta">
        <div><strong>Total Projects:</strong> {{ $projects->count() }}</div>
        <div><strong>Currency:</strong> {{ setting('general.currency', 'PKR') }}</div>
    </div>

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th style="width: 35px;">#</th>
                <th>Code</th>
                <th>Project Name</th>
                <th>Client</th>
                <th>Manager</th>
                <th>Status</th>
                <th>Progress</th>
                @can('project_finance.view')
                    <th class="text-end">Budget ({{ setting('general.currency', 'PKR') }})</th>
                    <th class="text-end">Expenses ({{ setting('general.currency', 'PKR') }})</th>
                    <th class="text-end">Est. Profit ({{ setting('general.currency', 'PKR') }})</th>
                @endcan
                <th>Deadline</th>
                <th>Health</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalBudget = 0;
                $totalExpenses = 0;
            @endphp
            @forelse($projects as $i => $project)
                @php
                    $expenses = $project->expenses->sum('amount');
                    $totalBudget += $project->budget;
                    $totalExpenses += $expenses;
                    $profit = $project->budget - $expenses;
                @endphp
                <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td class="fw-bold text-secondary">{{ $project->project_code }}</td>
                    <td class="fw-bold text-dark">{{ $project->name }}</td>
                    <td>{{ $project->client?->company_name ?? 'N/A' }}</td>
                    <td>{{ $project->manager?->name ?? 'Unassigned' }}</td>
                    <td>
                        <span class="badge-status bg-{{ Str::slug($project->status) }}">
                            {{ $project->status }}
                        </span>
                    </td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: {{ $project->progress }}%;"></div>
                            </div>
                            <span class="fw-bold fs-xs">{{ $project->progress }}%</span>
                        </div>
                    </td>
                    @can('project_finance.view')
                        <td class="fw-bold text-end">{{ number_format($project->budget, 2) }}</td>
                        <td class="text-danger text-end">{{ number_format($expenses, 2) }}</td>
                        <td class="fw-bold text-success text-end">{{ number_format($profit, 2) }}</td>
                    @endcan
                    <td>{{ $project->deadline ? $project->deadline->format('M d, Y') : 'N/A' }}</td>
                    <td>
                        <span class="badge-status bg-light text-dark border">
                            {{ $project->health }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center text-muted py-4">No project records found.</td>
                </tr>
            @endforelse

            @if($projects->count())
                @can('project_finance.view')
                <tr class="total-row">
                    <td colspan="7" class="text-end">Total Summary:</td>
                    <td class="text-end">{{ number_format($totalBudget, 2) }}</td>
                    <td class="text-danger text-end">{{ number_format($totalExpenses, 2) }}</td>
                    <td class="fw-bold text-success text-end">{{ number_format($totalBudget - $totalExpenses, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
                @endcan
            @endif
        </tbody>
    </table>
</body>
</html>
