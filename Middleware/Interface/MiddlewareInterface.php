<?php

declare(strict_types=1);

/**
 * ==============================================
 * Bolt - MiddlewareInterface ===================
 * ==============================================
 */

namespace celionatti\Bolt\Middleware\Interface;

use celionatti\Bolt\Http\Request;
use celionatti\Bolt\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}