<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case HalfDay = 'half_day';
    case OnLeave = 'on_leave';
    case Late = 'late';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::HalfDay => 'Half Day',
            self::OnLeave => 'On Leave',
            self::Late => 'Late',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Absent => 'danger',
            self::HalfDay => 'warning',
            self::OnLeave => 'info',
            self::Late => 'warning',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
