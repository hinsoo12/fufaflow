<?php

namespace App\Actions\Tenancy;

use App\Audit\AuditLogger;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateBusinessSettings
{
    public function __construct(private readonly AuditLogger $audit)
    {
        //
    }

    /**
     * @param  array{name: string, country: string, currency: string, timezone: string, grace_days: int, renewal_window_days: int, renewal_after_expiry: string}  $data
     */
    public function handle(Tenant $tenant, array $data): Tenant
    {
        return DB::transaction(function () use ($tenant, $data): Tenant {
            if ($data['currency'] !== $tenant->currency && $this->hasFinancialRecords()) {
                throw ValidationException::withMessages([
                    'currency' => __('The currency cannot change once plans, invoices or payments exist.'),
                ]);
            }

            $before = $this->snapshot($tenant);

            $tenant->forceFill([
                'name' => $data['name'],
                'country' => $data['country'],
                'currency' => $data['currency'],
                'timezone' => $data['timezone'],
                'settings' => array_merge($tenant->settings ?? [], [
                    'grace_days' => $data['grace_days'],
                    'renewal_window_days' => $data['renewal_window_days'],
                    'renewal_after_expiry' => $data['renewal_after_expiry'],
                ]),
            ])->save();

            $after = $this->snapshot($tenant);
            $changes = [];

            foreach ($after as $key => $value) {
                if ($before[$key] !== $value) {
                    $changes[$key] = ['from' => $before[$key], 'to' => $value];
                }
            }

            if ($changes !== []) {
                $this->audit->record('settings.updated', $tenant, changes: $changes);
            }

            return $tenant;
        });
    }

    private function hasFinancialRecords(): bool
    {
        return Plan::query()->exists() || Invoice::query()->exists() || Payment::query()->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Tenant $tenant): array
    {
        return [
            'name' => $tenant->name,
            'country' => $tenant->country,
            'currency' => $tenant->currency,
            'timezone' => $tenant->timezone,
            'grace_days' => $tenant->graceDays(),
            'renewal_window_days' => $tenant->renewalWindowDays(),
            'renewal_after_expiry' => $tenant->renewalAfterExpiry(),
        ];
    }
}
