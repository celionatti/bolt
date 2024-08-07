<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - SessionServiceProvider =======
 * =====================================
 */

namespace celionatti\Bolt\Providers\Services;

use celionatti\Bolt\Providers\ServiceProvider;
use celionatti\Bolt\Sessions\Handlers\DefaultSessionHandler;

class SessionServiceProvider extends ServiceProvider
{    
    public function register()
    {
        $this->app->bind('sessions', function($app) {
            return new DefaultSessionHandler();
        });
    }

    public function boot()
    {
        $this->app->make('sessions');
    }
}