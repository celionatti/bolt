<?php

declare(strict_types=1);

/**
 * ==================================================
 * ==================               =================
 * Model Class
 * ==================               =================
 * ==================================================
 */

namespace Bolt\Bolt;

use DateTime;


class Model
{
    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
    // public function loadData($data, $options = [])
    // {
    //     $defaults = [
    //         'validate' => false,
    //         'type_cast' => false,
    //         'ignore_unknown' => false,
    //     ];

    //     $options = array_merge($defaults, $options);

    //     foreach ($data as $key => $value) {
    //         if (property_exists($this, $key)) {
    //             if ($options['validate']) {
    //                 if (!$this->validateData($key, $value)) {
    //                     // Handle validation errors (e.g., log, throw exceptions).
    //                     continue;
    //                 }
    //             }

    //             if ($options['type_cast']) {
    //                 $value = $this->typeCastData($key, $value);
    //             }

    //             $this->{$key} = $value;
    //         } elseif (!$options['ignore_unknown']) {
    //             // Handle unknown properties (e.g., log, throw exceptions).
    //         }
    //     }
    // }

    // private function validateData($key, $value)
    // {
    //     // Implement custom validation logic for each property.
    //     switch ($key) {
    //         case 'name':
    //             return is_string($value) && strlen($value) <= 255;
    //         case 'age':
    //             return is_int($value) && $value >= 18;
    //         case 'email':
    //             // Check for unique email address (assuming you have a users table in your database).
    //             return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    //         case 'isSubscribed':
    //             return is_bool($value);
    //         case 'birthDate':
    //             // Assuming 'birthDate' is a date string in a specific format (e.g., 'Y-m-d').
    //             return DateTime::createFromFormat('Y-m-d', $value) !== false;
    //         case 'password':
    //             // Password validation is handled separately.
    //             return password_hash($value, PASSWORD_DEFAULT);
    //         default:
    //             return true;
    //     }
    // }

    // private function typeCastData($key, $value)
    // {
    //     // Implement custom type casting logic for each property.
    //     switch ($key) {
    //         case 'age':
    //             return (int)$value;
    //         case 'birthDate':
    //             // Assuming 'birthDate' is a date string in a specific format (e.g., 'Y-m-d').
    //             return DateTime::createFromFormat('Y-m-d', $value);
    //         case 'passwordHash':
    //             // Hash the password using a secure hashing algorithm like password_hash().
    //             return password_hash($value, PASSWORD_DEFAULT);
    //         default:
    //             return $value;
    //     }
    // }
}
