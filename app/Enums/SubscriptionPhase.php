<?php

namespace App\Enums;

/**
 * Derived lifecycle phase. Never stored: it is computed from the stored
 * status, the dates and the tenant's "today" by SubscriptionPhaseResolver
 * (single records) and Subscription::scopeInPhase() (lists).
 */
enum SubscriptionPhase: string
{
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Grace = 'grace';
    case Expired = 'expired';
    case Completed = 'completed';
    case Paused = 'paused';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Active => 'Active',
            self::Grace => 'In grace',
            self::Expired => 'Expired',
            self::Completed => 'Completed',
            self::Paused => 'Paused',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Whether the customer still counts as an active customer.
     */
    public function isLive(): bool
    {
        return in_array($this, [self::Active, self::Grace], true);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $phase): array => ['value' => $phase->value, 'label' => $phase->label()],
            self::cases(),
        );
    }
}
