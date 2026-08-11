<?php

namespace App\Http\Controllers\Hr;

use App\Enums\DailyUpdateStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\ReviewDailyUpdateRequest;
use App\Http\Requests\Hr\StoreDailyUpdateRequest;
use App\Models\DailyWorkUpdate;
use App\Models\Department;
use App\Repositories\DailyUpdateRepository;
use App\Repositories\EmployeeRepository;
use App\Services\AuditService;
use App\Services\Hr\DailyUpdateService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyUpdateController extends Controller
{
    public function __construct(
        protected DailyUpdateRepository $repository,
        protected DailyUpdateService $service,
        protected EmployeeRepository $employees,
        protected AuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['employee_id', 'department_id', 'status', 'from', 'to', 'my']);

        return view('hr.updates.index', [
            'updates' => $this->repository->paginate($filters, (int) $request->integer('per_page', 15) ?: 15),
            'filters' => $filters,
            'employees' => $this->employees->employeesOptions(),
            'departments' => Department::query()->active()->orderBy('name')->pluck('name', 'id')->all(),
            'statuses' => DailyUpdateStatus::options(),
        ]);
    }

    public function create(): View
    {
        return view('hr.updates.create');
    }

    public function store(StoreDailyUpdateRequest $request): RedirectResponse
    {
        $update = $this->service->submit(auth()->id(), $request->validated());

        $this->audit->log('Daily update submitted', 'hr', $update, event: 'created');

        $submitter = auth()->user();
        $manager = $submitter->reportingManager;

        $managerUsers = $manager ? collect([$manager]) : collect();
        app(NotificationService::class)->sendMany(
            $managerUsers,
            'Daily update submitted',
            $submitter->name.' submitted their work update for '.$update->work_date->format('M d, Y').'.',
            ['category' => 'update', 'url' => route('hr.updates.index'), 'color' => 'info']
        );

        return redirect()
            ->route('hr.updates.index')
            ->with('toast', ['type' => 'success', 'message' => 'Daily update submitted successfully.']);
    }

    public function show(DailyWorkUpdate $update): View
    {
        $this->authorize('view', $update);

        $update->load(['employee:id,name,employee_id,avatar,department_id', 'reviewer:id,name']);

        return view('hr.updates.show', compact('update'));
    }

    public function review(ReviewDailyUpdateRequest $request, DailyWorkUpdate $update): RedirectResponse
    {
        $this->authorize('review', $update);

        $this->service->review($update, $request->string('status'), $request->input('review_remarks'));

        $this->audit->log('Daily update reviewed', 'hr', $update, event: 'updated');

        return back()->with('toast', ['type' => 'success', 'message' => 'Daily update reviewed successfully.']);
    }
}
