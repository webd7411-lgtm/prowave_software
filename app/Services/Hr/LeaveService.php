<?php

namespace App\Services\Hr;

use App\Enums\LeaveStatus;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;

class LeaveService
{
    /**
     * Calculate number of leave days between two dates (inclusive).
     */
    public function calculateDays(string $startDate, string $endDate): float
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($end->lt($start)) {
            return 0;
        }

        return $start->diffInDays($end) + 1;
    }

    /**
     * Create a leave request and validate balance.
     */
    public function submit(User $employee, array $data): array
    {
        $days = $this->calculateDays($data['start_date'], $data['end_date']);

        if ($days <= 0) {
            return ['success' => false, 'message' => 'Invalid date range.', 'leave' => null];
        }

        $balance = $this->balanceFor($employee, $data['leave_type_id']);

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days' => $days,
            'reason' => $data['reason'] ?? null,
            'status' => LeaveStatus::Pending->value,
            'created_by' => auth()->id(),
        ]);

        return ['success' => true, 'message' => 'Leave request submitted.', 'leave' => $leave, 'balance' => $balance];
    }

    /**
     * Approve a leave request.
     */
    public function approve(LeaveRequest $leave, ?string $remarks = null): LeaveRequest
    {
        $leave->update([
            'status' => LeaveStatus::Approved->value,
            'remarks' => $remarks ?: $leave->remarks,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->consumeBalance($leave);

        return $leave->fresh();
    }

    /**
     * Reject a leave request.
     */
    public function reject(LeaveRequest $leave, ?string $remarks = null): LeaveRequest
    {
        $leave->update([
            'status' => LeaveStatus::Rejected->value,
            'remarks' => $remarks ?: $leave->remarks,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return $leave->fresh();
    }

    /**
     * Get the balance row for an employee + leave type this year.
     */
    public function balanceFor(User $employee, int $leaveTypeId, ?int $year = null): ?EmployeeLeaveBalance
    {
        $year = $year ?? now()->year;

        $balance = EmployeeLeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (! $balance) {
            $leaveType = LeaveType::find($leaveTypeId);
            $balance = EmployeeLeaveBalance::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
                'allocated' => $leaveType?->days_per_year ?? 0,
                'used' => 0,
                'created_by' => auth()->id(),
            ]);
        }

        return $balance;
    }

    protected function consumeBalance(LeaveRequest $leave): void
    {
        $balance = $this->balanceFor($leave->employee, $leave->leave_type_id, $leave->start_date->year);

        $balance->update([
            'used' => $balance->used + $leave->days,
            'updated_by' => auth()->id(),
        ]);
    }

    public function summaryFor(User $employee, ?int $year = null): array
    {
        $year = $year ?? now()->year;

        $leaveTypes = LeaveType::active()->get();

        $rows = [];

        foreach ($leaveTypes as $type) {
            $balance = $this->balanceFor($employee, $type->id, $year);
            $rows[] = [
                'type' => $type->name,
                'allocated' => $balance?->allocated ?? 0,
                'used' => $balance?->used ?? 0,
                'remaining' => $balance ? $balance->remaining : ($type->days_per_year ?? 0),
            ];
        }

        return $rows;
    }
}
