<?php
namespace App\Enums;

enum EscrowStatus: string
{
    case Holding = 'holding';
    case Released = 'released';
    case Refunded = 'refunded';
    case Disputed = 'disputed';
}
