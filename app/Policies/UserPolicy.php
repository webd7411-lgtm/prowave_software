<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view') || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.edit') || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->isSuperAdmin()) {
            return false;
        }

        if ($model->id === $user->id) {
            return false;
        }

        return $user->can('users.delete');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('users.restore');
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($model->isSuperAdmin()) {
            return false;
        }

        return $user->can('users.force-delete');
    }

    public function activate(User $user, User $model): bool
    {
        if ($model->isSuperAdmin()) {
            return false;
        }

        return $user->can('users.activate');
    }

    public function suspend(User $user, User $model): bool
    {
        if ($model->isSuperAdmin()) {
            return false;
        }

        return $user->can('users.suspend');
    }

    public function import(User $user): bool
    {
        return $user->can('users.import');
    }

    public function export(User $user): bool
    {
        return $user->can('users.export');
    }

    public function resetPassword(User $user, User $model): bool
    {
        return $user->can('users.reset-password') && $model->id !== $user->id;
    }
}
