<?php

namespace App\Tenancy;

use App\Models\Membership;
use App\Models\Tenant;
use Closure;

/**
 * Holds the tenant (business) the current request or job acts for.
 *
 * Registered as a scoped singleton, so it is reset for every request and
 * between queued jobs. Tenant-owned models refuse to query or save without
 * it (see TenantScope), which makes forgetting tenant scoping a loud error
 * instead of a silent data leak.
 */
final class TenantContext
{
    private ?Tenant $tenant = null;

    private ?Membership $membership = null;

    private int $bypassDepth = 0;

    public function set(Tenant $tenant, ?Membership $membership = null): void
    {
        $this->tenant = $tenant;
        $this->membership = $membership;
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->membership = null;
    }

    public function has(): bool
    {
        return $this->tenant !== null;
    }

    public function tenant(): Tenant
    {
        return $this->tenant ?? throw MissingTenantContext::make();
    }

    public function tenantOrNull(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): int
    {
        return $this->tenant()->id;
    }

    public function membership(): ?Membership
    {
        return $this->membership;
    }

    public function isBypassing(): bool
    {
        return $this->bypassDepth > 0;
    }

    /**
     * Run a callback as the given tenant, restoring the previous context after.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function run(Tenant $tenant, Closure $callback, ?Membership $membership = null): mixed
    {
        [$previousTenant, $previousMembership] = [$this->tenant, $this->membership];

        $this->set($tenant, $membership);

        try {
            return $callback();
        } finally {
            $this->tenant = $previousTenant;
            $this->membership = $previousMembership;
        }
    }

    /**
     * Run a callback with tenant scoping switched off.
     *
     * Only for platform-level code (tenant resolution, invitations, platform
     * administration, commands that loop over tenants). Every call site is
     * deliberate and reviewed; never use this in tenant request handling.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function bypass(Closure $callback): mixed
    {
        $this->bypassDepth++;

        try {
            return $callback();
        } finally {
            $this->bypassDepth--;
        }
    }
}
