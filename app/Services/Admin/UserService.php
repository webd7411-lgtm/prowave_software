<?php

namespace App\Services\Admin;

use App\Enums\UserStatus;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuditService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserService
{
    public function __construct(
        protected UserRepository $repository,
        protected AuditService $audit,
    ) {}

    public function create(array $data, array $roles = []): User
    {
        Arr::forget($data, ['roles']);

        $data['password'] = Hash::make($data['password']);
        $data['username'] = $data['username'] ?? $this->generateUsername($data['name']);
        $data['employee_id'] = $data['employee_id'] ?? $this->generateEmployeeId();
        $data['status'] = ($data['status'] ?? UserStatus::Active->value);
        $data['is_active'] = $data['status'] === UserStatus::Active->value;

        $user = $this->repository->create($data);

        $roleModels = ! empty($roles) ? Role::query()->whereIn('id', $roles)->orWhereIn('name', $roles)->get() : collect();
        $user->syncRoles($roleModels);

        $this->audit->log('User created', 'user', $user, properties: [
            'email' => $user->email,
            'roles' => $roles,
        ], event: 'created');

        return $user;
    }

    public function update(User $user, array $data, array $roles = []): User
    {
        Arr::forget($data, ['roles']);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            Arr::forget($data, ['password']);
        }

        $data['status'] = $data['status'] ?? $user->status->value;
        $data['is_active'] = $data['status'] === UserStatus::Active->value;

        $user = $this->repository->update($user, $data);

        $roleModels = ! empty($roles) ? Role::query()->whereIn('id', $roles)->orWhereIn('name', $roles)->get() : collect();
        $user->syncRoles($roleModels);

        $this->audit->log('User updated', 'user', $user, properties: [
            'email' => $user->email,
            'roles' => $roles,
        ], event: 'updated');

        return $user;
    }

    public function changeStatus(User $user, string $status): User
    {
        $user = $this->repository->changeStatus($user, UserStatus::from($status));

        $this->audit->log('User status changed to '.$status, 'user', $user, event: 'updated');

        return $user;
    }

    public function delete(User $user): void
    {
        $this->repository->delete($user);

        $this->audit->log('User soft-deleted', 'user', $user, event: 'deleted');
    }

    public function restore(User $user): void
    {
        $this->repository->restore($user);

        $this->audit->log('User restored', 'user', $user, event: 'restored');
    }

    public function forceDelete(User $user): void
    {
        $this->repository->forceDelete($user);

        $this->audit->log('User permanently deleted', 'user', $user, event: 'deleted');
    }

    public function bulkStatus(array $ids, string $status): int
    {
        $status = match ($status) {
            'activate' => UserStatus::Active->value,
            'suspend' => UserStatus::Suspended->value,
            'inactive' => UserStatus::Inactive->value,
            default => $status,
        };

        $count = 0;

        foreach ($ids as $id) {
            $user = $this->repository->find($id);

            if ($user && ! $user->isSuperAdmin()) {
                $this->changeStatus($user, $status);
                $count++;
            }
        }

        return $count;
    }

    public function bulkDelete(array $ids): int
    {
        $count = 0;

        foreach ($ids as $id) {
            $user = $this->repository->find($id);

            if ($user && ! $user->isSuperAdmin() && $user->id !== auth()->id()) {
                $this->delete($user);
                $count++;
            }
        }

        return $count;
    }

    protected function generateUsername(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'user';
        $username = $base;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.'_'.$suffix++;
        }

        return $username;
    }

    protected function generateEmployeeId(): string
    {
        $next = User::withTrashed()->count() + 1;

        return 'EMP-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
