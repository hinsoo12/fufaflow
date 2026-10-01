<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Tenancy\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Someone who buys from the business: a person or an organisation.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $number
 * @property string $name
 * @property string|null $phone
 * @property string|null $phone_e164
 * @property string|null $email
 * @property string|null $address
 * @property CarbonImmutable $joined_on
 * @property bool $messages_opt_out
 * @property CustomerStatus $status
 * @property CarbonImmutable|null $archived_at
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'phone', 'email', 'address', 'joined_on', 'messages_opt_out'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToTenant, HasFactory;

    public function isArchived(): bool
    {
        return $this->status === CustomerStatus::Archived;
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Search by name, customer number, email or phone (any common format).
     *
     * @param  Builder<Customer>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term): void {
            $like = '%'.$term.'%';

            $query->where('name', 'like', $like)
                ->orWhere('number', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like);

            $digits = (string) preg_replace('/\D+/', '', $term);

            if (strlen($digits) >= 4) {
                // Match the national number regardless of how it was typed (09…, +2519…).
                $query->orWhere('phone_e164', 'like', '%'.ltrim($digits, '0').'%');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'joined_on' => 'immutable_date',
            'messages_opt_out' => 'boolean',
            'status' => CustomerStatus::class,
            'archived_at' => 'immutable_datetime',
        ];
    }
}
