<?php

declare(strict_types=1);

/**
 * ======================================
 * Users Model =================
 * ======================================
 */

namespace Bolt\models;

use Bolt\Bolt\Database\DatabaseModel;

class Users extends DatabaseModel
{
    public static function tableName(): string
    {
        return "users";
    }
}