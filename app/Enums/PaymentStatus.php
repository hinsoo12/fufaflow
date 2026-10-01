<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Recorded = 'recorded';
    case Void = 'void';
}
