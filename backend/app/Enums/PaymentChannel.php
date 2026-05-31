<?php
namespace App\Enums;

enum PaymentChannel: string
{
    case MOBILE_MONEY = 'MOBILE_MONEY';
    case CARD = 'CARD';
    case BANK_TRANSFER = 'BANK_TRANSFER';
    case USSD = 'USSD';
}
