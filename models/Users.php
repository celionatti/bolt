<?php

declare(strict_types=1);

/**
 * ======================================
 * Users =================
 * ======================================
 */

namespace Bolt\models;

use Bolt\Bolt\Database\DatabaseModel;

class Users extends DatabaseModel
{
    public static function tableName(): string
    {
        return 'users';
    }
}