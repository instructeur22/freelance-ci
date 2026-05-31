<?php
namespace App\Enums;

enum MilestoneStatus: string
{
    case Pending = 'pending';
    case Delivered = 'delivered';
    case Validated = 'validated';
}
