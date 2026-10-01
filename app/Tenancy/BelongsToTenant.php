<?php

namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Marks a model as tenant-owned.
 *
 * - Every query is scoped to the current tenant (TenantScope, fail-closed).
 * - New records get the current tenant's id; a different id is rejected.
 * - A record can never be moved to another tenant.
 *
 * @mixin Model
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            $context = app(TenantContext::class);
            $tenantId = $model->getAttribute('tenant_id');

            if ($context->has()) {
                if ($tenantId === null) {
                    $model->setAttribute('tenant_id', $context->id());
                } elseif ((int) $tenantId !== $context->id()) {
                    throw MissingTenantContext::mismatch($model);
                }

                return;
            }

            if ($tenantId === null) {
                throw MissingTenantContext::forModel($model);
            }
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty('tenant_id')) {
                throw MissingTenantContext::mismatch($model);
            }
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
