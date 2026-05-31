<?php
namespace App\Enums;

enum DisputeStatus: string
{
    case Open = 'open';
    case UnderReview = 'under_review';
    case ResolvedClient = 'resolved_client';
    case ResolvedFreelance = 'resolved_freelance';
    case Closed = 'closed';
}
