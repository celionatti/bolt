<?php

declare(strict_types=1);

/**
 * ==============================================
 * Bolt - BaseMiddleware ========================
 * ==============================================
 */

namespace celionatti\Bolt\Middleware;

use celionatti\Bolt\Http\Request;
use celionatti\Bolt\Http\Response;
use celionatti\Bolt\Middleware\Interface\MiddlewareInterface;


abstract class BaseMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // Default behavior can be defined here
        return $next($request);
    }
}
