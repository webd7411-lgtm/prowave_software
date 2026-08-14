<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\DailyTask;
use App\Models\DailyWorkUpdate;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\DailyUpdatePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\LeavePolicy;
use App\Policies\RolePolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Department::class => DepartmentPolicy::class,
        Attendance::class => AttendancePolicy::class,
        LeaveRequest::class => LeavePolicy::class,
        DailyTask::class => TaskPolicy::class,
        DailyWorkUpdate::class => DailyUpdatePolicy::class,
    ];

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
