<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - HandlesEvents ===============
 * ====================================
 */

namespace celionatti\Bolt\Database\Model\Traits;

trait HandlesEvents
{
    protected static $dispatcher;
    protected $observables = [];

    public static function observe(string $observerClass): void
    {
        $observer = new $observerClass;
        foreach (get_class_methods($observer) as $method) {
            if (str_starts_with($method, 'on')) {
                static::registerModelEvent(substr($method, 2), [$observer, $method]);
            }
        }
    }

    protected static function registerModelEvent(string $event, callable $callback): void
    {
        static::$dispatcher[$event][] = $callback;
    }

    protected function fireModelEvent(string $event, bool $halt = true): ?bool
    {
        foreach (static::$dispatcher[$event] ?? [] as $callback) {
            $result = $callback($this);
            if ($halt && $result === false) {
                return false;
            }
        }
        return null;
    }
}