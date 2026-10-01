<?php

namespace App\Models;

use App\Enums\IntervalUnit;
use App\Enums\PlanStatus;
use App\Enums\RenewalMode;
use App\Tenancy\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Something the business sells repeatedly: a price plus a billing interval.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string|null $description
 * @property int $price_minor
 * @property string $currency
 * @property IntervalUnit $interval_unit
 * @property int $interval_count
 * @property RenewalMode $renewal_mode
 * @property int|null $term_periods
 * @property PlanStatus $status
 * @property CarbonImmutable|null $archived_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'description', 'price_minor', 'interval_unit', 'interval_count', 'renewal_mode', 'term_periods'])]
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory, BelongsToTenant;

    public function isActive(): bool
    {
        return $this->status === PlanStatus::Active;
    }

    /**
     * Once a plan has subscriptions its interval, renewal mode and term are
     * frozen (existing subscriptions keep their own snapshot either way).
     */
    public function hasSubscriptions(): bool
    {
        return $this->subscriptions()->exists();
    }

    public function intervalLabel(): string
    {
        return $this->interval_unit->describe($this->interval_count);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
            'interval_unit' => IntervalUnit::class,
            'interval_count' => 'integer',
            'renewal_mode' => RenewalMode::class,
            'term_periods' => 'integer',
            'status' => PlanStatus::class,
            'archived_at' => 'immutable_datetime',
        ];
    }
}
