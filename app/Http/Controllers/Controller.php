<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Test01 API',
    version: '1.0.0',
    description: 'API REST — Code Challenge Laravel + Sanctum'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token'
)]
#[OA\Server(
    url: '/api/v1',
    description: 'API v1'
)]
abstract class Controller
{
    //
}
