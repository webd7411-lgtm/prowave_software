<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\ProjectRequest;
use App\Models\{Project, Client, User, ProjectMember, ProjectTask, ProjectFile, ProjectActivity, ClientLedger, Payment};
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('projects.view');

        $query = Project::with(['client', 'manager', 'members.user', 'tasks'])
            ->withCount(['tasks as pending_tasks_count' => function ($q) {
                $q->where('status', '!=', 'Completed');
            }])
            ->withCount(['tasks as completed_tasks_count' => function ($q) {
                $q->where('status', 'Completed');
            }]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('company_name', 'like', "%{$search}%"))
                  ->orWhereHas('manager', fn($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Priority Filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Client Filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Manager Filter
        if ($request->filled('manager_id')) {
            $query->where('project_manager_id', $request->manager_id);
        }

        // Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('deadline')) {
            $query->whereDate('deadline', '<=', $request->deadline);
        }

        // Overdue Filter
        if ($request->boolean('overdue')) {
            $query->where('status', '!=', 'Completed')
                  ->where('deadline', '<', now()->startOfDay());
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['name', 'project_code', 'deadline', 'budget', 'progress', 'status', 'priority', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $projects = $query->paginate(12)->withQueryString();

        // Summary Widget Counters
        $totalProjects = Project::count();
        $activeProjects = Project::whereIn('status', ['Planning', 'In Progress', 'Not Started'])->count();
        $completedProjects = Project::where('status', 'Completed')->count();
        $overdueProjects = Project::where('status', '!=', 'Completed')->where('deadline', '<', now()->startOfDay())->count();

        $clients = Client::orderBy('company_name')->get();
        $managers = User::orderBy('name')->get();

        return view('projects.index', compact(
            'projects',
            'clients',
            'managers',
            'totalProjects',
            'activeProjects',
            'completedProjects',
            'overdueProjects'
        ));
    }

    public function create(): View
    {
        $this->authorize('projects.create');

        $clients = Client::where('status', 'active')->orderBy('company_name')->get();
        $users = User::where('status', 'active')->orderBy('name')->get();
        $project = new Project();

        return view('projects.create', compact('clients', 'users', 'project'));
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $this->authorize('projects.create');

        $validated = $request->validated();
        $validated['project_code'] = Project::generateCode();
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        $validated['budget'] = $validated['budget'] ?? 0;
        $validated['currency'] = $validated['currency'] ?? setting('general.currency', 'PKR');
        $validated['estimated_hours'] = $validated['estimated_hours'] ?? 0;
        $validated['priority'] = $validated['priority'] ?? 'Medium';

        unset($validated['team'], $validated['tasks'], $validated['milestones'], $validated['documents']);

        $project = Project::create($validated);

        $teamMemberIds = collect($request->team ?? [])->pluck('user_id')->filter()->values();
        if ($project->project_manager_id) {
            $teamMemberIds->push($project->project_manager_id);
        }

        app(NotificationService::class)->sendToPermission(
            'projects.view',
            'New project created',
            $project->name.($project->client ? ' — '.$project->client->company_name : ''),
            ['category' => 'project', 'url' => route('projects.show', $project), 'color' => 'primary']
        );

        if ($teamMemberIds->isNotEmpty()) {
            $members = User::whereIn('id', $teamMemberIds->unique())->get();
            app(NotificationService::class)->sendMany(
                $members,
                'You were added to project '.$project->name,
                $project->start_date ? 'Starts '.$project->start_date->format('M d, Y') : 'Project team assigned.',
                ['category' => 'project', 'url' => route('projects.show', $project), 'color' => 'info', 'include_self' => true]
            );
        }

        // Assign PM to team if selected
        if ($project->project_manager_id) {
            ProjectMember::firstOrCreate([
                'project_id' => $project->id,
                'user_id' => $project->project_manager_id,
                'role' => 'Project Manager',
            ], [
                'assigned_date' => now(),
                'status' => 'active',
            ]);
        }

        // Assign additional team members
        if ($request->filled('team')) {
            foreach ($request->team as $member) {
                if (!empty($member['user_id'])) {
                    ProjectMember::firstOrCreate([
                        'project_id' => $project->id,
                        'user_id' => $member['user_id'],
                        'role' => $member['role'] ?? 'Developer',
                    ], [
                        'assigned_date' => now(),
                        'hours_allocation' => $member['hours_allocation'] ?? 0,
                        'status' => 'active',
                    ]);
                }
            }
        }

        // Initial Tasks
        if ($request->filled('tasks')) {
            foreach ($request->tasks as $t) {
                if (!empty($t['title'])) {
                    ProjectTask::create([
                        'task_code' => ProjectTask::generateCode(),
                        'project_id' => $project->id,
                        'title' => $t['title'],
                        'assigned_to' => $t['assigned_to'] ?? null,
                        'assigned_by' => auth()->id(),
                        'priority' => $t['priority'] ?? 'Medium',
                        'deadline' => $t['deadline'] ?? null,
                        'status' => 'To Do',
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }
            }
        }

        // Initial Documents
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('project_files/' . $project->id, 'public');
                ProjectFile::create([
                    'project_id' => $project->id,
                    'name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => 'Client Documents',
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        ProjectActivity::log($project->id, 'Project created', "Project '{$project->name}' was created.");

        return redirect()->route('projects.show', $project)
            ->with('toast', ['type' => 'success', 'message' => 'Project created successfully.']);
    }

    public function show(Project $project): View
    {
        $this->authorize('projects.view');

        $project->load([
            'client',
            'manager',
            'members.user',
            'tasks.assignedTo',
            'files.uploader',
            'timeEntries.user',
            'timeEntries.task',
            'expenses.addedBy',
            'activities.user',
            'payments.client',
        ]);

        $users = User::where('status', 'active')->orderBy('name')->get();

        // Financial metrics
        $paidAmount = $project->paid_amount;
        $outstandingAmount = $project->remaining_balance;
        $totalExpenses = $project->expenses->sum('amount');
        $estimatedProfit = $project->budget - $totalExpenses;
        $actualProfit = $paidAmount - $totalExpenses;

        // Task stats
        $totalTasks = $project->tasks->count();
        $completedTasks = $project->tasks->where('status', 'Completed')->count();
        $inProgressTasks = $project->tasks->where('status', 'In Progress')->count();
        $overdueTasks = $project->tasks->where('status', '!=', 'Completed')->filter(fn($t) => $t->deadline && $t->deadline->lt(now()->startOfDay()))->count();

        // Total tracked time
        $totalTrackedMinutes = $project->timeEntries->sum('duration_minutes');
        $totalTrackedHours = round($totalTrackedMinutes / 60, 2);

        return view('projects.show', compact(
            'project',
            'users',
            'paidAmount',
            'outstandingAmount',
            'totalExpenses',
            'estimatedProfit',
            'actualProfit',
            'totalTasks',
            'completedTasks',
            'inProgressTasks',
            'overdueTasks',
            'totalTrackedHours'
        ));
    }

    public function edit(Project $project): View
    {
        $this->authorize('projects.edit');

        $clients = Client::where('status', 'active')->orderBy('company_name')->get();
        $users = User::where('status', 'active')->orderBy('name')->get();

        return view('projects.edit', compact('project', 'clients', 'users'));
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('projects.edit');

        $validated = $request->validated();
        $validated['updated_by'] = auth()->id();

        $oldStatus = $project->status;
        $oldDeadline = $project->deadline;

        $project->update($validated);

        if ($oldStatus !== $project->status) {
            ProjectActivity::log($project->id, 'Status changed', "Status changed from '{$oldStatus}' to '{$project->status}'.");
        }

        if ($oldDeadline != $project->deadline) {
            $formattedNew = $project->deadline ? $project->deadline->format('M d, Y') : 'None';
            ProjectActivity::log($project->id, 'Deadline changed', "Deadline updated to {$formattedNew}.");
        }

        $project->recalculateProgress();

        return redirect()->route('projects.show', $project)
            ->with('toast', ['type' => 'success', 'message' => 'Project updated successfully.']);
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('projects.delete');

        $project->update(['deleted_by' => auth()->id()]);
        $project->delete();

        return redirect()->route('projects.index')
            ->with('toast', ['type' => 'success', 'message' => 'Project deleted successfully.']);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('projects.export');

        $projects = Project::with(['client', 'manager'])->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="projects_export_' . date('Y_m_d_H_i') . '.csv"',
        ];

        return response()->stream(function () use ($projects) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Project Code', 'Project Name', 'Client', 'Manager', 'Type', 'Start Date', 'Deadline', 'Priority', 'Status', 'Budget', 'Currency', 'Progress (%)', 'Health']);

            foreach ($projects as $project) {
                fputcsv($handle, [
                    $project->project_code,
                    $project->name,
                    $project->client?->company_name ?? 'N/A',
                    $project->manager?->name ?? 'Unassigned',
                    $project->project_type,
                    $project->start_date ? $project->start_date->format('Y-m-d') : '',
                    $project->deadline ? $project->deadline->format('Y-m-d') : '',
                    $project->priority,
                    $project->status,
                    $project->budget,
                    $project->currency ?? setting('general.currency', 'PKR'),
                    $project->progress,
                    $project->health,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function printProjects(Request $request): View
    {
        $this->authorize('projects.view');

        $query = Project::with(['client', 'manager', 'members.user', 'tasks'])
            ->withCount(['tasks as pending_tasks_count' => function ($q) {
                $q->where('status', '!=', 'Completed');
            }])
            ->withCount(['tasks as completed_tasks_count' => function ($q) {
                $q->where('status', 'Completed');
            }]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('project_code', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('company_name', 'like', "%{$search}%"))
                  ->orWhereHas('manager', fn($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Priority Filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Client Filter
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Manager Filter
        if ($request->filled('manager_id')) {
            $query->where('project_manager_id', $request->manager_id);
        }

        // Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('deadline')) {
            $query->whereDate('deadline', '<=', $request->deadline);
        }

        // Overdue Filter
        if ($request->boolean('overdue')) {
            $query->where('status', '!=', 'Completed')
                  ->where('deadline', '<', now()->startOfDay());
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $allowedSorts = ['name', 'project_code', 'deadline', 'budget', 'progress', 'status', 'priority', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $projects = $query->get();

        // Summary Widget Counters
        $totalProjects = Project::count();
        $activeProjects = Project::whereIn('status', ['Planning', 'In Progress', 'Not Started'])->count();
        $completedProjects = Project::where('status', 'Completed')->count();
        $overdueProjects = Project::where('status', '!=', 'Completed')->where('deadline', '<', now()->startOfDay())->count();

        $clients = Client::orderBy('company_name')->get();
        $managers = User::orderBy('name')->get();

        return view('projects.print', [
            'projects' => $projects,
            'clients' => $clients,
            'managers' => $managers,
            'totalProjects' => $totalProjects,
            'activeProjects' => $activeProjects,
            'completedProjects' => $completedProjects,
            'overdueProjects' => $overdueProjects,
            'filters' => $request->only(['search', 'status', 'priority', 'client_id', 'manager_id', 'overdue', 'start_date', 'deadline'])
        ]);
    }

    public function importTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('projects.create');

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Project Name *', 'Project Type', 'Description', 'Start Date', 'Deadline',
                'Priority', 'Status', 'Budget', 'Currency', 'Estimated Hours', 'Notes'
            ]);
            fputcsv($handle, [
                'E-commerce Platform', 'Web Application', 'Build a modern online store', '2026-09-01', '2026-12-31',
                'Medium', 'In Progress', '500000', 'PKR', '160', 'Initial phase approved.'
            ]);
            fclose($handle);
        }, 'projects-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('projects.create');

        $request->validate(['file' => 'required|file|mimes:csv,txt|max:4096']);

        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        $header = array_map(
            fn ($h) => strtolower(trim((string) preg_replace(['/^\xEF\xBB\xBF/', '/\s*\*+$/'], ['', ''], $h))),
            array_shift($rows) ?? []
        );

        $aliases = [
            'name' => ['project name', 'name', 'project_name', 'title', 'project title'],
            'project_type' => ['project type', 'project_type', 'type', 'category'],
            'description' => ['description', 'desc', 'details'],
            'start_date' => ['start date', 'start_date', 'starts_at'],
            'deadline' => ['deadline', 'end date', 'end_date', 'due date', 'due_date'],
            'priority' => ['priority'],
            'status' => ['status'],
            'budget' => ['budget', 'cost'],
            'currency' => ['currency', 'currency_code'],
            'estimated_hours' => ['estimated hours', 'estimated_hours', 'hours'],
            'notes' => ['notes', 'remarks', 'comment'],
        ];

        $colMap = [];
        foreach ($aliases as $field => $names) {
            foreach ($names as $name) {
                if (in_array($name, $header, true)) {
                    $colMap[$field] = array_search($name, $header, true);
                    break;
                }
            }
        }

        $validTypes = ['Website', 'Web Application', 'Mobile Application', 'Desktop Software', 'ERP', 'CRM', 'E-commerce', 'Maintenance', 'SEO', 'Digital Marketing', 'Other'];
        $validStatuses = ['Planning', 'Not Started', 'In Progress', 'On Hold', 'Completed', 'Cancelled', 'Overdue'];
        $validPriorities = ['Low', 'Medium', 'High', 'Urgent'];

        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (! array_filter($row, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }

            $data = [];
            foreach ($colMap as $field => $idx) {
                $data[$field] = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
            }

            if (($data['name'] ?? '') === '') {
                $skipped++;
                continue;
            }

            $data['project_type'] = in_array(ucwords($data['project_type'] ?? ''), $validTypes, true) ? ucwords($data['project_type']) : 'Web Application';
            $data['status'] = in_array(ucwords($data['status'] ?? ''), $validStatuses, true) ? ucwords($data['status']) : 'Planning';
            $data['priority'] = in_array(ucwords($data['priority'] ?? ''), $validPriorities, true) ? ucwords($data['priority']) : 'Medium';

            if ($data['start_date'] ?? '') {
                $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
            } else {
                $data['start_date'] = null;
            }

            if ($data['deadline'] ?? '') {
                $data['deadline'] = date('Y-m-d', strtotime($data['deadline']));
            } else {
                $data['deadline'] = null;
            }

            $data['budget'] = is_numeric($data['budget'] ?? null) ? $data['budget'] : 0;
            $data['currency'] = !empty($data['currency']) ? strtoupper(trim($data['currency'])) : setting('general.currency', 'PKR');
            $data['estimated_hours'] = is_numeric($data['estimated_hours'] ?? null) ? $data['estimated_hours'] : 0;
            $data['progress'] = $data['status'] === 'Completed' ? 100 : 0;
            $data['project_code'] = Project::generateCode();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $project = Project::create($data);
            ProjectActivity::log($project->id, 'Project imported', "Project '{$project->name}' was imported via CSV.");
            $created++;
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Project import complete: {$created} created, {$skipped} skipped.",
        ]);
    }
}
