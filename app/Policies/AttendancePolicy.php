<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
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
        return $user->can('attendance.view');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->can('attendance.view') || $user->id === $attendance->employee_id;
    }

    public function manage(User $user): bool
    {
        return $user->can('attendance.manage');
    }

    public function checkInOut(User $user): bool
    {
        return $user->can('attendance.manage') || $user->can('attendance.create');
    }
}
