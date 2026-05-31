<?php
namespace App\Enums;

enum UserRole: string
{
    case Client = 'client';
    case Freelance = 'freelance';
    case Admin = 'admin';
}
