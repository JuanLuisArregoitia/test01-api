<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // swagger-php v6 emite E_USER_WARNING en issues de anotaciones menores.
        // Laravel los convierte en ErrorException, lo que rompe la generación.
        // Excluimos E_USER_WARNING solo en el contexto de artisan (CLI).
        if ($this->app->runningInConsole()) {
            error_reporting(error_reporting() & ~E_USER_WARNING & ~E_USER_NOTICE);
        }
    }
}
