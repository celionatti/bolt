<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - Config Class ==============
 * ==================================
 */

namespace Bolt\Bolt;

use Symfony\Component\Yaml\Yaml;


class Config
{
    private static array $config = [];
    private static array $cache = [];

    public static function load(string $configFile)
    {
        $fileExtension = pathinfo($configFile, PATHINFO_EXTENSION);

        if (!file_exists($configFile)) {
            throw new \Exception("Configuration file not found: $configFile");
        }

        switch ($fileExtension) {
            case 'json':
                self::$config = json_decode(file_get_contents($configFile), true);
                break;
            case 'yaml':
            case 'yml':
                self::$config = Yaml::parseFile($configFile);
                break;
            case 'ini':
                self::$config = parse_ini_file($configFile, true);
                break;
            default:
                throw new \Exception("Unsupported configuration file format: $fileExtension");
        }
    }

    public static function get($key, $default = null)
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $value = array_key_exists($key, self::$config) ? self::$config[$key] : $default;
        $validatedValue = self::validateValue($key, $value);

        self::$cache[$key] = $validatedValue;

        return $validatedValue;
    }

    public static function isDebugMode()
    {
        return self::get('debug_mode', false);
    }

    private static function validateValue($key, $value)
    {
        switch ($key) {
            case 'version':
                // Validate that version is a valid semantic version (e.g., "1.2.3")
                if (!preg_match('/^\d+\.\d+\.\d+$/', $value)) {
                    throw new \InvalidArgumentException("Invalid version format: $value");
                }
                break;
            case 'debug_mode':
                // Validate that debug_mode is a boolean value
                if (!is_bool($value)) {
                    throw new \InvalidArgumentException("Invalid debug_mode value: $value");
                }
                break;
                // Add more validation cases for other keys as needed
        }

        return $value;
    }
}
