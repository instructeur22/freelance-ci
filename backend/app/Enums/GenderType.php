<?php
namespace App\Enums;

enum GenderType: string
{
    case Homme = 'homme';
    case Femme = 'femme';
    case Autre = 'autre';
    case NonPrecise = 'non_precise';
}
