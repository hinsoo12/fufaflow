<?php

namespace App\Models;

use App\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only business activity log.
 *
 * Not using BelongsToTenant because platform-level events (logins, account
 * changes) have no tenant. Tenant screens must read through forCurrentTenant().
 *
 * @property int $id
 * @property int|null $tenant_id
 * @property string $actor_type
 * @property int|null $actor_id
 * @property string $action
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property array<string, mixed>|null $changes
 * @property array<string, mixed>|null $metadata
 * @property string|null $ip
 * @property CarbonImmutable|null $created_at
 * @property-read User|null $actor
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    /**
     * @param  Builder<AuditLog>  $query
     */
    public function scopeForCurrentTenant(Builder $query): void
    {
        $query->where('tenant_id', app(TenantContext::class)->id());
    }

    /**
     * @param  Builder<AuditLog>  $query
     */
    public function scopeForSubject(Builder $query, Model $subject): void
    {
        $query->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }
}
