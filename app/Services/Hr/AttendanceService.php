<?php

namespace App\Services\Hr;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Check in an employee for today.
     */
    public function checkIn(User $employee, ?string $remarks = null, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $date = $now->toDateString();

        $existing = Attendance::where('employee_id', $employee->id)->whereDate('date', $date)->first();

        if ($existing) {
            return ['success' => false, 'message' => 'You already checked in today.', 'attendance' => $existing];
        }

        $shift = $employee->shift;
        $checkInTime = $now->format('H:i:s');

        [$lateMinutes, $status] = $this->evaluateCheckIn($shift, $checkInTime);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'check_in' => $checkInTime,
            'late_minutes' => $lateMinutes,
            'status' => $status->value,
            'break_minutes' => $shift?->break_minutes ?? 0,
            'shift_id' => $shift?->id,
            'remarks' => $remarks,
            'created_by' => auth()->id(),
        ]);

        return ['success' => true, 'message' => 'Checked in successfully.', 'attendance' => $attendance];
    }

    /**
     * Check out an employee (finalize today's record).
     */
    public function checkOut(User $employee, ?string $remarks = null, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $date = $now->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)->whereDate('date', $date)->first();

        if (! $attendance) {
            return ['success' => false, 'message' => 'You have not checked in today yet.', 'attendance' => null];
        }

        if ($attendance->check_out) {
            return ['success' => false, 'message' => 'You already checked out today.', 'attendance' => $attendance];
        }

        $checkOutTime = $now->format('H:i:s');
        $shift = $attendance->shift_id ? $attendance->shift : $employee->shift;

        $checkIn = Carbon::createFromFormat('H:i:s', $attendance->check_in);
        $checkOut = Carbon::createFromFormat('H:i:s', $checkOutTime);

        $totalWorkMinutes = max(0, (int) $checkIn->diffInMinutes($checkOut) - (int) ($shift?->break_minutes ?? 0));
        [$earlyMinutes, $overtimeMinutes] = $this->evaluateCheckOut($shift, $checkOutTime, $totalWorkMinutes);

        $attendance->update([
            'check_out' => $checkOutTime,
            'total_work_minutes' => $totalWorkMinutes,
            'early_minutes' => $earlyMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'status' => $this->finalStatus($attendance->status, $attendance->late_minutes)->value,
            'remarks' => $remarks ?: $attendance->remarks,
            'updated_by' => auth()->id(),
        ]);

        return ['success' => true, 'message' => 'Checked out successfully.', 'attendance' => $attendance->fresh()];
    }

    /**
     * Evaluate late minutes based on shift + grace period.
     */
    protected function evaluateCheckIn(?Shift $shift, string $checkInTime): array
    {
        $lateMinutes = 0;
        $status = AttendanceStatus::Present;

        if ($shift) {
            $start = Carbon::createFromFormat('H:i:s', $shift->start_time);
            $checkIn = Carbon::createFromFormat('H:i:s', $checkInTime);
            $lateMinutes = max(0, (int) $start->diffInMinutes($checkIn, false));

            if ($lateMinutes > 0) {
                $grace = (int) $shift->grace_period;
                $lateMinutes = max(0, $lateMinutes - $grace);
            }

            if ($lateMinutes > 0) {
                $status = AttendanceStatus::Late;
            }
        }

        return [$lateMinutes, $status];
    }

    /**
     * Evaluate early leave + overtime based on shift end.
     */
    protected function evaluateCheckOut(?Shift $shift, string $checkOutTime, int $totalWorkMinutes): array
    {
        $early = 0;
        $overtime = 0;

        if ($shift) {
            $end = Carbon::createFromFormat('H:i:s', $shift->end_time);
            $checkOut = Carbon::createFromFormat('H:i:s', $checkOutTime);

            if ($checkOut->lt($end)) {
                $early = max(0, (int) $checkOut->diffInMinutes($end));
            } else {
                $overtime = max(0, (int) $end->diffInMinutes($checkOut));
            }
        }

        return [$early, $overtime];
    }

    protected function finalStatus(AttendanceStatus $current, int $lateMinutes): AttendanceStatus
    {
        if ($lateMinutes > 0) {
            return AttendanceStatus::Late;
        }

        return $current;
    }

    public function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return ($hours > 0 ? $hours.'h ' : '').$mins.'m';
    }

    /**
     * Build daily summary metrics for dashboards.
     */
    public function dailySummary(?string $date = null): array
    {
        $date = $date ?? now()->toDateString();

        $totalStaff = User::whereNotNull('employee_id')->where('employee_status', 'active')->count();

        $records = Attendance::whereDate('date', $date)->get();

        $summary = [
            'present' => $records->whereIn('status', [AttendanceStatus::Present->value, AttendanceStatus::Late->value])->count(),
            'absent' => $records->where('status', AttendanceStatus::Absent->value)->count(),
            'late' => $records->where('status', AttendanceStatus::Late->value)->count(),
            'on_leave' => $records->where('status', AttendanceStatus::OnLeave->value)->count(),
            'half_day' => $records->where('status', AttendanceStatus::HalfDay->value)->count(),
            'total_staff' => $totalStaff,
            'attendance_pct' => $totalStaff > 0 ? round((($records->whereIn('status', [AttendanceStatus::Present->value, AttendanceStatus::Late->value])->count()) / $totalStaff) * 100, 1) : 0,
        ];

        return $summary;
    }

    public function monthlyChart(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $rows = Attendance::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('DATE(date) as d, status, COUNT(*) as total')
            ->groupBy('d', 'status')
            ->get();

        $present = [];
        $absent = [];
        $late = [];

        for ($day = 1; $day <= $end->day; $day++) {
            $dateKey = $start->copy()->day($day)->toDateString();
            $dayRows = $rows->where('d', $dateKey);
            $present[] = $dayRows->whereIn('status', ['present', 'late'])->sum('total');
            $absent[] = $dayRows->where('status', 'absent')->sum('total');
            $late[] = $dayRows->where('status', 'late')->sum('total');
        }

        return [
            'labels' => collect(range(1, $end->day))->map(fn ($d) => (string) $d)->all(),
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
        ];
    }
}
