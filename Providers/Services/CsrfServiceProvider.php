<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - CsrfServiceProvider Class ====
 * =====================================
 */

namespace celionatti\Bolt\Providers\Services;

use celionatti\Bolt\Http\Request;
use celionatti\Bolt\Helpers\CSRF\Csrf;
use celionatti\Bolt\Providers\ServiceProvider;
use celionatti\Bolt\BoltException\BoltException;

class CsrfServiceProvider extends ServiceProvider
{
    protected Csrf $csrf;
    
    public function register()
    {
        $this->app->bind('csrf', function($app) {
            return new Csrf();
        });
    }

    public function boot()
    {
        $this->csrf = $this->app->make('csrf');
        $this->handle();
    }

    private function handle()
    {
        $request = Request::instance();
        
        if (in_array($request->getMethod(), ['POST', 'DELETE', 'PUT'])) {
            $formToken = $request->getBodyParam('__bv_csrf_token');

            if (!$formToken) {
                throw new BoltException('CSRF token missing from form.');
            }

            if (!$this->csrf->validateToken($formToken)) {
                throw new BoltException('CSRF token mismatch or expired.');
            }
        }
    }
}