<?php

namespace App\Http\Controllers\Hr;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use App\Repositories\AttendanceRepository;
use App\Repositories\EmployeeRepository;
use App\Services\AuditService;
use App\Services\Hr\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceRepository $repository,
        protected AttendanceService $service,
        protected EmployeeRepository $employees,
        protected AuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('attendance.view');

        $filters = $request->only(['employee_id', 'department_id', 'status', 'date', 'from', 'to']);

        return view('hr.attendance.index', [
            'attendance' => $this->repository->paginate($filters, (int) $request->integer('per_page', 15) ?: 15),
            'filters' => $filters,
            'employees' => $this->employees->employeesOptions(),
            'departments' => Department::query()->active()->orderBy('name')->pluck('name', 'id')->all(),
            'statuses' => AttendanceStatus::options(),
            'summary' => $this->service->dailySummary($request->input('date')),
        ]);
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $employee = User::findOrFail($request->integer('employee_id'));

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => $request->date('date'),
            'status' => $request->input('status', AttendanceStatus::Present->value),
            'check_in' => $request->input('check_in'),
            'check_out' => $request->input('check_out'),
            'remarks' => $request->input('remarks'),
            'shift_id' => $employee->shift_id,
            'created_by' => auth()->id(),
        ]);

        $this->audit->log('Attendance recorded', 'hr', $attendance, event: 'created');

        return back()->with('toast', ['type' => 'success', 'message' => 'Attendance record added successfully.']);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $this->authorize('checkInOut', Attendance::class);

        $result = $this->service->checkIn(auth()->user());

        return back()->with('toast', [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ]);
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $this->authorize('checkInOut', Attendance::class);

        $result = $this->service->checkOut(auth()->user());

        return back()->with('toast', [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ]);
    }

    public function dashboard(Request $request): View
    {
        $date = $request->input('date', now()->toDateString());

        $summary = $this->service->dailySummary($date);

        [$year, $month] = [(int) $date[0].$date[1].$date[2].$date[3], (int) explode('-', $date)[1]];

        $chart = $this->service->monthlyChart($year, $month);

        $dailyList = Attendance::query()
            ->with(['employee:id,name,employee_id,avatar,department_id'])
            ->whereDate('date', $date)
            ->when($request->filled('department_id'), fn ($q) => $q->whereHas('employee', fn ($q2) => $q2->where('department_id', $request->integer('department_id'))))
            ->paginate(15)
            ->withQueryString();

        $employees = $this->employees->employeesOptions();
        $departments = Department::query()->active()->orderBy('name')->pluck('name', 'id')->all();

        return view('hr.attendance.dashboard', compact('summary', 'chart', 'dailyList', 'date', 'employees', 'departments'));
    }
}
