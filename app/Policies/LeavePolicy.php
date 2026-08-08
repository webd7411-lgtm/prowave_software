<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeavePolicy
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
        return $user->can('leave.view');
    }

    public function view(User $user, LeaveRequest $leave): bool
    {
        return $user->can('leave.view') || $user->id === $leave->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->can('leave.create');
    }

    public function approve(User $user): bool
    {
        return $user->can('leave.approve');
    }

    public function reject(User $user): bool
    {
        return $user->can('leave.reject');
    }
}
