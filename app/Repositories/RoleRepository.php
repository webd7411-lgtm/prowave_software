<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepository
{
    public function paginate(array $filters = [], ?int $perPage = 15)
    {
        $query = Role::query()
            ->with('permissions')
            ->orderBy('name');

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function all(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }

    public function options(): array
    {
        return $this->all()->pluck('name', 'name')->all();
    }

    public function findByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }

    public function find(int $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    public function create(string $name, array $permissionNames = []): Role
    {
        $role = Role::create(['name' => $name, 'guard_name' => 'web']);

        if (! empty($permissionNames)) {
            $role->syncPermissions(Permission::whereIn('name', $permissionNames)->get());
        }

        return $role;
    }

    public function update(Role $role, string $name, array $permissionNames = []): Role
    {
        $role->update(['name' => $name]);

        $role->syncPermissions(Permission::whereIn('name', $permissionNames)->get());

        return $role->fresh();
    }

    public function delete(Role $role): bool
    {
        if (in_array($role->name, ['Super Admin', 'Administrator'], true)) {
            return false;
        }

        if ($role->users()->count() > 0) {
            return false;
        }

        $role->delete();

        return true;
    }
}
