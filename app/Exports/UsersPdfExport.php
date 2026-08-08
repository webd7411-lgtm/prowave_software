<?php

namespace App\Exports;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class UsersPdfExport
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function download(string $fileName = 'users.pdf')
    {
        $users = User::query()
            ->with(['roles', 'department'])
            ->when(! empty($this->filters['search']), fn ($q) => $q->search($this->filters['search']))
            ->when(! empty($this->filters['status']), fn ($q) => $q->where('status', $this->filters['status']))
            ->when(! empty($this->filters['department_id']), fn ($q) => $q->where('department_id', $this->filters['department_id']))
            ->when(! empty($this->filters['role']), fn ($q) => $q->role($this->filters['role']))
            ->latest()
            ->get();

        $summary = [
            'total' => $users->count(),
            'active' => $users->where('status', 'active')->count(),
            'inactive' => $users->where('status', 'inactive')->count(),
            'suspended' => $users->where('status', 'suspended')->count(),
        ];

        $pdf = Pdf::loadView('exports.users-pdf', compact('users', 'summary'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }
}
