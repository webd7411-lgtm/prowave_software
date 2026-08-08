<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(protected AuditService $audit) {}

    public function index(Request $request): View
    {
        $query = Department::query()->withCount('users');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('code', 'like', '%'.$request->string('search').'%');
        }

        return view('admin.departments.index', [
            'departments' => $query->orderBy('name')->paginate(15)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateDepartment($request);

        $department = Department::create($data);

        $this->audit->log('Department created', 'department', $department, event: 'created');

        return redirect()
            ->route('admin.departments.index')
            ->with('toast', ['type' => 'success', 'message' => 'Department created successfully.']);
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $this->validateDepartment($request, $department->id);

        $department->update($data);

        $this->audit->log('Department updated', 'department', $department, event: 'updated');

        return redirect()
            ->route('admin.departments.index')
            ->with('toast', ['type' => 'success', 'message' => 'Department updated successfully.']);
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        if ($department->users()->count() > 0) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Cannot delete a department that has users assigned.']);
        }

        $department->delete();

        $this->audit->log('Department deleted', 'department', $department, event: 'deleted');

        return back()->with('toast', ['type' => 'success', 'message' => 'Department deleted successfully.']);
    }

    protected function validateDepartment(Request $request, ?int $ignore = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,'.$ignore],
            'code' => ['required', 'string', 'max:20', 'unique:departments,code,'.$ignore],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
