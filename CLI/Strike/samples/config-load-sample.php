<?php

declare(strict_types=1);

/**
 * Framework: PhpStrike
 * Author: Celio Natti
 * version: 1.0.0
 * Year: 2023
 * 
 * Description: This file is for global constants
 */

$key = "{KEY}";

if (!defined('APP_KEY')) {
    define('APP_KEY', $key);
}

if (!defined('URL_ROOT')) {
    define('URL_ROOT', "");
}

if (!defined('CONFIG_ROOT')) {
    define('CONFIG_ROOT', "configs/config.json");
}

if (!defined('DEBUG')) {
    define('DEBUG', true);
}

if (!defined('BOLT_DATABASE')) {
    define('BOLT_DATABASE', "bolt_database");
}

if (!defined('DB_NAME')) {
    define('DB_NAME', "bolt");
}

if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', "root");
}

if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', "");
}

if (!defined('DB_DRIVERS')) {
    define('DB_DRIVERS', "mysql");
}

if (!defined('DB_HOST')) {
    define('DB_HOST', "127.0.0.1");
}

if (!defined('MAILER_EMAIL')) {
    define('MAILER_EMAIL', "");
}

if (!defined('MAILER_PASSWORD')) {
    define('MAILER_PASSWORD', "");
}

if (!defined('MAILER_HOST')) {
    define('MAILER_HOST', "smtp.gmail.com");
}
