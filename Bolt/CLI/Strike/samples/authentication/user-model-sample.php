<?php

declare(strict_types=1);

/**
 * =============================================
 * ================             ================
 * {CLASSNAME} Model
 * ================             ================
 * =============================================
 */

namespace Bolt\models;


use Bolt\Bolt\Database\DatabaseModel;

class {CLASSNAME} extends DatabaseModel
{
    private $isInsertion = false; // Default to the login scenario

    public static function tableName(): string
    {
        return 'users';
    }

    public function setIsInsertionScenario($isInsertion)
    {
        $this->isInsertion = $isInsertion;
    }

    public function isInsertionScenario(): bool
    {
        return $this->isInsertion;
    }
    public function rules(): array
    {
        // Validation rules for user insertion (registration)
        $insertionRules = [
            'surname' => [
                ['rule' => 'required', 'message' => 'Surname is required.'],
                ['rule' => 'maxLength', 'params' => [15], 'message' => 'Surname is a minimum of 20 characters.'],
                ['rule' => 'alpha', 'message' => 'Only Alphabet characters are allowed.'],
            ],
            'othername' => [
                ['rule' => 'required', 'message' => 'Othername is required.'],
                ['rule' => 'maxLength', 'params' => [15], 'message' => 'Othername is a minimum of 20 characters.'],
                ['rule' => 'alpha', 'message' => 'Only Alphabet characters are allowed.'],
            ],
            'email' => [
                ['rule' => 'required', 'message' => 'Email is required.'],
                ['rule' => 'email', 'message' => 'Email must be valid email address.'],
            ],
            'phone' => [
                ['rule' => 'required', 'message' => 'Phone Number is Required.'],
                ['rule' => 'numeric', 'message' => 'Only Numbers are allowed.'],
            ],
            'gender' => [
                ['rule' => 'required', 'message' => 'Gender is Required.'],
            ],
            'password' => [
                ['rule' => 'required', 'message' => 'Password is Required.'],
                ['rule' => 'securePassword', 'message' => 'Password is not strong enough.'],
            ],
            'confirm_password' => [
                ['rule' => 'required', 'message' => 'Confirm Password is Required.'],
            ],
        ];

        // Validation rules for user login
        $loginRules = [
            'email' => [
                ['rule' => 'required', 'message' => 'Email is required.'],
                ['rule' => 'email', 'message' => 'Email must be valid email address.'],
            ],
            'password' => [
                ['rule' => 'required', 'message' => 'Password is Required.'],
            ]
        ];

        // Define the rules based on the scenario (insertion or login)
        return $this->isInsertionScenario() ? $insertionRules : $loginRules;
    }

    public function beforeSave(): void
    {
    }
}
