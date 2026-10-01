<?php

namespace App\Enums;

/**
 * Manual: nothing happens until staff renew; after grace the subscription is
 * derived as expired (prepaid memberships). Automatic (P1): the system adds the
 * next period and invoice ahead of time and never expires the subscription.
 */
enum RenewalMode: string
{
    case Manual = 'manual';
    case Automatic = 'automatic';
}
