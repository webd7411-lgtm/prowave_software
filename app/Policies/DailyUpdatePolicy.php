<?php

namespace App\Policies;

use App\Models\DailyWorkUpdate;
use App\Models\User;

class DailyUpdatePolicy
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
        return $user->can('daily_reports.view') || $user->can('daily_reports.create');
    }

    public function view(User $user, DailyWorkUpdate $update): bool
    {
        return $user->can('daily_reports.view') || $user->id === $update->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->can('daily_reports.create');
    }

    public function review(User $user): bool
    {
        return $user->can('daily_reports.review');
    }
}
