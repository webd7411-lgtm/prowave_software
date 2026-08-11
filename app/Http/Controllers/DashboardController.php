<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $now = now();

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', UserStatus::Active->value)->count(),
            'inactive_users' => User::where('status', UserStatus::Inactive->value)->count(),
            'suspended_users' => User::where('status', UserStatus::Suspended->value)->count(),
            'new_this_month' => User::where('created_at', '>=', $now->copy()->startOfMonth())->count(),
            'total_departments' => Department::count(),
            'total_logins' => LoginLog::where('status', 'success')->count(),
            'logins_today' => LoginLog::where('status', 'success')->whereDate('login_at', $now->toDateString())->count(),
        ];

        // Delta vs. last month (for KPI trend indicators)
        $lastMonth = User::where('created_at', '>=', $now->copy()->subMonth()->startOfMonth())
            ->where('created_at', '<', $now->copy()->startOfMonth())
            ->count();

        $trends = [
            'users_delta' => $lastMonth > 0
                ? round((($stats['new_this_month'] - $lastMonth) / $lastMonth) * 100, 1)
                : ($stats['new_this_month'] > 0 ? 100 : 0),
        ];

        $recentUsers = User::query()
            ->with(['roles', 'department'])
            ->latest()
            ->limit(5)
            ->get();

        $recentLogins = LoginLog::query()
            ->with('user')
            ->whereNotNull('user_id')
            ->latest('login_at')
            ->limit(8)
            ->get();

        // Staff growth (last 6 months)
        $monthStart = $now->copy()->subMonths(5)->startOfMonth();
        $userGrowth = User::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as total')
            ->where('created_at', '>=', $monthStart)
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $key = $now->copy()->subMonths($i)->format('Y-m');
            $labels[] = $now->copy()->subMonths($i)->format('M');
            $values[] = (int) ($userGrowth[$key]->total ?? 0);
        }

        $chartData = [
            'labels' => $labels,
            'values' => $values,
            'active_vs_inactive' => [
                $stats['active_users'],
                max(0, $stats['total_users'] - $stats['active_users']),
            ],
        ];

        // Attendance overview — derived from real attendance records
        $presentToday = \App\Models\Attendance::whereDate('date', $now->toDateString())
            ->where('status', 'present')->count();
        $absentToday = \App\Models\Attendance::whereDate('date', $now->toDateString())
            ->where('status', 'absent')->count();
        $lateToday = \App\Models\Attendance::whereDate('date', $now->toDateString())
            ->where('status', 'late')->count();
        $onLeave = \App\Models\LeaveRequest::where('status', 'approved')
            ->where('start_date', '<=', $now->toDateString())
            ->where('end_date', '>=', $now->toDateString())->count();

        $totalStaff = User::where('status', UserStatus::Active->value)->count();

        $attendance = [
            'present' => $presentToday,
            'absent' => $absentToday,
            'late' => $lateToday,
            'leave' => $onLeave,
            'present_pct' => $totalStaff > 0
                ? round(($presentToday / $totalStaff) * 100, 1)
                : 0,
        ];

        $activities = app(AuditService::class)->paginate(8);

        return view('dashboard.index', compact(
            'stats',
            'trends',
            'recentUsers',
            'recentLogins',
            'chartData',
            'attendance',
            'activities'
        ));
    }
}
