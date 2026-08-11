<?php

namespace App\Http\Controllers\Hr;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\ReviewLeaveRequest;
use App\Http\Requests\Hr\StoreLeaveRequest;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Repositories\LeaveRepository;
use App\Services\AuditService;
use App\Services\Hr\LeaveService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function __construct(
        protected LeaveRepository $repository,
        protected LeaveService $service,
        protected EmployeeRepository $employees,
        protected AuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['employee_id', 'department_id', 'leave_type_id', 'status', 'from', 'to']);

        return view('hr.leave.index', [
            'leaves' => $this->repository->paginate($filters, (int) $request->integer('per_page', 15) ?: 15),
            'filters' => $filters,
            'employees' => $this->employees->employeesOptions(),
            'departments' => Department::query()->active()->orderBy('name')->pluck('name', 'id')->all(),
            'leaveTypes' => LeaveType::query()->active()->orderBy('name')->pluck('name', 'id')->all(),
            'statuses' => LeaveStatus::options(),
        ]);
    }

    public function create(): View
    {
        return view('hr.leave.create', [
            'employees' => $this->employees->employeesOptions(),
            'leaveTypes' => LeaveType::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        $employee = User::findOrFail($request->integer('employee_id'));

        $result = $this->service->submit($employee, $request->validated());

        $this->audit->log('Leave request submitted', 'hr', $result['leave'], event: 'created');

        if ($result['success']) {
            $leave = $result['leave'];
            app(NotificationService::class)->sendToPermission(
                'leave.approve',
                'Leave request submitted',
                $employee->name.' requested '.$leave->leaveType?->name.' ('.$leave->start_date->format('M d').' – '.$leave->end_date->format('M d, Y').')',
                ['category' => 'leave', 'url' => route('hr.leave.index'), 'color' => 'warning']
            );
        }

        return redirect()
            ->route('hr.leave.index')
            ->with('toast', ['type' => $result['success'] ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function review(ReviewLeaveRequest $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorize('approve', $leave);

        if ($request->input('status') === 'approved') {
            $this->authorize('approve', $leave);
            $this->service->approve($leave, $request->input('remarks'));
            $message = 'Leave request approved.';
        } else {
            $this->authorize('reject', $leave);
            $this->service->reject($leave, $request->input('remarks'));
            $message = 'Leave request rejected.';
        }

        $this->audit->log('Leave review '.$request->input('status'), 'hr', $leave, event: 'updated');

        $approved = $request->input('status') === 'approved';
        app(NotificationService::class)->send(
            $leave->employee,
            $approved ? 'Leave approved' : 'Leave rejected',
            'Your '.($leave->leaveType?->name ?? 'leave').' request ('.$leave->start_date->format('M d').' – '.$leave->end_date->format('M d, Y').') was '.$request->input('status').'.',
            ['category' => 'leave', 'url' => route('hr.leave.index'), 'color' => $approved ? 'success' : 'danger']
        );

        return back()->with('toast', ['type' => 'success', 'message' => $message]);
    }

    public function dashboard(Request $request): View
    {
        $pending = LeaveRequest::pending()->count();
        $approved = LeaveRequest::where('status', LeaveStatus::Approved->value)->count();
        $rejected = LeaveRequest::where('status', LeaveStatus::Rejected->value)->count();

        $onLeaveToday = LeaveRequest::where('status', LeaveStatus::Approved->value)
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->count();

        $approvedLeaves = LeaveRequest::query()
            ->with(['employee:id,name', 'leaveType:id,name'])
            ->where('status', LeaveStatus::Approved->value)
            ->whereMonth('start_date', now()->month)
            ->get();

        return view('hr.leave.dashboard', [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'onLeaveToday' => $onLeaveToday,
            'approvedLeaves' => $approvedLeaves,
            'recentRequests' => LeaveRequest::query()->with(['employee:id,name,avatar', 'leaveType:id,name'])->latest()->limit(8)->get(),
        ]);
    }
}
