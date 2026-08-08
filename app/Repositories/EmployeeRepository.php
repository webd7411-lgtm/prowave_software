<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeRepository
{
    public function query(array $filters = []): Builder
    {
        $query = User::query()
            ->with(['department', 'shift', 'reportingManager', 'designation'])
            ->whereNotNull('employee_id')
            ->latest();

        $this->applyFilters($query, $filters);

        return $query;
    }

    public function paginate(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)->paginate($perPage)->withQueryString();
    }

    public function find(int $id, bool $withTrashed = false): ?User
    {
        $query = User::query()->with([
            'department', 'shift', 'reportingManager', 'subordinates',
            'attendanceRecords', 'leaveRequests', 'assignedTasks', 'documents',
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->find($id);
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('employee_id', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%')
                    ->orWhere('phone', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['status'])) {
            $query->where('employee_status', $filters['status']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['designation'])) {
            $query->where('designation', $filters['designation']);
        }

        if (! empty($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }

        if (! empty($filters['joining_from'])) {
            $query->whereDate('joining_date', '>=', $filters['joining_from']);
        }

        if (! empty($filters['joining_to'])) {
            $query->whereDate('joining_date', '<=', $filters['joining_to']);
        }

        if (! empty($filters['trashed'])) {
            $query->onlyTrashed();
        }
    }

    public function employeesOptions(): array
    {
        return User::query()
            ->whereNotNull('employee_id')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
