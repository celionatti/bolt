<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - CsrfServiceProvider Class ====
 * =====================================
 */

namespace celionatti\Bolt\Providers\Services;

use celionatti\Bolt\Providers\ServiceProvider;

class CsrfServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('csrf', function($app) {
            echo "Hello From App Service <br>";
        });
    }

    public function boot()
    {
        // Optional boot logic
        $this->app->make('csrf');
    }
}