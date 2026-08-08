<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Repositories\RoleRepository;
use App\Services\Admin\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct(
        protected RoleRepository $repository,
        protected RoleService $service,
    ) {}

    public function index(Request $request): View
    {
        return view('admin.roles.index', [
            'roles' => $this->repository->paginate($request->only(['search'])),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'permissionGroups' => $this->groupedPermissions(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->service->create($request->string('name'), Permission::whereIn('id', $request->input('permissions', []))->pluck('name')->all());

        return redirect()
            ->route('admin.roles.index')
            ->with('toast', ['type' => 'success', 'message' => 'Role created successfully.']);
    }

    public function edit($id): View
    {
        $role = $this->repository->find((int) $id);

        abort_if(! $role, 404);

        return view('admin.roles.edit', [
            'role' => $role,
            'permissionGroups' => $this->groupedPermissions(),
        ]);
    }

    public function update(UpdateRoleRequest $request, $id): RedirectResponse
    {
        $role = $this->repository->find((int) $id);

        abort_if(! $role, 404);

        $this->authorize('update', $role);

        $this->service->update($role, $request->string('name'), Permission::whereIn('id', $request->input('permissions', []))->pluck('name')->all());

        return redirect()
            ->route('admin.roles.index')
            ->with('toast', ['type' => 'success', 'message' => 'Role updated successfully.']);
    }

    public function destroy($id): RedirectResponse
    {
        $role = $this->repository->find((int) $id);

        abort_if(! $role, 404);

        $this->authorize('delete', $role);

        if (! $this->service->delete($role)) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Cannot delete a role that is assigned to users.']);
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Role deleted successfully.']);
    }

    public function assignPermissions(Request $request, $id): RedirectResponse
    {
        $role = $this->repository->find((int) $id);

        abort_if(! $role, 404);

        $this->authorize('assignPermissions', $role);

        $request->validate(['permissions' => ['nullable', 'array']]);

        $this->service->assignPermissions($role, Permission::whereIn('id', $request->input('permissions', []))->pluck('name')->all());

        return back()->with('toast', ['type' => 'success', 'message' => 'Permissions updated successfully.']);
    }

    protected function groupedPermissions(): Collection
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => ucfirst(Str::before($permission->name, '.')))
            ->map->values();
    }
}
