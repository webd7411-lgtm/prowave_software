<?php

namespace App\Services\Hr;

use App\Enums\DailyUpdateStatus;
use App\Models\DailyWorkUpdate;

class DailyUpdateService
{
    public function submit(int $employeeId, array $data): DailyWorkUpdate
    {
        return DailyWorkUpdate::create([
            'employee_id' => $employeeId,
            'date' => $data['date'] ?? now()->toDateString(),
            'tasks_completed' => $data['tasks_completed'] ?? null,
            'work_description' => $data['work_description'] ?? null,
            'problems' => $data['problems'] ?? null,
            'hours_worked' => $data['hours_worked'] ?? null,
            'tomorrow_plan' => $data['tomorrow_plan'] ?? null,
            'additional_notes' => $data['additional_notes'] ?? null,
            'status' => DailyUpdateStatus::Submitted->value,
            'created_by' => auth()->id(),
        ]);
    }

    public function review(DailyWorkUpdate $update, string $status, ?string $remarks = null): DailyWorkUpdate
    {
        $update->update([
            'status' => $status,
            'review_remarks' => $remarks,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        return $update->fresh();
    }
}
