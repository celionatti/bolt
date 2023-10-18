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
    public string $username = "";
    public string $name = "";
    public string $email = "";
    public string $phone = "";
    public string $password = "";
    public string $confirm_password = "";

    public static function tableName(): string
    {
        return 'users';
    }

    public function rules()
    {
        return [
            'username' => [self::RULE_REQUIRED],
            'name' => [self::RULE_REQUIRED],
            'email' => [self::RULE_EMAIL],
            'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => '8']],
            // 'confirm_password' => [[self::RULE_MATCH, 'match' => 'password']],
        ];
    }

    public function beforeSave(): void
    {
    }
}
