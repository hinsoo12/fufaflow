<?php

namespace App\Enums;

enum PaymentMethodKind: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case MobileMoney = 'mobile_money';
    case Card = 'card';
    case Other = 'other';
}
