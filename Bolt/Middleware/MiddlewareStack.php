<?php

declare(strict_types=1);

/**
 * ============================================
 * Bolt - Middleware Stack ====================
 * ============================================
 */

namespace Bolt\Bolt\Middleware;

use Bolt\Bolt\Http\Response;
use Bolt\Bolt\Http\Request;

class MiddlewareStack extends Middleware
{
    /**
     * @var Middleware
     */
    private $currentMiddleware;

    /**
     * @var Middleware
     */
    private $nextMiddleware;

    /**
     * Constructor to set up the middleware chain.
     *
     * @param Middleware $currentMiddleware
     * @param Middleware $nextMiddleware
     */
    public function __construct(Middleware $currentMiddleware, Middleware $nextMiddleware)
    {
        $this->currentMiddleware = $currentMiddleware;
        $this->nextMiddleware = $nextMiddleware;
    }

    /**
     * Handle the middleware logic for the entire chain.
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        // Execute the current middleware in the chain
        $modifiedResponse = $this->currentMiddleware->__invoke($request, $response, function ($req, $res) use ($next) {
            // When $next is called, execute the next middleware in the chain
            return $this->nextMiddleware->__invoke($req, $res, $next);
        });

        // Return the modified response
        return $modifiedResponse;
    }
}
