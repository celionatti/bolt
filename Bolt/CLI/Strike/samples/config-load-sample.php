<?php

declare(strict_types=1);

$key = "{KEY}";

if (!defined('APP_KEY')) {
    define('APP_KEY', $key);
}

if (!defined('URL_ROOT')) {
    define('URL_ROOT', "");
}

if (!defined('DEBUG')) {
    define('DEBUG', true);
}