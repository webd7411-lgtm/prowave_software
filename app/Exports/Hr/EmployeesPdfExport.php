<?php

namespace App\Exports\Hr;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeesPdfExport
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function download(string $fileName = 'employees.pdf')
    {
        $employees = User::query()
            ->whereNotNull('employee_id')
            ->with(['department', 'shift'])
            ->when(! empty($this->filters['search']), fn ($q) => $q->where('name', 'like', '%'.$this->filters['search'].'%'))
            ->when(! empty($this->filters['status']), fn ($q) => $q->where('employee_status', $this->filters['status']))
            ->when(! empty($this->filters['department_id']), fn ($q) => $q->where('department_id', $this->filters['department_id']))
            ->when(! empty($this->filters['joining_from']), fn ($q) => $q->whereDate('joining_date', '>=', $this->filters['joining_from']))
            ->when(! empty($this->filters['joining_to']), fn ($q) => $q->whereDate('joining_date', '<=', $this->filters['joining_to']))
            ->orderBy('name')
            ->get();

        $summary = [
            'total' => $employees->count(),
            'active' => $employees->where('employee_status', \App\Enums\EmployeeStatus::Active)->count(),
            'on_leave' => $employees->where('employee_status', \App\Enums\EmployeeStatus::OnLeave)->count(),
            'terminated' => $employees->where('employee_status', \App\Enums\EmployeeStatus::Terminated)->count(),
        ];

        $pdf = Pdf::loadView('exports.hr.employees-pdf', compact('employees', 'summary'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }
}
