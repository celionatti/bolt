<?php


declare(strict_types=1);

/**
 * ===================================
 * Bolt Class ========================
 * ===================================
 */

namespace Bolt\Bolt;

use Bolt\Bolt\Database\Database;
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
    public Container $container;
    public Database $database;

    public static Bolt $bolt;

    public PathResolver $pathResolver;
    public AssetManager $assetManager;

    public function __construct()
    {
        $this->require_files();

        $this->bolt_run();

        self::$bolt = $this;
        $this->pathResolver = new PathResolver(dirname(__DIR__));
        $this->assetManager = new AssetManager(URL_ROOT);

        $this->session = new Session();
        $this->config = new Config();
        $this->config::load($this->pathResolver->base_path(CONFIG_ROOT));
        $this->container = new Container();
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);

        $this->container->singleton("Database", function () {
            return new Database();
        });

        $this->database = $this->container->make("Database");
    }

    public function run()
    {
        try {
            $this->router->resolve();
        } catch (\Exception $e) {
            echo $e;
        }
    }

    private function require_files()
    {
        return [
            require __DIR__ . "/Configs/load.php",
            require __DIR__ . "/Configs/functions.php",
            require __DIR__ . "/Configs/global-variables.php",
            require dirname(__DIR__) . "/configs/load.php"
        ];
    }

    private function bolt_run()
    {
        // Evaluate the expressions and store their results
        $result1 = BOLT_APP_KEY;
        $result2 = APP_KEY;

        // Check if both results are not empty and equal
        if (!empty($result1) && !empty($result2) && $result1 === $result2) {
            return true; // Both expressions are not empty and equal
        }

        return bolt_die("Bolt Application Key is Missing, Kindly run the generate key command, to get a valid key", "BOLT KEY", "BOLT KEY ERROR - Generate New Key"); // Expressions are empty or not equal
    }
}
