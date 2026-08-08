<?php

namespace App\Services\Admin;

use App\Repositories\RoleRepository;
use App\Services\AuditService;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function __construct(
        protected RoleRepository $repository,
        protected AuditService $audit,
    ) {}

    public function create(string $name, array $permissions = []): Role
    {
        $role = $this->repository->create($name, $permissions);

        $this->audit->log('Role created', 'role', $role, properties: [
            'name' => $role->name,
            'permissions' => $permissions,
        ], event: 'created');

        return $role;
    }

    public function update(Role $role, string $name, array $permissions = []): Role
    {
        $role = $this->repository->update($role, $name, $permissions);

        $this->audit->log('Role updated', 'role', $role, properties: [
            'name' => $role->name,
            'permissions' => $permissions,
        ], event: 'updated');

        return $role;
    }

    public function delete(Role $role): bool
    {
        if ($this->repository->delete($role)) {
            $this->audit->log('Role deleted', 'role', $role, event: 'deleted');

            return true;
        }

        return false;
    }

    public function assignPermissions(Role $role, array $permissions = []): void
    {
        $role->syncPermissions($permissions);

        $this->audit->log('Role permissions updated', 'role', $role, properties: [
            'permissions' => $permissions,
        ], event: 'updated');
    }
}
