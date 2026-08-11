<?php

namespace App\Http\Controllers;

use App\Enums\LeaveStatus;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\DailyTask;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManagementController extends Controller
{
    public function summary(): View
    {
        $this->authorize('management.view');

        $monthStart = now()->startOfMonth();

        $today = now()->toDateString();

        $totalEmployees = User::query()->active()->count();
        $attendanceToday = Attendance::query()->whereDate('date', $today)->where('status', 'present')->count();

        $totalClients = Client::query()->count();
        $newLeads = Lead::query()->whereDate('created_at', '>=', $monthStart)->count();
        $leadsTotal = max(Lead::query()->whereDate('created_at', '>=', now()->startOfYear())->count(), 1);
        $leadsWon = Lead::query()
            ->whereDate('created_at', '>=', now()->startOfYear())
            ->whereIn('status', ['won', 'converted'])
            ->count();
        $leadConversion = round(($leadsWon / $leadsTotal) * 100, 1);

        $activeProjects = Project::query()->whereIn('status', ['Planning', 'Not Started', 'In Progress', 'On Hold'])->count();
        $completedProjects = Project::query()->where('status', 'Completed')->count();
        $pendingTasks = DailyTask::query()->whereNotIn('status', ['completed', 'cancelled'])->count();
        $overdueTasks = DailyTask::query()->where('status', 'overdue')->count();

        $monthlyRevenue = (float) Payment::query()
            ->whereDate('payment_date', '>=', $monthStart)
            ->sum('amount');
        $monthlyExpenses = (float) Expense::query()
            ->whereDate('expense_date', '>=', $monthStart)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');
        $netProfit = $monthlyRevenue - $monthlyExpenses;

        $outstanding = (float) Invoice::query()
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as due')
            ->value('due');

        $upcomingDeadlines = collect()
            ->merge(
                ProjectTask::query()
                    ->with(['project:id,name', 'assignedTo:id,name'])
                    ->whereNotNull('deadline')
                    ->whereBetween('deadline', [now()->toDateString(), now()->addDays(14)->toDateString()])
                    ->where('status', '!=', 'Completed')
                    ->get()
                    ->map(fn ($t) => [
                        'title' => $t->title,
                        'date' => $t->deadline,
                        'type' => 'Task',
                        'url' => route('projects.tasks.show', $t),
                        'project' => $t->project?->name,
                        'assignee' => $t->assignedTo?->name,
                    ])
            )
            ->merge(
                Project::query()
                    ->whereNotNull('deadline')
                    ->whereBetween('deadline', [now()->toDateString(), now()->addDays(14)->toDateString()])
                    ->get()
                    ->map(fn ($p) => [
                        'title' => $p->name,
                        'date' => $p->deadline,
                        'type' => 'Project',
                        'url' => route('projects.show', $p),
                    ])
            )
            ->sortBy('date')
            ->take(8);

        $revenueTrend = Payment::query()
            ->whereDate('payment_date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month")
            ->selectRaw('SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->month => (float) $r->total]);

        $expenseTrend = Expense::query()
            ->whereDate('expense_date', '>=', now()->subMonths(5)->startOfMonth())
            ->whereIn('status', ['approved', 'paid'])
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month")
            ->selectRaw('SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->month => (float) $r->total]);

        $alerts = collect();

        if ($overdueTasks > 0) {
            $alerts->push(['type' => 'danger', 'icon' => 'bi-exclamation-triangle', 'text' => "{$overdueTasks} overdue task(s) require attention."]);
        }
        if ($outstanding > 0) {
            $alerts->push(['type' => 'warning', 'icon' => 'bi-clock-history', 'text' => 'Outstanding receivables: '.number_format($outstanding, 2).'.']);
        }
        $pendingLeaves = \App\Models\LeaveRequest::query()->pending()->count();
        if ($pendingLeaves > 0) {
            $alerts->push(['type' => 'info', 'icon' => 'bi-door-open', 'text' => "{$pendingLeaves} leave request(s) awaiting review."]);
        }
        if ($pendingTasks > 0) {
            $alerts->push(['type' => 'secondary', 'icon' => 'bi-list-check', 'text' => "{$pendingTasks} pending daily task(s) across teams."]);
        }
        if ($alerts->isEmpty()) {
            $alerts->push(['type' => 'success', 'icon' => 'bi-check-circle', 'text' => 'Everything looks healthy. No alerts right now.']);
        }

        $projectStatus = Project::query()
            ->selectRaw("CASE WHEN status = 'Completed' THEN 'Completed' WHEN status = 'Cancelled' THEN 'Cancelled' ELSE 'In Progress' END as label, count(*) as total")
            ->groupBy('label')
            ->pluck('total', 'label');

        return view('management.summary', compact(
            'totalEmployees',
            'attendanceToday',
            'totalClients',
            'newLeads',
            'leadConversion',
            'activeProjects',
            'completedProjects',
            'pendingTasks',
            'overdueTasks',
            'monthlyRevenue',
            'monthlyExpenses',
            'netProfit',
            'outstanding',
            'upcomingDeadlines',
            'revenueTrend',
            'expenseTrend',
            'alerts',
            'projectStatus',
        ));
    }
}
