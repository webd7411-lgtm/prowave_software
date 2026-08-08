<table>
    <thead>
        <tr>
            <th>Employee ID</th>
            <th>Name</th>
            <th>Department</th>
            <th>Designation</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Employment Type</th>
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
                <td>{{ $employee->phone }}</td>
                <td>{{ $employee->email }}</td>
                <td>{{ $employee->employment_type ? \Illuminate\Support\Str::title(str_replace('_', ' ', $employee->employment_type)) : '—' }}</td>
                <td>{{ $employee->joining_date?->format('d-m-Y') }}</td>
                <td>{{ $employee->employee_status ? \Illuminate\Support\Str::title(str_replace('_', ' ', $employee->employee_status)) : '—' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
