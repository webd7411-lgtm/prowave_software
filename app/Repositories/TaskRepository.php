<?php

namespace App\Repositories;

use App\Enums\TaskStatus;
use App\Models\DailyTask;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function query(array $filters = []): Builder
    {
        $query = DailyTask::query()
            ->with(['assignee:id,name,employee_id,avatar', 'assigner:id,name', 'department:id,name'])
            ->latest();

        $this->applyFilters($query, $filters);

        return $query;
    }

    public function paginate(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)->paginate($perPage)->withQueryString();
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        $me = auth()->user();

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        } elseif (! empty($filters['my']) && $me) {
            $query->where('assigned_to', $me->id);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }
    }

    public function markOverdue(): void
    {
        DailyTask::whereIn('status', [TaskStatus::Pending->value, TaskStatus::InProgress->value, TaskStatus::OnHold->value])
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', now()->toDateString())
            ->update(['status' => TaskStatus::Overdue->value]);
    }
}
