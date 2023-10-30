<?php

declare(strict_types=1);

/**
 * ==========================================
 * ================         =================
 * Controller Class
 * ================         =================
 * ==========================================
 */

namespace celionatti\Bolt;

use celionatti\Bolt\Http\Response;
use celionatti\Bolt\View\BoltView;
use celionatti\Bolt\Middleware\Middleware;

class Controller
{
    public BoltView $view;
    public string $action = '';
    protected $currentUser;

    /**
     * @var \celionatti\Bolt\Middleware\Middleware[]
     */
    protected array $middlewares = [];

    public function __construct()
    {
        $this->view = new BoltView('', ENABLE_BLADE ?? false, ENABLE_TWIG ?? false);
        $this->view->setLayout("default");
        $this->onConstruct();
    }

    public function setCurrentUser($user)
    {
        // Allow the developer to set the current user
        $this->currentUser = $user;
    }

    public function registerMiddleware(Middleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * @return \celionatti\Bolt\Middleware\Middleware[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function json_response($data, $statusCode = Response::OK, $headers = [], $options = JSON_PRETTY_PRINT): void
    {
        if (!is_array($data)) {
            // Handle invalid data, maybe by throwing an exception or returning an error response
            $this->json_error_response('Invalid data', Response::BAD_REQUEST);
            return;
        }

        // Allow for additional custom headers
        $defaultHeaders = [
            "Access-Control-Allow-Origin" => "*",
            "Content-Type" => "application/json; charset=UTF-8",
        ];

        // Merge custom headers with default headers
        $mergedHeaders = array_merge($defaultHeaders, $headers);

        header("HTTP/1.1 $statusCode " . Response::getStatusText($statusCode));

        foreach ($mergedHeaders as $name => $value) {
            header("$name: $value");
        }

        echo json_encode($data, $options);
        exit;
    }

    public function json_error_response($message, $statusCode = Response::INTERNAL_SERVER_ERROR): void
    {
        $errorResponse = [
            'error' => true,
            'message' => $message,
        ];

        $this->json_response($errorResponse, $statusCode);
    }

    public function onConstruct(): void
    {
    }
}
