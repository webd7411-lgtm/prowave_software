<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        h2 { margin: 0 0 4px; color: #1e293b; }
        .muted { color: #64748b; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #2563eb; color: #fff; text-align: left; padding: 8px; font-size: 10px; }
        td { border-bottom: 1px solid #e5e9f0; padding: 7px 8px; }
        .summary { margin-bottom: 16px; }
        .summary span { margin-right: 18px; }
    </style>
</head>
<body>
    <h2>Employee List</h2>
    <div class="muted">Generated on {{ now()->format('d M, Y') }}</div>
    <div class="summary">
        <span>Total: <strong>{{ $summary['total'] }}</strong></span>
        <span>Active: <strong>{{ $summary['active'] }}</strong></span>
        <span>On Leave: <strong>{{ $summary['on_leave'] }}</strong></span>
        <span>Terminated: <strong>{{ $summary['terminated'] }}</strong></span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Email</th>
                <th>Joining Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->department?->name ?? '—' }}</td>
                    <td>{{ $employee->designation ?? '—' }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>{{ $employee->joining_date?->format('d-m-Y') }}</td>
                    <td>{{ $employee->employee_status ? \Illuminate\Support\Str::title(str_replace('_', ' ', $employee->employee_status)) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
