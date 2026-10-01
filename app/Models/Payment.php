<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Tenancy\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Money received from a customer. Immutable once recorded: mistakes are
 * corrected by voiding (with a reason) and recording again.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $customer_id
 * @property string $receipt_number
 * @property int $amount_minor
 * @property string $currency
 * @property CarbonImmutable $received_on
 * @property int $payment_method_id
 * @property string|null $reference
 * @property string|null $notes
 * @property PaymentStatus $status
 * @property string $idempotency_key
 * @property int $recorded_by
 * @property CarbonImmutable|null $voided_at
 * @property string|null $void_reason
 * @property int|null $voided_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Customer $customer
 * @property-read PaymentMethod $method
 * @property-read User $recorder
 */
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use BelongsToTenant, HasFactory;

    public function isVoid(): bool
    {
        return $this->status === PaymentStatus::Void;
    }

    /**
     * @param  Builder<Payment>  $query
     */
    public function scopeRecorded(Builder $query): void
    {
        $query->where('status', PaymentStatus::Recorded->value);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * @return HasMany<PaymentAllocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'received_on' => 'immutable_date',
            'status' => PaymentStatus::class,
            'voided_at' => 'immutable_datetime',
        ];
    }
}
