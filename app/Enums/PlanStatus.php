<?php

namespace App\Enums;

enum PlanStatus: string
{
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Archived => 'Inactive',
        };
    }
}
