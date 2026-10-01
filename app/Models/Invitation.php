<?php

namespace App\Models;

use App\Enums\MembershipRole;
use App\Tenancy\BelongsToTenant;
use App\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An invitation for someone to join a business. Only a SHA-256 hash of the
 * token is stored; the plain token exists only in the emailed link.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $email
 * @property MembershipRole $role
 * @property string $token_hash
 * @property int $invited_by
 * @property CarbonImmutable $expires_at
 * @property CarbonImmutable|null $accepted_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Tenant $tenant
 * @property-read User $inviter
 */
class Invitation extends Model
{
    /** @use HasFactory<InvitationFactory> */
    use BelongsToTenant, HasFactory;

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Find a pending invitation by its plain token, across all tenants.
     * Invitation links are opened before any tenant context exists.
     */
    public static function findPendingByToken(string $token): ?self
    {
        return app(TenantContext::class)->bypass(
            fn (): ?self => static::query()
                ->with('tenant')
                ->where('token_hash', static::hashToken($token))
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->first(),
        );
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => MembershipRole::class,
            'expires_at' => 'immutable_datetime',
            'accepted_at' => 'immutable_datetime',
        ];
    }
}
