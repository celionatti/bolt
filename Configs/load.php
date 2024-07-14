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


/**
 * Bolt key value.
 * @var string
 */

$key = null;

if (!defined('BOLT_APP_KEY')) {
    define('BOLT_APP_KEY', $key);
}