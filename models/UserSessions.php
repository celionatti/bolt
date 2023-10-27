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
    public static function tableName(): string
    {
        return "user_sessions";
    }

    public function findByHash($hash)
    {
        return $this->findOne([
            'token_hash' => $hash
        ]);
    }

    public function createrecord(array $data)
    {
        return $this->insert($data);
    }


    public function delete($conditions)
    {
        return $this->deleteBy($conditions);
    }
}
