<?php

namespace App\Http\Middleware;

use App\Enums\Ability;
use App\Models\User;
use App\Tenancy\TenantClock;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * Tenant-dependent props are closures: this method runs before the route
     * middleware resolves the tenant, and closures are only evaluated when the
     * page renders (inside the tenant context).
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => fn (): ?array => $this->user($request),
            ],
            'tenant' => fn (): ?array => $this->tenant(),
            'can' => fn (): array => $this->abilities($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * Only the fields the UI needs; never the whole model.
     *
     * @return array<string, mixed>|null
     */
    private function user(Request $request): ?array
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'two_factor_enabled' => $user->two_factor_confirmed_at !== null,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function tenant(): ?array
    {
        $context = app(TenantContext::class);
        $tenant = $context->tenantOrNull();

        if ($tenant === null) {
            return null;
        }

        $today = TenantClock::today($tenant);
        $role = $context->membership()?->role;

        return [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'country' => $tenant->country,
            'currency' => $tenant->currency,
            'currency_exponent' => $tenant->currencyExponent(),
            'timezone' => $tenant->timezone,
            'locale' => $tenant->locale,
            'today' => $today->toDateString(),
            'account_phase' => $tenant->accountPhase($today),
            'trial_ends_on' => $tenant->trial_ends_on?->toDateString(),
            'role' => $role?->value,
            'role_label' => $role?->label(),
        ];
    }

    /**
     * The ability map the UI uses to show or hide actions. The server enforces
     * every action independently through policies.
     *
     * @return array<string, bool>
     */
    private function abilities(Request $request): array
    {
        $user = $request->user();

        if (! $user instanceof User || ! app(TenantContext::class)->has()) {
            return [];
        }

        $abilities = [];

        foreach (Ability::cases() as $ability) {
            $abilities[$ability->value] = $user->hasAbility($ability);
        }

        return $abilities;
    }
}
