<?php


declare(strict_types=1);

/**
 * ===================================
 * Bolt Class ========================
 * ===================================
 */

namespace Bolt\Bolt;

use Bolt\Bolt\Http\Request;
use Bolt\Bolt\Http\Response;
use Bolt\Bolt\Resolver\PathResolver;




class Bolt
{
    public Request $request;
    public Response $response;
    public Router $router;

    private static Bolt $instance;
    public static Bolt $bolt;

    public PathResolver $pathResolver;

    public function __construct()
    {
        self::$bolt = $this;
        $this->pathResolver = new PathResolver(dirname(__DIR__));

        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);

    }

    public function run()
    {
        try {
            echo $this->router->resolve();
        } catch (\Exception $e) {
            echo $e;
        }
    }
}
