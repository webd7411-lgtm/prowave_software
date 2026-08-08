<?php

namespace App\Repositories;

use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveRepository
{
    public function query(array $filters = []): Builder
    {
        $query = LeaveRequest::query()->with(['employee:id,name,employee_id,avatar,department_id', 'leaveType:id,name,code', 'reviewer:id,name']);

        $this->applyFilters($query, $filters);

        return $query->latest();
    }

    public function paginate(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)->paginate($perPage)->withQueryString();
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $filters['department_id']));
        }

        if (! empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('start_date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('end_date', '<=', $filters['to']);
        }
    }
}
