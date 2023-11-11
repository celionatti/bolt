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

    public function json_response($data, $statusCode = Response::OK, $headers = [], $options = JSON_PRETTY_PRINT, $enableCompression = true): void
    {
        // Allow for additional custom headers
        $defaultHeaders = [
            "Access-Control-Allow-Origin" => "*",
            "Content-Type" => "application/json",
        ];

        // Merge custom headers with default headers
        $mergedHeaders = array_merge($defaultHeaders, $headers);

        // CORS headers
        $mergedHeaders['Access-Control-Allow-Methods'] = 'GET, POST, OPTIONS';
        $mergedHeaders['Access-Control-Allow-Headers'] = 'Content-Type';

        // Enable Gzip Compression if specified
        if ($enableCompression) {
            // Compression: Enable Gzip Compression
            ob_start("ob_gzhandler");

            // Compression: Set Content-Encoding Header
            header('Content-Encoding: gzip');
        }

        http_response_code($statusCode);

        foreach ($mergedHeaders as $name => $value) {
            header("$name: $value");
        }

        $json = json_encode($data, $options);

        if ($json === false) {
            $this->json_error_response('Error encoding JSON', Response::INTERNAL_SERVER_ERROR);
            return;
        }

        // JSONP support
        $callback = isset($_GET['callback']) ? $_GET['callback'] : null;

        if (!empty($callback)) {
            echo $callback . '(' . $json . ');';
        } else {
            echo $json;
        }

        die;
    }

    /**
     * Recursively applies htmlspecialchars to data if sanitization is enabled.
     *
     * @param mixed $data
     * @return mixed
     */
    private function sanitizeData($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeData'], $data);
        } else {
            return htmlspecialchars($data);
        }
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
