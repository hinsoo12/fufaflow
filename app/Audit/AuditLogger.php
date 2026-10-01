<?php

namespace App\Audit;

use App\Models\AuditLog;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Records business activity. Called explicitly from actions (not model
 * observers) so every entry corresponds to a deliberate business operation.
 */
final class AuditLogger
{
    public function __construct(private readonly TenantContext $context)
    {
        //
    }

    /**
     * @param  array<string, mixed>  $changes  before/after values for edits
     * @param  array<string, mixed>  $metadata  extra context (amounts, numbers, reasons)
     */
    public function record(
        string $action,
        ?Model $subject = null,
        array $changes = [],
        array $metadata = [],
        ?User $actor = null,
        ?int $tenantId = null,
    ): AuditLog {
        $actor ??= Auth::user() instanceof User ? Auth::user() : null;

        $log = new AuditLog;
        $log->forceFill([
            'tenant_id' => $tenantId ?? $this->context->tenantOrNull()?->id,
            'actor_type' => $actor !== null ? 'user' : 'system',
            'actor_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'changes' => $changes === [] ? null : $changes,
            'metadata' => $metadata === [] ? null : $metadata,
            'ip' => request()->ip(),
            'created_at' => now(),
        ]);
        $log->save();

        return $log;
    }
}
