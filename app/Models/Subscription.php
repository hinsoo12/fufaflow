<?php

namespace App\Models;

use App\Billing\SubscriptionPhaseResolver;
use App\Enums\IntervalUnit;
use App\Enums\RenewalMode;
use App\Enums\SubscriptionPhase;
use App\Enums\SubscriptionStatus;
use App\Tenancy\BelongsToTenant;
use App\Tenancy\TenantClock;
use App\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A customer's ongoing relationship with a plan.
 *
 * Plan terms are snapshotted here so plan edits never rewrite an existing
 * customer's agreement. All writes go through the Subscriptions actions.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $customer_id
 * @property int $plan_id
 * @property int $price_minor
 * @property bool $price_overridden
 * @property string $currency
 * @property IntervalUnit $interval_unit
 * @property int $interval_count
 * @property RenewalMode $renewal_mode
 * @property int|null $term_periods
 * @property int $anchor_day
 * @property CarbonImmutable $starts_on
 * @property CarbonImmutable $current_period_ends_on
 * @property int $periods_count
 * @property SubscriptionStatus $status
 * @property CarbonImmutable|null $cancel_on
 * @property CarbonImmutable|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Customer $customer
 * @property-read Plan $plan
 */
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use BelongsToTenant, HasFactory;

    public function isTermComplete(): bool
    {
        return $this->term_periods !== null && $this->periods_count >= $this->term_periods;
    }

    public function phase(?CarbonImmutable $today = null): SubscriptionPhase
    {
        $tenant = app(TenantContext::class)->tenant();

        return app(SubscriptionPhaseResolver::class)->resolve(
            $this,
            $today ?? TenantClock::today($tenant),
            $tenant->graceDays(),
        );
    }

    public function isRenewalDue(?CarbonImmutable $today = null): bool
    {
        $tenant = app(TenantContext::class)->tenant();

        return app(SubscriptionPhaseResolver::class)->isRenewalDue(
            $this,
            $today ?? TenantClock::today($tenant),
            $tenant->graceDays(),
            $tenant->renewalWindowDays(),
        );
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<SubscriptionPeriod, $this>
     */
    public function periods(): HasMany
    {
        return $this->hasMany(SubscriptionPeriod::class)->orderBy('sequence');
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * SQL twin of SubscriptionPhaseResolver::resolve(). Keep the two in step;
     * SubscriptionPhaseConsistencyTest compares them on every rule boundary.
     *
     * @param  Builder<Subscription>  $query
     */
    public function scopeInPhase(Builder $query, SubscriptionPhase $phase, CarbonImmutable $today, int $graceDays): void
    {
        $todayDate = $today->toDateString();
        $graceLimit = $today->subDays($graceDays)->toDateString();

        $query->where(function (Builder $query) use ($phase, $todayDate, $graceLimit): void {
            if ($phase === SubscriptionPhase::Cancelled) {
                $query->where('status', SubscriptionStatus::Cancelled->value)
                    ->orWhere(fn (Builder $query) => $query
                        ->whereNotNull('cancel_on')
                        ->where('cancel_on', '<', $todayDate));

                return;
            }

            // Every other phase excludes cancelled subscriptions.
            $query->where('status', '!=', SubscriptionStatus::Cancelled->value)
                ->where(fn (Builder $query) => $query
                    ->whereNull('cancel_on')
                    ->orWhere('cancel_on', '>=', $todayDate));

            if ($phase === SubscriptionPhase::Paused) {
                $query->where('status', SubscriptionStatus::Paused->value);

                return;
            }

            $query->where('status', '!=', SubscriptionStatus::Paused->value);

            if ($phase === SubscriptionPhase::Scheduled) {
                $query->where('starts_on', '>', $todayDate);

                return;
            }

            $query->where('starts_on', '<=', $todayDate);

            if ($phase === SubscriptionPhase::Completed) {
                $query->whereNotNull('term_periods')
                    ->whereColumn('periods_count', '>=', 'term_periods')
                    ->where('current_period_ends_on', '<', $todayDate);

                return;
            }

            // Not completed.
            $query->where(fn (Builder $query) => $query
                ->whereNull('term_periods')
                ->orWhereColumn('periods_count', '<', 'term_periods')
                ->orWhere('current_period_ends_on', '>=', $todayDate));

            match ($phase) {
                SubscriptionPhase::Expired => $query
                    ->where('renewal_mode', RenewalMode::Manual->value)
                    ->where('current_period_ends_on', '<', $graceLimit),
                SubscriptionPhase::Grace => $query
                    ->where('renewal_mode', RenewalMode::Manual->value)
                    ->where('current_period_ends_on', '<', $todayDate)
                    ->where('current_period_ends_on', '>=', $graceLimit),
                default => $query->where(fn (Builder $query) => $query
                    ->where('renewal_mode', RenewalMode::Automatic->value)
                    ->orWhere('current_period_ends_on', '>=', $todayDate)),
            };
        });
    }

    /**
     * Active subscriptions whose period ends within the renewal window.
     *
     * @param  Builder<Subscription>  $query
     */
    public function scopeRenewalDue(Builder $query, CarbonImmutable $today, int $graceDays, int $windowDays): void
    {
        $query->inPhase(SubscriptionPhase::Active, $today, $graceDays)
            ->whereBetween('current_period_ends_on', [
                $today->toDateString(),
                $today->addDays($windowDays)->toDateString(),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
            'price_overridden' => 'boolean',
            'interval_unit' => IntervalUnit::class,
            'interval_count' => 'integer',
            'renewal_mode' => RenewalMode::class,
            'term_periods' => 'integer',
            'anchor_day' => 'integer',
            'starts_on' => 'immutable_date',
            'current_period_ends_on' => 'immutable_date',
            'periods_count' => 'integer',
            'status' => SubscriptionStatus::class,
            'cancel_on' => 'immutable_date',
            'cancelled_at' => 'immutable_datetime',
        ];
    }
}
