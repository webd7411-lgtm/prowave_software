<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\{User, ProjectTask};
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkloadController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('project_team.view');

        $users = User::with(['assignedTasks' => function ($q) {
            $q->where('status', '!=', 'Cancelled');
        }])
        ->where('status', 'active')
        ->orderBy('name')
        ->get()
        ->map(function ($user) {
            $tasks = $user->assignedTasks;
            $user->total_tasks_count = $tasks->count();
            $user->in_progress_count = $tasks->where('status', 'In Progress')->count();
            $user->completed_count = $tasks->where('status', 'Completed')->count();
            $user->overdue_count = $tasks->where('status', '!=', 'Completed')->filter(fn($t) => $t->deadline && $t->deadline->lt(now()->startOfDay()))->count();
            $user->total_estimated_hours = $tasks->sum('estimated_hours');
            $user->total_actual_hours = $tasks->sum('actual_hours');
            
            // Workload level calculation
            $pendingCount = $user->total_tasks_count - $user->completed_count;
            if ($pendingCount == 0) {
                $user->workload_status = 'Light';
                $user->workload_badge = 'bg-success-subtle text-success';
                $user->workload_percentage = 20;
            } elseif ($pendingCount <= 3) {
                $user->workload_status = 'Optimal';
                $user->workload_badge = 'bg-primary-subtle text-primary';
                $user->workload_percentage = 50;
            } elseif ($pendingCount <= 6) {
                $user->workload_status = 'Heavy';
                $user->workload_badge = 'bg-warning-subtle text-warning';
                $user->workload_percentage = 80;
            } else {
                $user->workload_status = 'Overloaded';
                $user->workload_badge = 'bg-danger-subtle text-danger';
                $user->workload_percentage = 100;
            }

            return $user;
        });

        return view('projects.workload', compact('users'));
    }
}
