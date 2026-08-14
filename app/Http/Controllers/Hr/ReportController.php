<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DailyTask;
use App\Models\DailyWorkUpdate;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('reports.view');

        return view('hr.reports.index', [
            'departments' => Department::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function employees(Request $request): View
    {
        $filters = $this->filters($request);

        $employees = User::query()
            ->whereNotNull('employee_id')
            ->with(['department', 'shift'])
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->integer('department_id')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('joining_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('joining_date', '<=', $request->date('to')))
            ->orderBy('name')
            ->get();

        $departments = Department::query()->active()->orderBy('name')->get();

        return view('hr.reports.employees', compact('employees', 'filters', 'departments'));
    }

    public function attendance(Request $request): View
    {
        $filters = $this->filters($request);

        $records = Attendance::query()
            ->with(['employee:id,name,employee_id,department_id'])
            ->when($request->filled('department_id'), fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department_id', $request->integer('department_id'))))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('date', '<=', $request->date('to')))
            ->orderBy('date', 'desc')
            ->get();

        $summary = [
            'present' => $records->whereIn('status', ['present', 'late'])->count(),
            'late' => $records->where('status', 'late')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'leave' => $records->where('status', 'on_leave')->count(),
            'total' => $records->count(),
        ];

        $departments = Department::query()->active()->orderBy('name')->get();

        return view('hr.reports.attendance', compact('records', 'summary', 'filters', 'departments'));
    }

    public function leaves(Request $request): View
    {
        $filters = $this->filters($request);

        $leaves = LeaveRequest::query()
            ->with(['employee:id,name,department_id', 'leaveType:id,name'])
            ->when($request->filled('department_id'), fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department_id', $request->integer('department_id'))))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('start_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('end_date', '<=', $request->date('to')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('created_at', 'desc')
            ->get();

        $departments = Department::query()->active()->orderBy('name')->get();

        return view('hr.reports.leaves', compact('leaves', 'filters', 'departments'));
    }

    public function tasks(Request $request): View
    {
        $filters = $this->filters($request);

        $tasks = DailyTask::query()
            ->with(['assignee:id,name,department_id', 'department:id,name'])
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->integer('department_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'pending' => $tasks->whereIn('status', ['pending', 'in_progress', 'on_hold', 'overdue'])->count(),
        ];

        $departments = Department::query()->active()->orderBy('name')->get();

        return view('hr.reports.tasks', compact('tasks', 'summary', 'filters', 'departments'));
    }

    public function updates(Request $request): View
    {
        $filters = $this->filters($request);

        $updates = DailyWorkUpdate::query()
            ->with(['employee:id,name,department_id'])
            ->when($request->filled('department_id'), fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department_id', $request->integer('department_id'))))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('date', '<=', $request->date('to')))
            ->orderBy('date', 'desc')
            ->get();

        $departments = Department::query()->active()->orderBy('name')->get();

        return view('hr.reports.updates', compact('updates', 'filters', 'departments'));
    }

    protected function filters(Request $request): array
    {
        return $request->only(['department_id', 'from', 'to', 'status']);
    }
}
