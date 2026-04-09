<?php

namespace App\Swagger;

use OpenApi\Loggers\DefaultLogger;
use Psr\Log\LogLevel;

/**
 * Silencia los E_USER_WARNING de swagger-php para que no rompan la generación.
 * Los warnings de "Required @OA\PathItem() not found" son avisos, no errores reales.
 */
class SwaggerLogger extends DefaultLogger
{
    public function log($level, $message, array $context = []): void
    {
        if ($level === LogLevel::WARNING) {
            return; // swallow warnings, only fail on errors
        }

        parent::log($level, $message, $context);
    }
}
