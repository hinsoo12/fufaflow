<?php

namespace App\Http\Middleware;

use App\Models\Membership;
use App\Models\User;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the business the authenticated user is working in.
 *
 * The tenant always comes from the user's active membership (re-checked on
 * every request), never from request input. Users without a business are sent
 * to onboarding. Deactivated members lose access immediately.
 */
class ResolveTenant
{
    public const SESSION_KEY = 'current_tenant_id';

    public function __construct(private readonly TenantContext $context)
    {
        //
    }

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        $membership = $this->resolveMembership($request, $user);

        if ($membership === null) {
            abort_if($request->expectsJson(), 403);

            return redirect()->route('onboarding.create');
        }

        $this->context->set($membership->tenant, $membership);

        try {
            if (! $request->isMethodSafe() && ! $membership->tenant->canWrite()) {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => __('This business account is read-only. Contact Fufa to reactivate it.'),
                ]);

                return back(303);
            }

            return $next($request);
        } finally {
            // Never let one request's tenant outlive the request.
            $this->context->clear();
        }
    }

    private function resolveMembership(Request $request, User $user): ?Membership
    {
        $memberships = $user->activeMemberships();

        if ($memberships->isEmpty()) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        $preferred = $request->session()->get(self::SESSION_KEY);

        $membership = $memberships->firstWhere('tenant_id', $preferred) ?? $memberships->first();

        $request->session()->put(self::SESSION_KEY, $membership?->tenant_id);

        return $membership;
    }
}
