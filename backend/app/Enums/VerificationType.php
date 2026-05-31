<?php
namespace App\Enums;

enum VerificationType: string
{
    case Identity = 'identity';
    case Portfolio = 'portfolio';
    case Diploma = 'diploma';
    case Professional = 'professional';
}
