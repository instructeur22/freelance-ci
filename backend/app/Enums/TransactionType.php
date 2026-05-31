<?php
namespace App\Enums;

enum TransactionType: string
{
    case Mission = 'mission';
    case Subscription = 'subscription';
    case BoostProfile = 'boost_profile';
    case BoostProject = 'boost_project';
    case BadgeVerified = 'badge_verified';
    case Ad = 'ad';
    case Refund = 'refund';
}
