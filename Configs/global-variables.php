<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Global Variables =========
 * =================================
 */

if (!defined('BOLT_ROOT')) {
    define('BOLT_ROOT', get_root_dir());
}

if (!defined('ACCESS_RULES')) {
    define('ACCESS_RULES', [
        'all'    => ['admin', 'user', 'author', 'editor', 'manager', 'guest'],
        'create' => ['admin', 'user', 'author', 'editor', 'manager', 'guest'],
        'view'   => ['admin', 'user', 'author', 'editor', 'manager', 'guest'],
        'edit'   => ['admin', 'user', 'author', 'editor', 'manager', 'guest'],
        'delete' => ['admin', 'user', 'author', 'editor', 'manager', 'guest'],
        // Add more actions and roles as needed
    ]);
}

if (!defined('COOKIE_SECRET')) {
    define('COOKIE_SECRET', "");
}
