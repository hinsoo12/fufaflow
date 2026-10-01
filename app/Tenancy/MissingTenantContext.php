<?php

namespace App\Tenancy;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Thrown when tenant-owned data is touched without a tenant context.
 *
 * This is a programming error (a missing Tenancy::run() or middleware), so
 * it fails closed instead of returning or writing data for every tenant.
 */
final class MissingTenantContext extends RuntimeException
{
    public static function make(): self
    {
        return new self('No tenant context is set.');
    }

    public static function forModel(Model $model): self
    {
        return new self(sprintf(
            'Cannot query or save [%s] without a tenant context.',
            $model::class,
        ));
    }

    public static function mismatch(Model $model): self
    {
        return new self(sprintf(
            'Refusing to save [%s] for a tenant other than the current one.',
            $model::class,
        ));
    }
}
