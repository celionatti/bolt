<?php


declare(strict_types=1);

/**
 * ===================================
 * Bolt Class ========================
 * ===================================
 */

namespace celionatti\Bolt;

use celionatti\Bolt\Router\Router;
use celionatti\Bolt\Database\Database;
use celionatti\Bolt\Container\Container;
use celionatti\Bolt\Resolver\AssetManager;
use celionatti\Bolt\Resolver\PathResolver;
use celionatti\Bolt\Illuminate\Support\Session;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Illuminate\Support\ExtensionCheck;




class Bolt
{
    public Config $config;
    public Router $router;
    public Session $session;
    public Container $container;
    public Database $database;
    public ?Controller $controller;
    public ExtensionCheck $extensionCheck;
    public PathResolver $pathResolver;
    public AssetManager $assetManager;

    public static Bolt $bolt;
    protected $providers = [];

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

        $this->config = new Config();
        $this->config::load($this->pathResolver->base_path(CONFIG_ROOT));

        $this->container = new Container();
        $this->router = new Router();

        $this->container->singleton('Database', fn () => Database::getInstance());
        $this->database = $this->container->make('Database');

        $this->verifyApplicationKey();

        $this->loadProviders(get_root_dir() . "/bootstrap/providers.php");
        $this->loadProviders(__DIR__ . "/bolt-providers.php");
        $this->bootProviders();
    }

    public function run()
    {
        try {
            $this->router->resolve();
        } catch (BoltException $e) {
            throw new BoltException($e->getMessage(), $e->getCode(), "error");
        }
    }

    public function loadProviders($configPath)
    {
        $providers = require $configPath;

        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }
    }

    public function registerProvider($provider)
    {
        $providerInstance = new $provider($this->container);
        $this->providers[] = $providerInstance;
        $providerInstance->register();
    }

    public function bootProviders()
    {
        foreach ($this->providers as $provider) {
            $provider->boot();
        }
    }

    public function __get($name)
    {
        return $this->container->$name;
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
