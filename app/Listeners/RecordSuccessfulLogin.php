<?php

namespace App\Listeners;

use App\Audit\AuditLogger;
use App\Models\User;
use Illuminate\Auth\Events\Login;

class RecordSuccessfulLogin
{
    public function __construct(private readonly AuditLogger $audit)
    {
        //
    }

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        // Attribute the login to the user's business when there is exactly one.
        $memberships = $user->activeMemberships();

        $this->audit->record(
            action: 'auth.login',
            subject: $user,
            metadata: ['guard' => $event->guard, 'remember' => $event->remember],
            actor: $user,
            tenantId: $memberships->count() === 1 ? $memberships->first()?->tenant_id : null,
        );
    }
}
