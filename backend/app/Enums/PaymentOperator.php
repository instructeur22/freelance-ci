<?php
namespace App\Enums;

enum PaymentOperator: string
{
    case ORANGE = 'ORANGE';
    case MTN = 'MTN';
    case MOOV = 'MOOV';
    case WAVE = 'WAVE';
    case CARD = 'CARD';
    case UNKNOWN = 'UNKNOWN';
}
