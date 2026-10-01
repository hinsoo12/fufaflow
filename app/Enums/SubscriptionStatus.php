<?php

namespace App\Enums;

/**
 * Stored decisions only. Use SubscriptionPhase for anything time-based.
 */
enum SubscriptionStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Cancelled = 'cancelled';
}
