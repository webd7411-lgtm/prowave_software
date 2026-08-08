<?php

namespace App\Services\Hr;

use App\Enums\TaskStatus;
use App\Models\DailyTask;

class TaskService
{
    public function create(array $data): DailyTask
    {
        $data['assigned_by'] = $data['assigned_by'] ?? auth()->id();
        $data['created_by'] = auth()->id();

        return DailyTask::create($data);
    }

    public function update(DailyTask $task, array $data): DailyTask
    {
        $data['updated_by'] = auth()->id();

        $task->update($data);

        return $task->fresh();
    }

    public function changeStatus(DailyTask $task, string $status): DailyTask
    {
        $task->update([
            'status' => $status,
            'updated_by' => auth()->id(),
        ]);

        return $task->fresh();
    }

    public function delete(DailyTask $task): void
    {
        $task->deleted_by = auth()->id();
        $task->save();
        $task->delete();
    }

    public function applyOverdue(): void
    {
        DailyTask::whereIn('status', [TaskStatus::Pending->value, TaskStatus::InProgress->value, TaskStatus::OnHold->value])
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', now()->toDateString())
            ->update(['status' => TaskStatus::Overdue->value]);
    }
}
