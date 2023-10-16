<?php

declare(strict_types=1);

$key = "db30936bba549c10ef0eb08efbd211589bc8fb4cc5257634ee46c60da8d1551b";

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