<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============        ===============
 * ===== {CLASSNAME} Provider
 * ===============        ===============
 * ======================================
 */

namespace PhpStrike\app\providers;

use celionatti\Bolt\Providers\ServiceProvider;


class {CLASSNAME} extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('example', function($app) {
            echo "Hello From App Service";
        });
    }

    public function boot()
    {
        // Optional boot logic
        $this->app->make('example');
    }
}