<?php

namespace App\Policies;

use App\Models\DailyTask;
use App\Models\User;

class TaskPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view') || $user->can('tasks.assign');
    }

    public function view(User $user, DailyTask $task): bool
    {
        return $user->can('tasks.view') || $user->id === $task->assigned_to || $user->id === $task->assigned_by;
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function assign(User $user): bool
    {
        return $user->can('tasks.assign');
    }

    public function update(User $user, DailyTask $task): bool
    {
        return $user->can('tasks.edit') || $user->id === $task->assigned_to;
    }

    public function delete(User $user, DailyTask $task): bool
    {
        return $user->can('tasks.delete');
    }
}
