<?php

namespace App\Models;

use App\Enums\Ability;
use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Tenancy\TenantContext;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $last_login_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Active memberships across all businesses. Tenant resolution happens
     * before any tenant context exists, so this deliberately bypasses scoping.
     *
     * @return Collection<int, Membership>
     */
    public function activeMemberships(): Collection
    {
        return app(TenantContext::class)->bypass(
            fn (): Collection => Membership::query()
                ->with('tenant')
                ->where('user_id', $this->id)
                ->where('status', MembershipStatus::Active->value)
                ->orderBy('id')
                ->get(),
        );
    }

    /**
     * The membership the current request acts through, if it belongs to this user.
     */
    public function currentMembership(): ?Membership
    {
        $membership = app(TenantContext::class)->membership();

        return $membership !== null && $membership->user_id === $this->id ? $membership : null;
    }

    public function currentRole(): ?MembershipRole
    {
        return $this->currentMembership()?->role;
    }

    public function hasAbility(Ability $ability): bool
    {
        return $this->currentRole()?->allows($ability) ?? false;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }
}
