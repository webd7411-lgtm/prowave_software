<?php

namespace App\Policies;

use App\Models\EmployeeDocument;
use App\Models\User;

class EmployeeDocumentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('employee_documents.view');
    }

    public function view(User $user, EmployeeDocument $document): bool
    {
        return $user->can('employee_documents.view') || $user->id === $document->employee_id;
    }

    public function upload(User $user): bool
    {
        return $user->can('employee_documents.upload');
    }

    public function delete(User $user, EmployeeDocument $document): bool
    {
        return $user->can('employee_documents.delete');
    }
}
