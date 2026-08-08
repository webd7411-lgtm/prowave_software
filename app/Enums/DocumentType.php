<?php

namespace App\Enums;

enum DocumentType: string
{
    case CNIC = 'cnic';
    case Contract = 'contract';
    case JoiningLetter = 'joining_letter';
    case ExperienceLetter = 'experience_letter';
    case Certificate = 'certificate';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CNIC => 'CNIC',
            self::Contract => 'Contract',
            self::JoiningLetter => 'Joining Letter',
            self::ExperienceLetter => 'Experience Letter',
            self::Certificate => 'Certificate',
            self::Other => 'Other',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}
