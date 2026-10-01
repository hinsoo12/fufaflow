<?php

namespace App\Models;

use App\Enums\InvoiceKind;
use App\Enums\InvoiceStatus;
use App\Tenancy\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * What a customer owes. Stored status is issued/paid/void; "partially paid"
 * and "overdue" are derived from the balance and the tenant's today.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $customer_id
 * @property int|null $subscription_id
 * @property string $number
 * @property InvoiceKind $kind
 * @property InvoiceStatus $status
 * @property CarbonImmutable $issued_on
 * @property CarbonImmutable $due_on
 * @property int $subtotal_minor
 * @property int $discount_minor
 * @property int $tax_minor
 * @property int $total_minor
 * @property int $amount_paid_minor
 * @property int $balance_minor
 * @property string $currency
 * @property CarbonImmutable|null $voided_at
 * @property string|null $void_reason
 * @property int|null $voided_by
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Customer $customer
 * @property-read Subscription|null $subscription
 */
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use BelongsToTenant, HasFactory;

    public function isOpen(): bool
    {
        return $this->status === InvoiceStatus::Issued;
    }

    public function isOverdue(CarbonImmutable $today): bool
    {
        return $this->isOpen() && $this->due_on->lt($today);
    }

    /**
     * issued | partially_paid | overdue | paid | void
     */
    public function displayStatus(CarbonImmutable $today): string
    {
        return match (true) {
            $this->status === InvoiceStatus::Void => 'void',
            $this->status === InvoiceStatus::Paid => 'paid',
            $this->due_on->lt($today) => 'overdue',
            $this->amount_paid_minor > 0 => 'partially_paid',
            default => 'issued',
        };
    }

    /**
     * Recompute paid amount, balance and status from the live allocations.
     * Always recompute from the source rows; never increment.
     */
    public function recalculate(): void
    {
        $paid = (int) $this->allocations()->whereNull('voided_at')->sum('amount_minor');

        $this->amount_paid_minor = $paid;
        $this->balance_minor = $this->total_minor - $paid;

        if ($this->status !== InvoiceStatus::Void) {
            $this->status = $this->balance_minor <= 0 ? InvoiceStatus::Paid : InvoiceStatus::Issued;
        }

        $this->save();
    }

    /**
     * @param  Builder<Invoice>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', InvoiceStatus::Issued->value);
    }

    /**
     * @param  Builder<Invoice>  $query
     */
    public function scopeOverdue(Builder $query, CarbonImmutable $today): void
    {
        $query->open()->where('due_on', '<', $today->toDateString());
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * @return HasMany<PaymentAllocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * @return HasMany<SubscriptionPeriod, $this>
     */
    public function periods(): HasMany
    {
        return $this->hasMany(SubscriptionPeriod::class)->orderBy('sequence');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => InvoiceKind::class,
            'status' => InvoiceStatus::class,
            'issued_on' => 'immutable_date',
            'due_on' => 'immutable_date',
            'subtotal_minor' => 'integer',
            'discount_minor' => 'integer',
            'tax_minor' => 'integer',
            'total_minor' => 'integer',
            'amount_paid_minor' => 'integer',
            'balance_minor' => 'integer',
            'voided_at' => 'immutable_datetime',
        ];
    }
}
