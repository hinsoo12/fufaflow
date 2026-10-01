<?php

namespace App\Enums;

/**
 * Stored invoice states. "Partially paid" and "overdue" are derived.
 */
enum InvoiceStatus: string
{
    case Issued = 'issued';
    case Paid = 'paid';
    case Void = 'void';
}
