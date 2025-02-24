<?php

declare(strict_types=1);

namespace celionatti\Bolt\Router;

use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Database\Model;
use celionatti\Bolt\Cache\CacheInterface;
use celionatti\Bolt\Container\Container;
use celionatti\Bolt\Security\RateLimiter;

class RouteBinding
{
    protected static $binders = [];
    protected static ?Container $container = null;

    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }

    public static function resolve(string $modelClass, string $value)
    {
        if (!self::$container) {
            throw new BoltException("Container not initialized for RouteBinding");
        }

        $model = self::$container->make($modelClass);
        $column = $model->getRouteKeyName();

        if (isset(self::$binders[$modelClass])) {
            return call_user_func(self::$binders[$modelClass], $value);
        }

        $result = $model->resolveRouteBinding($value, $column);

        if (!$result) {
            throw new BoltException("No query results for model [{$modelClass}]", 404);
        }

        return $result;
    }

    public static function register(string $modelClass, callable $callback): void
    {
        self::$binders[$modelClass] = $callback;
    }
}