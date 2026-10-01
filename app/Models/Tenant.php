<?php

namespace App\Models;

use App\Enums\BusinessType;
use App\Enums\TenantStatus;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A business using FufaFlow. Owns all of its business data.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property BusinessType|null $business_type
 * @property string $country
 * @property string $currency
 * @property string $timezone
 * @property string $locale
 * @property TenantStatus $status
 * @property CarbonImmutable|null $trial_ends_on
 * @property CarbonImmutable|null $paid_until
 * @property array<string, mixed>|null $settings
 * @property CarbonImmutable|null $suspended_at
 * @property string|null $suspension_reason
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'business_type', 'country', 'currency', 'timezone', 'locale'])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    /**
     * Past-due days before an unpaid account is considered lapsed (D4 default).
     */
    public const PAST_DUE_GRACE_DAYS = 7;

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant): void {
            if (empty($tenant->slug)) {
                $tenant->slug = static::uniqueSlug($tenant->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'business';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return HasMany<Membership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    public function graceDays(): int
    {
        return (int) $this->setting('grace_days', config('fufaflow.defaults.grace_days'));
    }

    public function renewalWindowDays(): int
    {
        return (int) $this->setting('renewal_window_days', config('fufaflow.defaults.renewal_window_days'));
    }

    /**
     * What happens when an expired subscription is renewed: "restart" starts the
     * new period on the renewal date; "continue" starts it the day after the
     * last covered day.
     */
    public function renewalAfterExpiry(): string
    {
        return $this->setting('renewal_after_expiry', config('fufaflow.defaults.renewal_after_expiry')) === 'continue'
            ? 'continue'
            : 'restart';
    }

    public function currencyExponent(): int
    {
        return Money::exponent($this->currency);
    }

    /**
     * Derived account phase: trialing, paid, past_due, lapsed, suspended, cancelled.
     */
    public function accountPhase(CarbonImmutable $today): string
    {
        if ($this->status === TenantStatus::Cancelled) {
            return 'cancelled';
        }

        if ($this->status === TenantStatus::Suspended) {
            return 'suspended';
        }

        if ($this->paid_until !== null) {
            if ($today->lte($this->paid_until)) {
                return 'paid';
            }

            return $today->lte($this->paid_until->addDays(self::PAST_DUE_GRACE_DAYS)) ? 'past_due' : 'lapsed';
        }

        return $this->trial_ends_on !== null && $today->lte($this->trial_ends_on) ? 'trialing' : 'lapsed';
    }

    /**
     * Whether the business may change data. Phase 1 only enforces the stored
     * decisions (suspended, cancelled); trial and payment phases are shown but
     * not enforced until the platform admin console can extend them.
     */
    public function canWrite(): bool
    {
        return $this->status === TenantStatus::Active;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'business_type' => BusinessType::class,
            'status' => TenantStatus::class,
            'trial_ends_on' => 'immutable_date',
            'paid_until' => 'immutable_date',
            'settings' => 'array',
            'suspended_at' => 'immutable_datetime',
        ];
    }
}
