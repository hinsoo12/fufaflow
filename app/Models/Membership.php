<?php

namespace App\Models;

use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Tenancy\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user's role in a business.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $user_id
 * @property MembershipRole $role
 * @property MembershipStatus $status
 * @property int|null $invited_by
 * @property CarbonImmutable|null $joined_at
 * @property CarbonImmutable|null $deactivated_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Tenant $tenant
 * @property-read User $user
 */
class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use BelongsToTenant, HasFactory;

    public function isActive(): bool
    {
        return $this->status === MembershipStatus::Active;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => MembershipRole::class,
            'status' => MembershipStatus::class,
            'joined_at' => 'immutable_datetime',
            'deactivated_at' => 'immutable_datetime',
        ];
    }
}
