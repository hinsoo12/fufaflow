<?php

namespace App\Enums;

/**
 * Stored account decisions. Time-based account phases (trialing, paid,
 * past due) are derived from the tenant's dates and never stored.
 */
enum TenantStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';
}
