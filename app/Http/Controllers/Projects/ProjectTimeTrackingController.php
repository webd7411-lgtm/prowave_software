<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\{Project, ProjectTask, ProjectTimeEntry, ProjectActivity};
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ProjectTimeTrackingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('time_tracking.view');

        $user = auth()->user();
        $query = ProjectTimeEntry::with(['project', 'task', 'user'])->latest();

        if (!$user->hasAnyRole(['Super Admin', 'Administrator', 'Project Manager'])) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $timeEntries = $query->paginate(20)->withQueryString();

        // Calculate time stats for logged in user
        $todayMinutes = ProjectTimeEntry::where('user_id', $user->id)
            ->whereDate('created_at', now()->today())
            ->sum('duration_minutes');

        $weekMinutes = ProjectTimeEntry::where('user_id', $user->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('duration_minutes');

        $monthMinutes = ProjectTimeEntry::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('duration_minutes');

        $totalMinutes = ProjectTimeEntry::where('user_id', $user->id)->sum('duration_minutes');

        $activeTimer = ProjectTimeEntry::where('user_id', $user->id)->where('is_running', true)->first();

        $projects = Project::orderBy('name')->get();

        return view('projects.time_tracking', compact(
            'timeEntries',
            'todayMinutes',
            'weekMinutes',
            'monthMinutes',
            'totalMinutes',
            'activeTimer',
            'projects'
        ));
    }

    public function startTimer(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('time_tracking.create');

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'description' => 'nullable|string',
        ]);

        $userId = auth()->id();

        // Stop any running timer first
        $running = ProjectTimeEntry::where('user_id', $userId)->where('is_running', true)->get();
        foreach ($running as $timer) {
            $duration = now()->diffInMinutes($timer->start_time);
            $timer->update([
                'end_time' => now(),
                'duration_minutes' => max(1, $duration),
                'is_running' => false,
            ]);
        }

        $entry = ProjectTimeEntry::create([
            'project_id' => $validated['project_id'],
            'task_id' => $validated['task_id'] ?? null,
            'user_id' => $userId,
            'start_time' => now(),
            'description' => $validated['description'] ?? 'Timer started',
            'is_running' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'entry' => $entry, 'message' => 'Timer started.']);
        }

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Timer started.']);
    }

    public function stopTimer(Request $request, ProjectTimeEntry $entry): JsonResponse|RedirectResponse
    {
        $this->authorize('time_tracking.create');

        if ($entry->user_id !== auth()->id() && !auth()->user()->hasAnyRole(['Super Admin', 'Administrator', 'Project Manager'])) {
            abort(403);
        }

        if ($entry->is_running) {
            $duration = now()->diffInMinutes($entry->start_time);
            $entry->update([
                'end_time' => now(),
                'duration_minutes' => max(1, $duration),
                'is_running' => false,
            ]);

            // Update task actual hours if task is associated
            if ($entry->task_id) {
                $task = ProjectTask::find($entry->task_id);
                if ($task) {
                    $totalTaskMinutes = ProjectTimeEntry::where('task_id', $task->id)->sum('duration_minutes');
                    $task->update(['actual_hours' => round($totalTaskMinutes / 60, 2)]);
                }
            }

            // Update project actual hours
            $totalProjMinutes = ProjectTimeEntry::where('project_id', $entry->project_id)->sum('duration_minutes');
            Project::where('id', $entry->project_id)->update(['actual_hours' => round($totalProjMinutes / 60, 2)]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'entry' => $entry, 'message' => 'Timer stopped.']);
        }

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Timer stopped.']);
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $this->authorize('time_tracking.create');

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'hours' => 'required|numeric|min:0.1',
            'date' => 'required|date',
            'description' => 'required|string',
        ]);

        $durationMinutes = (int) round($validated['hours'] * 60);

        $entry = ProjectTimeEntry::create([
            'project_id' => $validated['project_id'],
            'task_id' => $validated['task_id'] ?? null,
            'user_id' => auth()->id(),
            'start_time' => \Carbon\Carbon::parse($validated['date']),
            'end_time' => \Carbon\Carbon::parse($validated['date'])->addMinutes($durationMinutes),
            'duration_minutes' => $durationMinutes,
            'description' => $validated['description'],
            'is_running' => false,
        ]);

        // Update task actual hours
        if ($entry->task_id) {
            $task = ProjectTask::find($entry->task_id);
            if ($task) {
                $totalTaskMinutes = ProjectTimeEntry::where('task_id', $task->id)->sum('duration_minutes');
                $task->update(['actual_hours' => round($totalTaskMinutes / 60, 2)]);
            }
        }

        // Update project actual hours
        $totalProjMinutes = ProjectTimeEntry::where('project_id', $entry->project_id)->sum('duration_minutes');
        Project::where('id', $entry->project_id)->update(['actual_hours' => round($totalProjMinutes / 60, 2)]);

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Time log saved successfully.']);
    }
}
