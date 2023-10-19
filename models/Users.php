<?php

declare(strict_types=1);

/**
 * =============================================
 * ================             ================
 * Users Model
 * ================             ================
 * =============================================
 */

namespace Bolt\models;


use Bolt\Bolt\Database\DatabaseModel;

class Users extends DatabaseModel
{
    public static function tableName(): string
    {
        return 'users';
    }

    public function rules(): array
    {
        return [
            'username' => [
                ['rule' => 'required', 'message' => 'Username is required.'],
                ['rule' => 'maxLength', 'params' => [15], 'message' => 'Username characters is too long.'],
                ['rule' => 'alpha', 'message' => 'Only Alphabet characters are allowed.'],
            ],
            'name' => [
                ['rule' => 'required', 'message' => 'Name is required.'],
                ['rule' => 'maxLength', 'params' => [255], 'message' => 'Name characters is too long.'],
            ],
            'email' => [
                ['rule' => 'required', 'message' => 'Email is required.'],
                ['rule' => 'maxLength', 'params' => [100], 'message' => 'Email is too long.'],
                ['rule' => 'email', 'message' => 'Email must be valid email address.'],
            ],
            'phone' => [
                ['rule' => 'required', 'message' => 'Phone Number is Required.'],
                ['rule' => 'numeric', 'message' => 'Only Numbers are allowed.'],
            ],
            'password' => [
                ['rule' => 'required', 'message' => 'Password is Required.'],
                ['rule' => 'securePassword', 'message' => 'Password is not strong enough.'],
            ],
            'confirm_password' => [
                ['rule' => 'required', 'message' => 'Confirm Password is Required.'],
                ['rule' => 'passwordsMatch', 'params' => ['password'], 'message' => 'Passwords do not match.'],
            ],
        ];
    }

    public function beforeSave(): void
    {
    }
}
