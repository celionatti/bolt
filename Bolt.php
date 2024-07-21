<?php


declare(strict_types=1);

/**
 * ===================================
 * Bolt Class ========================
 * ===================================
 */

namespace celionatti\Bolt;

use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Database\Database;
use celionatti\Bolt\Http\Request;
use celionatti\Bolt\Http\Response;
use celionatti\Bolt\Resolver\AssetManager;
use celionatti\Bolt\Router\Router;
use celionatti\Bolt\Resolver\PathResolver;




class Bolt
{
    public Config $config;
    public Request $request;
    public Response $response;
    public Router $router;
    public Session $session;
    public Container $container;
    public Database $database;
    public ?Controller $controller;
    public ExtensionCheck $extensionCheck;
    public PathResolver $pathResolver;
    public AssetManager $assetManager;

    public static Bolt $bolt;

    public function __construct()
    {
        $this->initialize();
    }

    private function initialize()
    {
        self::$bolt = $this;

        $this->require_files();

        $this->extensionCheck = new ExtensionCheck();
        $this->extensionCheck->checkExtensions();

        $this->pathResolver = new PathResolver(get_root_dir());
        $this->assetManager = new AssetManager(URL_ROOT);

        $this->session = new Session();
        $this->config = new Config();
        $this->config::load($this->pathResolver->base_path(CONFIG_ROOT));

        $this->container = new Container();
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);

        $this->container->singleton('Database', fn () => new Database());
        $this->database = $this->container->make('Database');

        $this->verifyApplicationKey();
    }

    public function run()
    {
        try {
            $this->router->resolve();
        } catch (BoltException $e) {
            throw new BoltException($e->getMessage(), $e->getCode(), "error");
        }
    }

    private function require_files()
    {
        return [
            require __DIR__ . "/Configs/load.php",
            require __DIR__ . "/Configs/functions.php",
            require __DIR__ . "/Configs/utilities.php",
            require __DIR__ . "/Configs/global-variables.php",
            require get_root_dir() . "/configs/load.php",
            require get_root_dir() . "/utils/functions.php"
        ];
    }

    private function verifyApplicationKey(): void
    {
        if (empty(BOLT_APP_KEY)) {
            $this->dieWithError('Bolt Application Key is Missing, Kindly run the generate key command, to get a valid key', 'BOLT KEY', 'BOLT KEY ERROR - Generate New Key');
        }
    }

    private function dieWithError(string $message, string $title, string $error): void
    {
        throw new BoltException("[$title]:  $error - $message");
    }

    private function getRootDir(): string
    {
        return get_root_dir();
    }
}
