<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - Config Class ==============
 * ==================================
 */

namespace Bolt\Bolt;

class Config
{
    private static $config = [];

    /**
     * Load configuration settings from a PHP file.
     *
     * @param string $configFile The path to the configuration file.
     */
    public static function load(string $configFile)
    {
        if (file_exists($configFile)) {
            self::$config = require $configFile;
        } else {
            throw new \Exception("Configuration file not found: $configFile");
        }
    }

    /**
     * Get a configuration setting by key.
     *
     * @param string $key The key of the configuration setting.
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The value of the configuration setting, or the default value if not found.
     */
    public static function get(string $key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }

    /**
     * Set a configuration setting by key.
     *
     * @param string $key The key of the configuration setting.
     * @param mixed $value The value to set.
     */
    public static function set(string $key, $value)
    {
        self::$config[$key] = $value;
    }

    /**
     * Check if a configuration setting exists.
     *
     * @param string $key The key of the configuration setting.
     * @return bool True if the key exists, false otherwise.
     */
    public static function has(string $key)
    {
        return isset(self::$config[$key]);
    }

    /**
     * Get all configuration settings as an array.
     *
     * @return array All configuration settings.
     */
    public static function all()
    {
        return self::$config;
    }
}