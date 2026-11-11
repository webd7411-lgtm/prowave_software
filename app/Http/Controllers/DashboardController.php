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
    public function __invoke(): View|\Illuminate\Http\RedirectResponse
    {
        if (! auth()->user()->can('dashboard.view')) {
            if (auth()->user()->can('crm_dashboard.view')) {
                return redirect()->route('crm.dashboard');
            }
            if (auth()->user()->can('hr_dashboard.view')) {
                return redirect()->route('hr.dashboard');
            }
            if (auth()->user()->can('finance_dashboard.view')) {
                return redirect()->route('finance.dashboard');
            }
            if (auth()->user()->can('leads.view')) {
                return redirect()->route('crm.leads.index');
            }
            if (auth()->user()->can('projects.view')) {
                return redirect()->route('projects.index');
            }
            return redirect()->route('profile.show')->with('toast', ['type' => 'info', 'message' => 'Welcome! You have been redirected to your profile.']);
        }

        $now = now();
        $monthStartDt = $now->copy()->startOfMonth();
        $prevMonthStartDt = $now->copy()->subMonth()->startOfMonth();

        $userAgg = User::query()
            ->selectRaw(
                'COUNT(*) as total_users,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as inactive_users,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as suspended_users,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_this_month,
                SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as last_month',
                [
                    UserStatus::Active->value,
                    UserStatus::Inactive->value,
                    UserStatus::Suspended->value,
                    $monthStartDt,
                    $prevMonthStartDt,
                    $monthStartDt,
                ]
            )
            ->first();

        $loginAgg = LoginLog::query()
            ->where('status', 'success')
            ->selectRaw(
                'COUNT(*) as total_logins,
                SUM(CASE WHEN login_at >= ? AND login_at <= ? THEN 1 ELSE 0 END) as logins_today',
                [$now->copy()->startOfDay(), $now->copy()->endOfDay()]
            )
            ->first();

        $stats = [
            'total_users' => (int) ($userAgg->total_users ?? 0),
            'active_users' => (int) ($userAgg->active_users ?? 0),
            'inactive_users' => (int) ($userAgg->inactive_users ?? 0),
            'suspended_users' => (int) ($userAgg->suspended_users ?? 0),
            'new_this_month' => (int) ($userAgg->new_this_month ?? 0),
            'total_departments' => Department::count(),
            'total_logins' => (int) ($loginAgg->total_logins ?? 0),
            'logins_today' => (int) ($loginAgg->logins_today ?? 0),
        ];

        // Delta vs. last month (for KPI trend indicators)
        $lastMonth = (int) ($userAgg->last_month ?? 0);

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
