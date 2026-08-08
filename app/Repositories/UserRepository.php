<?php

namespace App\Repositories;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function query(array $filters = []): Builder
    {
        $query = User::query()
            ->with(['roles', 'department'])
            ->withTrashed()
            ->latest();

        $this->applyFilters($query, $filters);

        return $query;
    }

    public function paginate(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)->paginate($perPage)->withQueryString();
    }

    public function find(int $id, bool $withTrashed = false): ?User
    {
        $query = User::query()->with(['roles', 'department', 'reportingManager', 'creator', 'updater', 'deleter']);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function managers(): array
    {
        return User::query()
            ->active()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (! empty($filters['trashed'])) {
            $query->onlyTrashed();
        }
    }

    public function create(array $data): User
    {
        $data['created_by'] = $data['created_by'] ?? auth()->id();
        $data['status'] = $data['status'] ?? UserStatus::Active->value;

        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $data['updated_by'] = auth()->id();

        $user->update($data);

        return $user->fresh();
    }

    public function changeStatus(User $user, UserStatus $status): User
    {
        return $this->update($user, [
            'status' => $status->value,
            'is_active' => $status === UserStatus::Active,
        ]);
    }

    public function delete(User $user): bool
    {
        $user->deleted_by = auth()->id();
        $user->save();

        return $user->delete();
    }

    public function restore(User $user): bool
    {
        return $user->restore();
    }

    public function forceDelete(User $user): bool
    {
        return $user->forceDelete();
    }
}
