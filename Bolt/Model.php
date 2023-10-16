<?php

declare(strict_types=1);

/**
 * ===================================================
 * ======================           ==================
 * Model Class
 * ======================           ==================
 * ===================================================
 */

namespace Bolt\Bolt;

use DateTime;

class Model
{
    public function loadData($data, $options = [])
    {
        $defaults = [
            'validate' => false,
            'type_cast' => false,
            'ignore_unknown' => false,
        ];

        $options = array_merge($defaults, $options);

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                if ($options['validate']) {
                    if (!$this->validateData($key, $value)) {
                        // Handle validation errors (e.g., log, throw exceptions).
                        continue;
                    }
                }

                if ($options['type_cast']) {
                    $value = $this->typeCastData($key, $value);
                }

                $this->{$key} = $value;
            } elseif (!$options['ignore_unknown']) {
                // Handle unknown properties (e.g., log, throw exceptions).
            }
        }
    }

    private function validateData($key, $value)
    {
        // Implement custom validation logic for each property.
        // Example: return true if the data is valid, false otherwise.
        switch ($key) {
            case 'name':
                return is_string($value) && strlen($value) <= 255;
            case 'age':
                return is_int($value) && $value >= 18;
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'isSubscribed':
                return is_bool($value);
            case 'birthDate':
                // Assuming 'birthDate' is a date string in a specific format (e.g., 'Y-m-d').
                return DateTime::createFromFormat('Y-m-d', $value) !== false;
            default:
                return true;
        }
    }

    private function typeCastData($key, $value)
    {
        // Implement custom type casting logic for each property.
        // Example: Convert string to integer, parse dates, etc.
        switch ($key) {
            case 'age':
                return (int)$value;
            case 'name':
                return (string)$value;
            case 'username':
                return (string)$value;
            case 'surname':
                return (string)$value;
            case 'birthDate':
                // Assuming 'birthDate' is a date string in a specific format (e.g., 'Y-m-d').
                return DateTime::createFromFormat('Y-m-d', $value);
            case 'password':
                // Hash the password using a secure hashing algorithm like password_hash().
                // You can add salt for additional security.
                return password_hash($value, PASSWORD_DEFAULT);
            default:
                return $value;
        }
    }
}
