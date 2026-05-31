<?php
namespace App\Enums;

enum ContractStatus: string
{
    case Draft = 'draft';
    case Signed = 'signed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
