<?php

namespace App\Enums;

enum PeriodSource: string
{
    case Start = 'start';
    case Renewal = 'renewal';
    case Import = 'import';
    case Adjustment = 'adjustment';

    /**
     * Billable periods count towards a plan's fixed term.
     */
    public function isBillable(): bool
    {
        return $this !== self::Adjustment;
    }
}
