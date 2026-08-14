<?php

namespace App\Http\Controllers\Hr;

use App\Enums\EmployeeStatus;
use App\Enums\EmploymentType;
use App\Exports\Hr\EmployeesExport;
use App\Exports\Hr\EmployeesPdfExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreStaffRequest;
use App\Http\Requests\Hr\UpdateStaffRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Shift;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Services\Admin\UserService;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class StaffController extends Controller
{
    public function __construct(
        protected EmployeeRepository $repository,
        protected UserService $service,
        protected AuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('staff.view');

        $filters = $request->only(['search', 'status', 'department_id', 'designation', 'employment_type', 'joining_from', 'joining_to', 'trashed']);

        $employees = $this->repository->paginate($filters, (int) $request->integer('per_page', 15) ?: 15);

        $data = [
            'employees' => $employees,
            'filters' => $filters,
            'departments' => Department::query()->active()->orderBy('name')->pluck('name', 'id')->all(),
            'designations' => Designation::query()->active()->orderBy('name')->pluck('name')->all(),
            'statuses' => EmployeeStatus::options(),
            'employmentTypes' => EmploymentType::options(),
            'stats' => [
                'total' => User::whereNotNull('employee_id')->count(),
                'active' => User::where('employee_status', EmployeeStatus::Active->value)->count(),
                'on_leave' => User::where('employee_status', EmployeeStatus::OnLeave->value)->count(),
                'terminated' => User::where('employee_status', EmployeeStatus::Terminated->value)->count(),
            ],
        ];

        return view('hr.staff.index', $data);
    }

    public function create(): View
    {
        return view('hr.staff.create', [
            'departments' => Department::query()->active()->orderBy('name')->get(),
            'designations' => Designation::query()->active()->orderBy('name')->get(),
            'shifts' => Shift::query()->active()->orderBy('name')->get(),
            'managers' => $this->repository->employeesOptions(),
            'statuses' => EmployeeStatus::options(),
            'employmentTypes' => EmploymentType::options(),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);
        $data['employee_status'] = $data['employee_status'] ?? EmployeeStatus::Active->value;
        $data['is_active'] = $data['employee_status'] === EmployeeStatus::Active->value;
        $data['username'] = $data['username'] ?? null;
        $data['created_by'] = auth()->id();

        $employee = User::create($data);

        $this->audit->log('Staff member created', 'hr', $employee, event: 'created');

        return redirect()
            ->route('hr.staff.show', $employee)
            ->with('toast', ['type' => 'success', 'message' => 'Staff member added successfully.']);
    }

    public function show(User $staff): View
    {
        $staff->load([
            'department', 'shift', 'reportingManager', 'subordinates',
            'attendanceRecords', 'leaveRequests', 'leaveBalances', 'assignedTasks', 'dailyWorkUpdates',
        ]);

        $attendanceSummary = [
            'present' => $staff->attendanceRecords->where('status', 'present')->count(),
            'late' => $staff->attendanceRecords->where('status', 'late')->count(),
            'absent' => $staff->attendanceRecords->where('status', 'absent')->count(),
            'leave' => $staff->attendanceRecords->where('status', 'on_leave')->count(),
        ];

        $taskSummary = [
            'total' => $staff->assignedTasks->count(),
            'completed' => $staff->assignedTasks->where('status', 'completed')->count(),
            'pending' => $staff->assignedTasks->whereIn('status', ['pending', 'in_progress', 'on_hold', 'overdue'])->count(),
        ];

        return view('hr.staff.show', [
            'employee' => $staff,
            'attendanceSummary' => $attendanceSummary,
            'taskSummary' => $taskSummary,
            'leaveSummary' => $staff->leaveRequests->groupBy('status')->map->count(),
            'recentAttendance' => $staff->attendanceRecords->sortByDesc('date')->take(7),
            'recentTasks' => $staff->assignedTasks->sortByDesc('created_at')->take(5),
            'recentUpdates' => $staff->dailyWorkUpdates->sortByDesc('date')->take(5),
        ]);
    }

    public function edit(User $staff): View
    {
        return view('hr.staff.edit', [
            'employee' => $staff,
            'departments' => Department::query()->active()->orderBy('name')->get(),
            'designations' => Designation::query()->active()->orderBy('name')->get(),
            'shifts' => Shift::query()->active()->orderBy('name')->get(),
            'managers' => $this->repository->employeesOptions(),
            'statuses' => EmployeeStatus::options(),
            'employmentTypes' => EmploymentType::options(),
        ]);
    }

    public function update(UpdateStaffRequest $request, User $staff): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['employee_status'] = $data['employee_status'] ?? $staff->employee_status?->value ?? EmployeeStatus::Active->value;
        $data['is_active'] = $data['employee_status'] === EmployeeStatus::Active->value;
        $data['updated_by'] = auth()->id();

        $staff->update($data);

        $this->audit->log('Staff member updated', 'hr', $staff, event: 'updated');

        return redirect()
            ->route('hr.staff.show', $staff)
            ->with('toast', ['type' => 'success', 'message' => 'Staff member updated successfully.']);
    }

    public function destroy(User $staff): RedirectResponse
    {
        $this->authorize('delete', $staff);

        $staff->deleted_by = auth()->id();
        $staff->save();
        $staff->delete();

        $this->audit->log('Staff member deleted', 'hr', $staff, event: 'deleted');

        return back()->with('toast', ['type' => 'success', 'message' => 'Staff member moved to trash.']);
    }

    public function restore(int $id): RedirectResponse
    {
        $staff = User::withTrashed()->findOrFail($id);

        $this->authorize('restore', $staff);

        $staff->restore();

        $this->audit->log('Staff member restored', 'hr', $staff, event: 'restored');

        return back()->with('toast', ['type' => 'success', 'message' => 'Staff member restored successfully.']);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'department_id', 'designation', 'employment_type', 'joining_from', 'joining_to']);

        return Excel::download(new EmployeesExport($filters), 'employees-'.now()->format('Y-m-d').'.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['search', 'status', 'department_id', 'designation', 'employment_type', 'joining_from', 'joining_to']);

        return app(EmployeesPdfExport::class, ['filters' => $filters])->download('employees-'.now()->format('Y-m-d').'.pdf');
    }
}
