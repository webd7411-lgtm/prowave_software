<?php

namespace App\Http\Controllers\Hr;

use App\Enums\DailyUpdateStatus;
use App\Enums\EmployeeStatus;
use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DailyTask;
use App\Models\DailyWorkUpdate;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\Hr\AttendanceService;
use Illuminate\View\View;

class HrDashboardController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService) {}

    public function dashboard(): View
    {
        $today = now()->toDateString();

        $totalEmployees = User::whereNotNull('employee_id')->count();
        $activeEmployees = User::where('employee_status', EmployeeStatus::Active->value)->count();
        $onLeave = User::where('employee_status', EmployeeStatus::OnLeave->value)->count();

        $attendance = $this->attendanceService->dailySummary($today);

        $pendingLeaves = LeaveRequest::pending()->count();
        $pendingUpdates = DailyWorkUpdate::where('status', DailyUpdateStatus::Submitted->value)->count();
        $pendingTasks = DailyTask::whereIn('status', ['pending', 'in_progress', 'on_hold', 'overdue'])->count();

        $newEmployees = User::whereNotNull('employee_id')->where('created_at', '>=', now()->startOfMonth())->count();

        // Department overview
        $departments = Department::query()->withCount('users')->active()->orderBy('name')->get();

        // Attendance chart (last 7 days)
        $attendanceChart = $this->attendanceService->monthlyChart(now()->year, now()->month);

        // Leave chart by status
        $leaveChart = [
            'labels' => ['Pending', 'Approved', 'Rejected'],
            'data' => [
                LeaveRequest::pending()->count(),
                LeaveRequest::where('status', LeaveStatus::Approved->value)->count(),
                LeaveRequest::where('status', LeaveStatus::Rejected->value)->count(),
            ],
        ];

        // Recent HR activity via audit
        $recentActivities = LoginLog::query()->with('user')->whereNotNull('user_id')->latest('login_at')->limit(8)->get();

        return view('hr.dashboard', compact(
            'totalEmployees',
            'activeEmployees',
            'onLeave',
            'attendance',
            'pendingLeaves',
            'pendingUpdates',
            'pendingTasks',
            'newEmployees',
            'departments',
            'attendanceChart',
            'leaveChart',
            'recentActivities'
        ));
    }
}
