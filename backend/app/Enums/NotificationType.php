<?php
namespace App\Enums;

enum NotificationType: string
{
    case Message = 'message';
    case Offer = 'offer';
    case Payment = 'payment';
    case Project = 'project';
    case Review = 'review';
    case System = 'system';
    case Alert = 'alert';
}
