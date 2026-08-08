<?php

namespace App\Policies;

use App\Models\User;

class EmployeePolicy
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
        return $user->can('staff.view') || $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('staff.view') || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->can('staff.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('staff.edit') || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->id === $user->id) {
            return false;
        }

        return $user->can('staff.delete');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('staff.delete');
    }
}
