<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - CsrfServiceProvider Class ====
 * =====================================
 */

namespace celionatti\Bolt\Providers\Services;

use celionatti\Bolt\Helpers\CSRF\Csrf;
use celionatti\Bolt\Providers\ServiceProvider;

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
        $this->handle($this->app->Request);
    }

    private function handle($request)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $method = $request->getMethod();

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'GET'])) {
            $csrfToken = $request->get('_csrf_token') ?? $request->get('X-CSRF-TOKEN');
            
            if (!$csrfToken || !$this->csrf->validateToken($csrfToken)) {
                throw new \Exception('CSRF token validation failed.');
            }
        }

        // Generate a new token if it's a GET request or no token is set
        if ($method === 'GET' || !$this->csrf->getToken()) {
            $this->csrf->generateToken();
        }

        // Set the token in the request for form rendering purposes
        $request->set('_csrf_token', $this->csrf->getToken());
    }
}