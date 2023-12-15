<?php

declare(strict_types=1);

/**
 * Framework: PhpStrike
 * Author: Celio Natti
 * version: 1.0.0
 * Year: 2023
 * 
 * Description: This file is for Bolt global constants
 */

$key = "2c0b2fdf89bd3455b81b4ff409578d1b7bc3a067147631db9d5486b9e95024fa";

if (!defined('BOLT_APP_KEY')) {
    define('BOLT_APP_KEY', $key);
}