<?php

namespace App\Models;

use App\Tenancy\BelongsToTenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * How much of a payment settles which invoice. Voided with the payment,
 * never deleted.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $payment_id
 * @property int $invoice_id
 * @property int $amount_minor
 * @property CarbonImmutable|null $voided_at
 * @property CarbonImmutable|null $created_at
 * @property-read Payment $payment
 * @property-read Invoice $invoice
 */
class PaymentAllocation extends Model
{
    use BelongsToTenant;

    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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
            'amount_minor' => 'integer',
            'voided_at' => 'immutable_datetime',
        ];
    }
}
