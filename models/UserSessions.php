<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============        ===============
 * ===== UserSessions Model
 * ===============        ===============
 * ======================================
 */

namespace Bolt\models;

use Bolt\Bolt\Database\DatabaseModel;

class UserSessions extends DatabaseModel
{
    public static function tableName():string
    {
        return "user_sessions";
    }

    public static function findByHash($hash)
    {
        return self::findOne([
            'token_hash' => $hash
        ]);
    }

    public static function createrecord(array $conditions)
    {
        return self::insert($conditions);
    }

    public static function delete($conditions)
    {
        return self::deleteBy($conditions);
    }
}