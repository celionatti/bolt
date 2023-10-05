<?php

namespace App\Core;

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
                if (function_exists('yaml_parse_file')) {
                    self::$config = yaml_parse_file($configFile);
                } else {
                    throw new \Exception("YAML support is not available. Please install the YAML extension.");
                }
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
        // Validation logic goes here
        return $value;
    }
}
