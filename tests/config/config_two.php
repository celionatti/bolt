<?php

namespace App\Core;

class Config
{
    private static array $config = [
        'version'               => '1.0.0',
        'default_controller'    => 'Home', // The default home controller
        'default_layout'        => 'default', // Default layout that is used
        'debug_mode'            => false, // Debug mode flag
    ];

    private static array $cache = [];

    public static function get($key, $default = null)
    {
        // Check if the value is cached
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        // If not cached, retrieve the value from the static configuration array
        $value = array_key_exists($key, self::$config) ? self::$config[$key] : $default;

        // Validate and sanitize the retrieved value if needed
        $validatedValue = self::validateValue($key, $value);

        // Cache the validated value for future use
        self::$cache[$key] = $validatedValue;

        return $validatedValue;
    }

    public static function isDebugMode()
    {
        return self::get('debug_mode', false);
    }

    private static function validateValue($key, $value)
    {
        // Add validation rules for specific keys if needed
        switch ($key) {
            case 'version':
                // Validate that version is a valid semantic version (e.g., "1.2.3")
                if (!preg_match('/^\d+\.\d+\.\d+$/', $value)) {
                    throw new \InvalidArgumentException("Invalid version format: $value");
                }
                break;
            // Add more validation cases for other keys as needed
        }

        return $value;
    }
}
