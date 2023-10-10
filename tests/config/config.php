<?php

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
        case 'database':
            // Validate database configuration
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Invalid database configuration: $value");
            }

            $requiredKeys = ['driver', 'host', 'dbname', 'username', 'password'];
            foreach ($requiredKeys as $requiredKey) {
                if (!array_key_exists($requiredKey, $value)) {
                    throw new \InvalidArgumentException("Missing '$requiredKey' in database configuration");
                }
            }

            // Validate supported database drivers
            $supportedDrivers = ['mysql', 'pgsql', 'sqlite', 'sqlsrv']; // Add more as needed
            if (!in_array($value['driver'], $supportedDrivers)) {
                throw new \InvalidArgumentException("Unsupported database driver: {$value['driver']}");
            }

            // Optional validation for additional database parameters
            if (isset($value['charset'])) {
                // Validate charset value if provided
                $validCharsets = ['utf8', 'utf8mb4', 'latin1', 'latin2']; // Add more as needed
                if (!in_array($value['charset'], $validCharsets)) {
                    throw new \InvalidArgumentException("Invalid database charset: {$value['charset']}");
                }
            }

            if (isset($value['collation'])) {
                // Validate collation value if provided
                // Add your collation validation logic here
            }
            // Add further validation for specific database configuration values if needed.
            break;
        case 'cache':
            // Validate cache configuration
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Invalid cache configuration: $value");
            }
            $requiredKeys = ['type', 'host', 'port'];
            foreach ($requiredKeys as $requiredKey) {
                if (!array_key_exists($requiredKey, $value)) {
                    throw new \InvalidArgumentException("Missing '$requiredKey' in cache configuration");
                }
            }
            // Add further validation for specific cache configuration values if needed.
            break;
        case 'security':
            // Validate security configuration
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Invalid security configuration: $value");
            }
            // Add specific validation logic for security-related configuration options.
            break;
        case 'logging':
            // Validate logging configuration
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Invalid logging configuration: $value");
            }
            // Add specific validation logic for logging-related configuration options.
            break;
        case 'api':
            // Validate API configuration
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Invalid API configuration: $value");
            }
            // Add specific validation logic for API-related configuration options.
            break;
        case 'email':
            // Validate email configuration
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Invalid email configuration: $value");
            }
            // Add specific validation logic for email-related configuration options.
            break;
        // Add more cases for other configuration keys as needed.
