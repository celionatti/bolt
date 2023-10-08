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
use Bolt\Bolt\Resolver\AssetManager;
use Bolt\Bolt\Router\Router;
use Bolt\Bolt\Resolver\PathResolver;




class Bolt
{
    public Config $config;
    public Request $request;
    public Response $response;
    public Router $router;
    public Session $session;

    public static Bolt $bolt;

    public PathResolver $pathResolver;
    public AssetManager $assetManager;

    public function __construct()
    {
        self::$bolt = $this;
        $this->pathResolver = new PathResolver(dirname(__DIR__));
        $this->assetManager = new AssetManager(URL_ROOT);

        $this->session = new Session();
        $this->config = new Config();
        $this->config::load($this->pathResolver->base_path("configs/config.json"));
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
    }

    public function run()
    {
        try {
            $this->router->resolve();
        } catch (\Exception $e) {
            echo $e;
        }
    }
}
