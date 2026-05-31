<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '0.6.0',
    title: 'Freelance CI API',
    description: 'API REST de la plateforme Freelance CI — connexion freelances, clients et entrepreneurs en Côte d\'Ivoire. Paiements via Genius Pay (Mobile Money, Carte), authentification Supabase JWT.',
    contact: new OA\Contact(
        email: 'contact@freelance-ci.com',
        name: 'Freelance CI',
    ),
    license: new OA\License(
        name: 'MIT',
    ),
)]
#[OA\Server(
    url: 'http://localhost:8000/api',
    description: 'Serveur de développement local',
)]
#[OA\Server(
    url: 'https://api.freelance-ci.com/api',
    description: 'Serveur de production',
)]
#[OA\SecurityScheme(
    securityScheme: 'BearerToken',
    type: 'http',
    scheme: 'bearer',
    description: 'JWT Supabase — coller le token reçu après connexion',
)]
class Annotations
{
}
