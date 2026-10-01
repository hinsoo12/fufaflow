<?php

namespace App\Models;

use App\Enums\PeriodSource;
use App\Tenancy\BelongsToTenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One covered period of a subscription (the persisted "billing cycle").
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $subscription_id
 * @property int $sequence
 * @property CarbonImmutable $starts_on
 * @property CarbonImmutable $ends_on
 * @property int $plan_id
 * @property int $anchor_day
 * @property int $price_minor
 * @property string $currency
 * @property int|null $invoice_id
 * @property PeriodSource $source
 * @property string|null $note
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property-read Subscription $subscription
 * @property-read Invoice|null $invoice
 */
class SubscriptionPeriod extends Model
{
    use BelongsToTenant;

    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'starts_on' => 'immutable_date',
            'ends_on' => 'immutable_date',
            'plan_id' => 'integer',
            'anchor_day' => 'integer',
            'price_minor' => 'integer',
            'source' => PeriodSource::class,
        ];
    }
}
