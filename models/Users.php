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
    /**
     * here is how to define const
     */
    const GREETING = "Bolt Users";

    /**
     * Also for defining variables
     */
    public string $name = "Bolt";
    public string $version;

    public static function tableName():string
    {
        return "users";
    }

    public function others()
    {
        
    }
}