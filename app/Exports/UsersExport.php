<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromView, ShouldAutoSize, WithColumnFormatting, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $users = $this->query()->get();

        return view('exports.users', compact('users'));
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
            'I' => 'dd-mm-yyyy',
        ];
    }

    protected function query()
    {
        return User::query()
            ->with(['roles', 'department'])
            ->when(! empty($this->filters['search']), fn ($q) => $q->search($this->filters['search']))
            ->when(! empty($this->filters['status']), fn ($q) => $q->where('status', $this->filters['status']))
            ->when(! empty($this->filters['department_id']), fn ($q) => $q->where('department_id', $this->filters['department_id']))
            ->when(! empty($this->filters['role']), fn ($q) => $q->role($this->filters['role']))
            ->latest();
    }
}
