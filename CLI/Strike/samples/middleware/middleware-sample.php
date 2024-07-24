<?php

declare(strict_types=1);

/**
 * ===============================================
 * ==================           ==================
 * ****** {CLASSNAME}
 * ==================           ==================
 * ===============================================
 */

namespace PhpStrike\app\middlewares;

use celionatti\Bolt\Http\Request;
use celionatti\Bolt\Http\Response;
use celionatti\Bolt\Middleware\BaseMiddleware;

class {CLASSNAME} extends BaseMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Check if user is authenticated
        if (!$this->isAuthenticated()) {
            return new Response('Unauthorized', 401);
        }

        return $next($request);
    }

    private function isAuthenticated(): bool
    {
        // Implement your authentication logic here
        return isset($_SESSION['user']);
    }
}
