<?php

declare(strict_types=1);

use celionatti\Bolt\BoltException\BoltException;

/**
 * Framework: PhpStrike
 * Author: Celio Natti
 * version: 1.0.0
 * Year: 2023
 * 
 * Description: This file is for Bolt global constants
 */

ini_set('display_errors', '0'); // Turn off error display

// Register a shutdown function
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        if ($error['type'] === E_ERROR) {
            // Handle fatal errors and wrap them in your custom exception
            throw new BoltException("{$error['message']}", $error['type']);
        }
    }
});

$key = "{KEY}";

if (!defined('BOLT_APP_KEY')) {
    define('BOLT_APP_KEY', $key);
}