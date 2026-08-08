<table>
    <thead>
        <tr>
            <th>Employee ID</th>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Designation</th>
            <th>Department</th>
            <th>Joining Date</th>
            <th>Created At</th>
            <th>Status</th>
            <th>Roles</th>
            <th>Last Login</th>
            <th>Last Login IP</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td>{{ $user->employee_id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone }}</td>
                <td>{{ $user->designation }}</td>
                <td>{{ $user->department?->name ?? $user->department_label }}</td>
                <td>{{ $user->joining_date?->format('d-m-Y') }}</td>
                <td>{{ $user->created_at?->format('d-m-Y') }}</td>
                <td>{{ $user->status->label() }}</td>
                <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
                <td>{{ $user->last_login_at?->format('d-m-Y H:i') }}</td>
                <td>{{ $user->last_login_ip }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
