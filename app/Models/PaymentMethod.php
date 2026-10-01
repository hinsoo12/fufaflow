<?php

namespace App\Models;

use App\Enums\PaymentMethodKind;
use App\Tenancy\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A way the business receives money (Cash, Telebirr, a bank...).
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property PaymentMethodKind $kind
 * @property bool $is_active
 * @property int $sort_order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'kind', 'is_active', 'sort_order'])]
class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => PaymentMethodKind::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
