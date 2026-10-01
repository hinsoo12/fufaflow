<?php

namespace App\Enums;

enum InvoiceKind: string
{
    case Standard = 'standard';
    case OpeningBalance = 'opening_balance';
}
