<?php

namespace App\Providers;

use App\Enums\Ability;
use App\Listeners\RecordSuccessfulLogin;
use App\Models\Customer;
use App\Models\Invitation;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Scoped: reset for every request and between queued jobs, so one
        // tenant's context can never leak into another tenant's work.
        $this->app->scoped(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureModels();
        $this->configureAuthorization();

        Event::listen(Login::class, RecordSuccessfulLogin::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureModels(): void
    {
        // Outside production: fail on lazy loading in loops, on silently
        // discarded (non-fillable) attributes and on missing attributes.
        Model::shouldBeStrict(! app()->isProduction());

        Relation::enforceMorphMap([
            'user' => User::class,
            'tenant' => Tenant::class,
            'membership' => Membership::class,
            'invitation' => Invitation::class,
            'customer' => Customer::class,
            'plan' => Plan::class,
            'payment_method' => PaymentMethod::class,
            'subscription' => Subscription::class,
            'invoice' => Invoice::class,
            'payment' => Payment::class,
        ]);
    }

    /**
     * Every ability is a gate, so `@can('payments.void')`-style checks and the
     * permission map shared with the UI come from the same role matrix.
     */
    protected function configureAuthorization(): void
    {
        foreach (Ability::cases() as $ability) {
            Gate::define($ability->value, fn (User $user): bool => $user->hasAbility($ability));
        }
    }
}
