<?php

namespace App\Http\Controllers\Hr;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreTaskRequest;
use App\Http\Requests\Hr\UpdateTaskRequest;
use App\Models\DailyTask;
use App\Models\Department;
use App\Repositories\EmployeeRepository;
use App\Repositories\TaskRepository;
use App\Services\AuditService;
use App\Services\Hr\TaskService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DailyTaskController extends Controller
{
    public function __construct(
        protected TaskRepository $repository,
        protected TaskService $service,
        protected EmployeeRepository $employees,
        protected AuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->service->applyOverdue();

        $filters = $request->only(['search', 'assigned_to', 'department_id', 'status', 'priority', 'my']);

        return view('hr.tasks.index', [
            'tasks' => $this->repository->paginate($filters, (int) $request->integer('per_page', 15) ?: 15),
            'filters' => $filters,
            'employees' => $this->employees->employeesOptions(),
            'departments' => Department::query()->active()->orderBy('name')->pluck('name', 'id')->all(),
            'statuses' => TaskStatus::options(),
            'priorities' => Priority::options(),
        ]);
    }

    public function create(): View
    {
        return view('hr.tasks.create', [
            'employees' => $this->employees->employeesOptions(),
            'departments' => Department::query()->active()->orderBy('name')->get(),
            'priorities' => Priority::options(),
            'statuses' => TaskStatus::options(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('task-attachments', 'public');
        }

        $task = $this->service->create($data);

        $this->audit->log('Task created', 'hr', $task, event: 'created');

        if ($task->assigned_to && $task->assigned_to !== auth()->id()) {
            app(NotificationService::class)->send(
                $task->assignee,
                'Task assigned to you',
                $task->title.($task->deadline ? ' — due '.$task->deadline->format('M d, Y') : ''),
                ['category' => 'task', 'url' => route('hr.tasks.board'), 'color' => 'info']
            );
        }

        return redirect()
            ->route('hr.tasks.index')
            ->with('toast', ['type' => 'success', 'message' => 'Task created successfully.']);
    }

    public function edit(DailyTask $task): View
    {
        return view('hr.tasks.edit', [
            'task' => $task,
            'employees' => $this->employees->employeesOptions(),
            'departments' => Department::query()->active()->orderBy('name')->get(),
            'priorities' => Priority::options(),
            'statuses' => TaskStatus::options(),
        ]);
    }

    public function update(UpdateTaskRequest $request, DailyTask $task): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            if ($task->attachment) {
                Storage::disk('public')->delete($task->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('task-attachments', 'public');
        }

        $this->service->update($task, $data);

        $this->audit->log('Task updated', 'hr', $task, event: 'updated');

        return redirect()
            ->route('hr.tasks.index')
            ->with('toast', ['type' => 'success', 'message' => 'Task updated successfully.']);
    }

    public function changeStatus(Request $request, DailyTask $task): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:pending,in_progress,on_hold,completed,cancelled,overdue']]);

        $this->authorize('update', $task);

        $this->service->changeStatus($task, $request->string('status'));

        if ($request->string('status')->toString() === 'completed' && $task->created_by && $task->created_by !== auth()->id()) {
            app(NotificationService::class)->send(
                $task->assigner,
                'Task completed',
                $task->assignee?->name.' completed "'.$task->title.'".',
                ['category' => 'task', 'url' => route('hr.tasks.index'), 'color' => 'success']
            );
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Task status updated.']);
    }

    public function destroy(DailyTask $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        if ($task->attachment) {
            Storage::disk('public')->delete($task->attachment);
        }

        $this->service->delete($task);

        $this->audit->log('Task deleted', 'hr', $task, event: 'deleted');

        return back()->with('toast', ['type' => 'success', 'message' => 'Task deleted successfully.']);
    }

    public function board(): View
    {
        $this->service->applyOverdue();

        $tasks = DailyTask::query()
            ->with(['assignee:id,name,avatar', 'assigner:id,name', 'department:id,name'])
            ->where('assigned_to', auth()->id())
            ->orderBy('deadline')
            ->get();

        return view('hr.tasks.board', [
            'tasks' => $tasks,
            'statuses' => TaskStatus::options(),
        ]);
    }
}
