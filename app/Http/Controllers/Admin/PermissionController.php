<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\RoleRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(protected RoleRepository $roles) {}

    public function index(Request $request): View
    {
        $query = Permission::query()->with('roles')->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->string('search').'%');
        }

        if ($request->filled('role')) {
            $role = $this->roles->findByName($request->string('role'));
            $roleIds = $role ? [$role->id] : [];
        } else {
            $roleIds = $this->roles->all()->pluck('id')->all();
        }

        $permissions = $query->get()->groupBy(fn (Permission $permission) => ucfirst(Str::before($permission->name, '.')));

        return view('admin.permissions.index', [
            'permissions' => $permissions,
            'roles' => $this->roles->all(),
            'selectedRole' => $request->string('role'),
            'roleIds' => $roleIds,
            'filters' => $request->only(['search', 'role']),
        ]);
    }
}
