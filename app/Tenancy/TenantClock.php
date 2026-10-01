<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Carbon\CarbonImmutable;

/**
 * The single source of "today" for business rules.
 *
 * Business dates (starts_on, ends_on, due_on, received_on) are calendar dates
 * in the tenant's timezone. They are represented as midnight UTC values so
 * they compare cleanly with DATE columns. Domain code must use this class
 * instead of now()/today() (enforced by an architecture test).
 */
final class TenantClock
{
    public static function today(?Tenant $tenant = null): CarbonImmutable
    {
        $tenant ??= app(TenantContext::class)->tenant();

        $localDate = CarbonImmutable::now($tenant->timezone)->toDateString();

        return CarbonImmutable::parse($localDate, 'UTC')->startOfDay();
    }

    /**
     * Current instant, for technical timestamps (stored in UTC).
     */
    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now('UTC');
    }
}
