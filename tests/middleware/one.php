<?php

<?php

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

abstract class Middleware
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor to inject dependencies.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Handle the middleware logic.
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public abstract function __invoke(Request $request, Response $response, $next): Response;

    /**
     * Modify the response before returning it.
     *
     * @param Response $response
     * @return Response
     */
    protected function modifyResponse(Response $response): Response
    {
        // Implement response modification logic in subclasses
        return $response;
    }

    /**
     * Middleware stacking mechanism.
     *
     * @param Middleware $middleware
     * @return Middleware
     */
    public function stack(Middleware $middleware): Middleware
    {
        // Implement middleware stacking logic here
        // You can create a chain of middleware
        return $middleware;
    }

    /**
     * Handle exceptions within the middleware.
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     * @throws \Exception
     */
    protected function handleExceptions(Request $request, Response $response, $next): Response
    {
        try {
            return $next($request, $response);
        } catch (\Exception $e) {
            // Handle exceptions here, e.g., log, report, or render an error response
            throw $e;
        }
    }
}
