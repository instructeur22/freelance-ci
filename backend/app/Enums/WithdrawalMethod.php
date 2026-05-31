<?php
namespace App\Enums;

enum WithdrawalMethod: string
{
    case OrangeMoney = 'orange_money';
    case MtnMoMo = 'mtn_momo';
    case Wave = 'wave';
    case BankTransfer = 'bank_transfer';
}
