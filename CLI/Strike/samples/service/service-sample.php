<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============        ===============
 * ===== {CLASSNAME}
 * ===============        ===============
 * ======================================
 */

namespace PhpStrike\app\providers;

use celionatti\Bolt\Providers\ServiceProvider;


class {CLASSNAME} extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('{TABLENAME}', function($app) {
            echo "Hello From {TABLENAME} Service";
        });
    }

    public function boot()
    {
        // Optional boot logic
        $this->app->make('{TABLENAME}');
    }
}