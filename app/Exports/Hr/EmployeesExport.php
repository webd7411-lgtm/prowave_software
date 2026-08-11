<?php

namespace App\Exports\Hr;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromView, ShouldAutoSize, WithColumnFormatting, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $employees = $this->query()->get();

        return view('exports.hr.employees', compact('employees'));
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '2563EB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => 'dd-mm-yyyy',
        ];
    }

    protected function query(): Builder
    {
        return User::query()
            ->whereNotNull('employee_id')
            ->with(['department', 'shift'])
            ->when(! empty($this->filters['search']), fn ($q) => $q->where('name', 'like', '%'.$this->filters['search'].'%'))
            ->when(! empty($this->filters['status']), fn ($q) => $q->where('employee_status', $this->filters['status']))
            ->when(! empty($this->filters['department_id']), fn ($q) => $q->where('department_id', $this->filters['department_id']))
            ->when(! empty($this->filters['joining_from']), fn ($q) => $q->whereDate('joining_date', '>=', $this->filters['joining_from']))
            ->when(! empty($this->filters['joining_to']), fn ($q) => $q->whereDate('joining_date', '<=', $this->filters['joining_to']))
            ->orderBy('name');
    }
}
