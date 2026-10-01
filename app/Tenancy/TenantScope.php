<?php

namespace App\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts every query on a tenant-owned model to the current tenant.
 *
 * Fails closed: without a tenant context (and outside an explicit bypass)
 * the query throws instead of returning every tenant's rows.
 */
final class TenantScope implements Scope
{
    /**
     * @param  Builder<Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if ($context->isBypassing()) {
            return;
        }

        if (! $context->has()) {
            throw MissingTenantContext::forModel($model);
        }

        $builder->where($model->qualifyColumn('tenant_id'), $context->id());
    }
}
