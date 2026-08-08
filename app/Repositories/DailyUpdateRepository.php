<?php

namespace App\Repositories;

use App\Models\DailyWorkUpdate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class DailyUpdateRepository
{
    public function query(array $filters = []): Builder
    {
        $query = DailyWorkUpdate::query()->with(['employee:id,name,employee_id,avatar,department_id', 'reviewer:id,name']);

        $this->applyFilters($query, $filters);

        return $query->latest('date');
    }

    public function paginate(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)->paginate($perPage)->withQueryString();
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        $me = auth()->user();

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        } elseif (! empty($filters['my']) && $me) {
            $query->where('employee_id', $me->id);
        }

        if (! empty($filters['department_id'])) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $filters['department_id']));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('date', '<=', $filters['to']);
        }
    }
}
