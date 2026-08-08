<?php

namespace App\Enums;

enum DailyUpdateStatus: string
{
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case NeedsRevision = 'needs_revision';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Reviewed => 'Reviewed',
            self::NeedsRevision => 'Needs Revision',
            self::Approved => 'Approved',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Submitted => 'info',
            self::Reviewed => 'primary',
            self::NeedsRevision => 'warning',
            self::Approved => 'success',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
