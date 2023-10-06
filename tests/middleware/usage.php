<?php

use Bolt\Bolt\Http\Request;
use Bolt\Bolt\Http\Response;
use Bolt\Bolt\Middleware\Middleware;
use Bolt\Bolt\Middleware\MiddlewareStack;

class MyCustomMiddleware extends Middleware
{
    public function __invoke(Request $request, Response $response, $next): Response
    {
        // Middleware logic here

        // Call the next middleware in the chain
        $response = $next($request, $response);

        // Modify the response if needed
        $response = $this->modifyResponse($response);

        return $response;
    }

    protected function modifyResponse(Response $response): Response
    {
        // Implement response modification logic
        return $response;
    }
}

class AnotherMiddleware extends Middleware
{
    public function __invoke(Request $request, Response $response, $next): Response
    {
        // Middleware logic here

        // Call the next middleware in the chain
        $response = $next($request, $response);

        // Modify the response if needed
        $response = $this->modifyResponse($response);

        return $response;
    }

    protected function modifyResponse(Response $response): Response
    {
        // Implement response modification logic
        return $response;
    }
}


/**
 * Create a Middleware Chain:
 *Instantiate the MiddlewareChain class with your middleware instances in the desired order.
 */

$middlewareChain = new MiddlewareStack(
    new MyCustomMiddleware($container),
    new AnotherMiddleware($container),
    // Add more middleware instances as needed
);

/**
 * Invoke the Middleware Chain:
 *To process a request with the middleware chain, you call the __invoke method on the MiddlewareChain instance.
 */

$response = $middlewareChain->__invoke($request, $response, $next);



/**
 * Usage in your Application.
 */

// use Bolt\Bolt\Http\Request;
// use Bolt\Bolt\Http\Response;

// Create a request and response
$request = new Request();
$response = new Response();

// Create the middleware chain
$middlewareChain = new MiddlewareStack(
    new MyCustomMiddleware($container),
    new AnotherMiddleware($container),
    // Add more middleware instances as needed
);

// Define a callback for the final step in middleware processing
$finalCallback = function (Request $request, Response $response) {
    // Handle the final step, e.g., send the response to the client
};

// Process the request through the middleware chain
$response = $middlewareChain->__invoke($request, $response, $finalCallback);

// Send the final response to the client
$response->send();
