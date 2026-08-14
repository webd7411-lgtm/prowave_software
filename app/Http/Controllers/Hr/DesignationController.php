<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreDesignationRequest;
use App\Http\Requests\Hr\UpdateDesignationRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignationController extends Controller
{
    public function __construct(protected AuditService $audit) {}

    public function index(Request $request): View
    {
        $this->authorize('designations.view');

        $query = Designation::query()
            ->with(['department'])
            ->withCount('users')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->integer('department_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->boolean('status')));

        return view('hr.designations.index', [
            'designations' => $query->orderBy('name')->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'department_id', 'status']),
            'departments' => Department::query()->active()->orderBy('name')->pluck('name', 'id')->all(),
        ]);
    }

    public function create(): View
    {
        return view('hr.designations.create', [
            'departments' => Department::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreDesignationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        $designation = Designation::create($data);

        $this->audit->log('Designation created', 'hr', $designation, event: 'created');

        return redirect()
            ->route('hr.designations.index')
            ->with('toast', ['type' => 'success', 'message' => 'Designation created successfully.']);
    }

    public function edit(Designation $designation): View
    {
        return view('hr.designations.edit', [
            'designation' => $designation,
            'departments' => Department::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateDesignationRequest $request, Designation $designation): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $designation->update($data);

        $this->audit->log('Designation updated', 'hr', $designation, event: 'updated');

        return redirect()
            ->route('hr.designations.index')
            ->with('toast', ['type' => 'success', 'message' => 'Designation updated successfully.']);
    }

    public function destroy(Designation $designation): RedirectResponse
    {
        if ($designation->users()->count() > 0) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Cannot delete a designation that has employees assigned.']);
        }

        $designation->delete();

        $this->audit->log('Designation deleted', 'hr', $designation, event: 'deleted');

        return back()->with('toast', ['type' => 'success', 'message' => 'Designation deleted successfully.']);
    }
}
