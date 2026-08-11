<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\ProjectTaskRequest;
use App\Models\{Project, ProjectTask, ProjectMilestone, ProjectFile, ProjectActivity, User};
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProjectTaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('project_tasks.view');

        $query = ProjectTask::with(['project', 'milestone', 'assignedTo', 'assignedBy'])
            ->latest();

        // My Tasks filter view
        if ($request->get('view') === 'my') {
            $query->where('assigned_to', auth()->id());
        }

        if ($request->filled('filter')) {
            $today = now()->startOfDay();
            switch ($request->filter) {
                case 'today':
                    $query->whereDate('deadline', $today);
                    break;
                case 'upcoming':
                    $query->whereDate('deadline', '>', $today);
                    break;
                case 'overdue':
                    $query->where('status', '!=', 'Completed')->whereDate('deadline', '<', $today);
                    break;
                case 'completed':
                    $query->where('status', 'Completed');
                    break;
            }
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('task_code', 'like', "%{$s}%")
                  ->orWhereHas('project', fn($p) => $p->where('name', 'like', "%{$s}%"));
            });
        }

        $tasks = $query->paginate(15)->withQueryString();
        $projects = Project::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('projects.tasks.index', compact('tasks', 'projects', 'users'));
    }

    public function board(Request $request, ?Project $project = null): View
    {
        $this->authorize('project_tasks.view');

        $projects = Project::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        $selectedProjectId = $project ? $project->id : $request->get('project_id');

        $query = ProjectTask::with(['project', 'assignedTo', 'milestone', 'comments', 'attachments']);

        if ($selectedProjectId) {
            $query->where('project_id', $selectedProjectId);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->orderBy('order')->latest()->get();

        $columns = [
            'To Do' => $tasks->where('status', 'To Do'),
            'In Progress' => $tasks->where('status', 'In Progress'),
            'Review' => $tasks->where('status', 'Review'),
            'Testing' => $tasks->where('status', 'Testing'),
            'Completed' => $tasks->where('status', 'Completed'),
        ];

        return view('projects.kanban', compact('columns', 'projects', 'users', 'selectedProjectId', 'project'));
    }

    public function store(ProjectTaskRequest $request): RedirectResponse
    {
        $this->authorize('project_tasks.create');

        $validated = $request->validated();
        $validated['status'] = $validated['status'] ?? 'To Do';
        $validated['task_code'] = ProjectTask::generateCode();
        $validated['assigned_by'] = auth()->id();
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $task = ProjectTask::create($validated);

        // Upload attachments if provided
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('project_files/' . $task->project_id . '/tasks', 'public');
                ProjectFile::create([
                    'project_id' => $task->project_id,
                    'task_id' => $task->id,
                    'name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => 'Screenshots',
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        $task->project->recalculateProgress();
        if ($task->milestone) {
            $task->milestone->recalculateProgress();
        }

        if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
            app(NotificationService::class)->send(
                $task->assignedTo,
                'Task assigned to you',
                $task->title.' — '.$task->project->name.($task->deadline ? ' (due '.$task->deadline->format('M d, Y').')' : ''),
                ['category' => 'task', 'url' => route('projects.tasks.show', $task), 'color' => 'info']
            );
        }

        $assignedName = $task->assignedTo ? $task->assignedTo->name : 'unassigned';
        ProjectActivity::log($task->project_id, 'Task created', "Task '{$task->title}' assigned to {$assignedName}.", 'ProjectTask', $task->id);

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Task created successfully.']);
    }

    public function show(ProjectTask $task): View|JsonResponse
    {
        $this->authorize('project_tasks.view');

        $task->load([
            'project',
            'milestone',
            'assignedTo',
            'assignedBy',
            'comments.user',
            'attachments.uploader',
            'timeEntries.user',
        ]);

        if (request()->wantsJson()) {
            return response()->json($task);
        }

        return view('projects.tasks.show', compact('task'));
    }

    public function update(ProjectTaskRequest $request, ProjectTask $task): RedirectResponse
    {
        $this->authorize('project_tasks.edit');

        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();
        $oldStatus = $task->status;

        $task->update($validated);

        if ($oldStatus !== $task->status) {
            ProjectActivity::log($task->project_id, 'Task status changed', "Task '{$task->title}' status changed to {$task->status}.", 'ProjectTask', $task->id);
        }

        $task->project->recalculateProgress();
        if ($task->milestone) {
            $task->milestone->recalculateProgress();
        }

        if ($task->status === 'Completed') {
            $assigner = $task->assignedBy;
            if ($assigner && $assigner->id !== auth()->id()) {
                app(NotificationService::class)->send(
                    $assigner,
                    'Task completed',
                    $task->assignedTo?->name.' completed "'.$task->title.'" in '.$task->project->name.'.',
                    ['category' => 'task', 'url' => route('projects.tasks.show', $task), 'color' => 'success']
                );
            }
        }

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Task updated successfully.']);
    }

    public function updateStatus(Request $request, ProjectTask $task): JsonResponse|RedirectResponse
    {
        $this->authorize('project_tasks.edit');

        $validated = $request->validate([
            'status' => 'required|string|in:To Do,In Progress,Review,Testing,Completed,On Hold,Cancelled,Overdue',
        ]);

        $oldStatus = $task->status;
        $task->update([
            'status' => $validated['status'],
            'progress' => $validated['status'] === 'Completed' ? 100 : ($task->progress < 100 ? $task->progress : 50),
            'updated_by' => auth()->id(),
        ]);

        ProjectActivity::log($task->project_id, 'Task status changed', "Task '{$task->title}' moved from {$oldStatus} to {$task->status}.", 'ProjectTask', $task->id);

        $task->project->recalculateProgress();
        if ($task->milestone) {
            $task->milestone->recalculateProgress();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task status updated.',
                'status' => $task->status,
                'progress' => $task->progress,
            ]);
        }

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Task status updated.']);
    }

    public function destroy(ProjectTask $task): RedirectResponse
    {
        $this->authorize('project_tasks.delete');

        $projectId = $task->project_id;
        $title = $task->title;
        $task->delete();

        ProjectActivity::log($projectId, 'Task deleted', "Task '{$title}' deleted.");

        return redirect()->back()->with('toast', ['type' => 'success', 'message' => 'Task deleted.']);
    }
}
