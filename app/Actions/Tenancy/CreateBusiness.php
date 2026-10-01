<?php

namespace App\Actions\Tenancy;

use App\Audit\AuditLogger;
use App\Enums\MembershipRole;
use App\Enums\MembershipStatus;
use App\Enums\PaymentMethodKind;
use App\Enums\TenantStatus;
use App\Models\Membership;
use App\Models\PaymentMethod;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantClock;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Creates a business with its owner, default settings and payment methods.
 */
final class CreateBusiness
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly AuditLogger $audit,
    ) {
        //
    }

    /**
     * @param  array{name: string, business_type: ?string, country: string, currency: string, timezone: string}  $data
     */
    public function handle(User $owner, array $data): Tenant
    {
        return DB::transaction(function () use ($owner, $data): Tenant {
            $tenant = new Tenant;
            $tenant->forceFill([
                'name' => $data['name'],
                'business_type' => $data['business_type'],
                'country' => $data['country'],
                'currency' => $data['currency'],
                'timezone' => $data['timezone'],
                'locale' => config('fufaflow.defaults.locale'),
                'status' => TenantStatus::Active,
                'settings' => [
                    'grace_days' => config('fufaflow.defaults.grace_days'),
                    'renewal_window_days' => config('fufaflow.defaults.renewal_window_days'),
                    'renewal_after_expiry' => config('fufaflow.defaults.renewal_after_expiry'),
                ],
            ]);
            $tenant->save();

            $tenant->trial_ends_on = TenantClock::today($tenant)->addDays((int) config('fufaflow.defaults.trial_days'));
            $tenant->save();

            $this->context->run($tenant, function () use ($tenant, $owner): void {
                $membership = new Membership;
                $membership->forceFill([
                    'user_id' => $owner->id,
                    'role' => MembershipRole::Owner,
                    'status' => MembershipStatus::Active,
                    'joined_at' => TenantClock::now(),
                ])->save();

                $this->seedPaymentMethods($tenant);

                $this->audit->record('tenant.created', $tenant, metadata: [
                    'name' => $tenant->name,
                    'currency' => $tenant->currency,
                    'timezone' => $tenant->timezone,
                ], actor: $owner);
            });

            return $tenant;
        });
    }

    private function seedPaymentMethods(Tenant $tenant): void
    {
        /** @var array<string, list<array{name: string, kind: string}>> $presets */
        $presets = config('fufaflow.payment_methods');
        $methods = $presets[$tenant->country] ?? $presets['default'];

        foreach ($methods as $position => $method) {
            PaymentMethod::create([
                'name' => $method['name'],
                'kind' => PaymentMethodKind::from($method['kind']),
                'is_active' => true,
                'sort_order' => $position,
            ]);
        }
    }
}
