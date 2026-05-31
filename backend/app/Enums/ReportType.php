<?php
namespace App\Enums;

enum ReportType: string
{
    case Profil = 'profil';
    case Comportement = 'comportement';
    case Contenu = 'contenu';
    case Fraude = 'fraude';
    case Autre = 'autre';
}
