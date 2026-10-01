<?php

namespace App\Billing;

use App\Enums\RenewalMode;
use App\Enums\SubscriptionPhase;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Carbon\CarbonImmutable;

/**
 * Derives a subscription's lifecycle phase. Rules are evaluated in order and
 * the first match wins (see docs/architecture/DOMAIN-MODEL.md):
 *
 *   1. cancelled  status = cancelled, or cancel_on is set and today > cancel_on
 *   2. paused     status = paused
 *   3. scheduled  today < starts_on
 *   4. completed  fixed term used up and today > current_period_ends_on
 *   5. expired    manual renewal and today > current_period_ends_on + grace
 *   6. grace      manual renewal and today > current_period_ends_on
 *   7. active     everything else
 *
 * Subscription::scopeInPhase() implements the same rules in SQL; a test keeps
 * the two in lockstep.
 */
final class SubscriptionPhaseResolver
{
    public function resolve(Subscription $subscription, CarbonImmutable $today, int $graceDays): SubscriptionPhase
    {
        if ($subscription->status === SubscriptionStatus::Cancelled
            || ($subscription->cancel_on !== null && $today->gt($subscription->cancel_on))) {
            return SubscriptionPhase::Cancelled;
        }

        if ($subscription->status === SubscriptionStatus::Paused) {
            return SubscriptionPhase::Paused;
        }

        if ($today->lt($subscription->starts_on)) {
            return SubscriptionPhase::Scheduled;
        }

        if ($subscription->isTermComplete() && $today->gt($subscription->current_period_ends_on)) {
            return SubscriptionPhase::Completed;
        }

        if ($subscription->renewal_mode === RenewalMode::Manual) {
            if ($today->gt($subscription->current_period_ends_on->addDays($graceDays))) {
                return SubscriptionPhase::Expired;
            }

            if ($today->gt($subscription->current_period_ends_on)) {
                return SubscriptionPhase::Grace;
            }
        }

        return SubscriptionPhase::Active;
    }

    /**
     * Active and ending within the renewal window (inclusive).
     */
    public function isRenewalDue(Subscription $subscription, CarbonImmutable $today, int $graceDays, int $windowDays): bool
    {
        if ($this->resolve($subscription, $today, $graceDays) !== SubscriptionPhase::Active) {
            return false;
        }

        $daysLeft = (int) $today->diffInDays($subscription->current_period_ends_on, false);

        return $daysLeft >= 0 && $daysLeft <= $windowDays;
    }
}
