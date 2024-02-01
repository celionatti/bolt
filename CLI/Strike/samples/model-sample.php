<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============        ===============
 * ===== {CLASSNAME} Model
 * ===============        ===============
 * ======================================
 */

namespace PhpStrike\models;

use celionatti\Bolt\Database\DatabaseModel;

class {CLASSNAME} extends DatabaseModel
{
    private $scenario = 'create'; // Default scenario is 'create'


    /**
     * If incase you are using __construct
     * You also need to bring in the parent parent::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        // Set some default values
        $this->limit = 20;
        $this->order = 'asc';
    }

    public static function tableName():string
    {
        return "{TABLENAME}";
    }

    public function setIsInsertionScenario($isInsertion)
    {
        $this->isInsertion = $isInsertion;
    }

    public function setIsInsertionScenario(string $scenario)
    {
        // You can validate the scenario here if needed
        $this->scenario = $scenario;
    }

    /**
     * Validation Mode -- Option One.
     *
     */
    public function rules(string $scenario = null): array
    {
        // Define validation rules for different scenarios
        $rules = [
            'signup' => [
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
                    ['rule' => 'email', 'message' => 'Email must be a valid email address.'],
                ],
                'phone' => [
                    ['rule' => 'required', 'message' => 'Phone Number is Required.'],
                    ['rule' => 'numeric', 'message' => 'Only Numbers are allowed.'],
                ],
                'gender' => [
                    ['rule' => 'required', 'message' => 'Gender is Required.'],
                ],
                'terms' => [
                    ['rule' => 'required', 'message' => 'Terms and conditions are required'],
                ],
                'password' => [
                    ['rule' => 'required', 'message' => 'Password is Required.'],
                    ['rule' => 'securePassword', 'message' => 'Password is not strong enough.'],
                ],
                'confirm_password' => [
                    ['rule' => 'required', 'message' => 'Confirm Password is Required.'],
                ],
            ],
            'login' => [
                'email' => [
                    ['rule' => 'required', 'message' => 'Email is required.'],
                    ['rule' => 'email', 'message' => 'Email must be a valid email address.'],
                ],
                'password' => [
                    ['rule' => 'required', 'message' => 'Password is Required.'],
                ],
            ],
            'admin' => [
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
                    ['rule' => 'email', 'message' => 'Email must be a valid email address.'],
                ],
                'phone' => [
                    ['rule' => 'required', 'message' => 'Phone Number is Required.'],
                    ['rule' => 'numeric', 'message' => 'Only Numbers are allowed.'],
                ],
                'gender' => [
                    ['rule' => 'required', 'message' => 'Gender is Required.'],
                ],
                'role' => [
                    ['rule' => 'required', 'message' => 'Role is Required.'],
                ],
                'terms' => [
                    ['rule' => 'required', 'message' => 'Terms and conditions are required'],
                ],
            ],
        ];

        // Determine the scenario to use or fallback to the default
        $scenario = $scenario ?: $this->scenario;

        return $rules[$scenario] ?? [];
    }

    public function beforeSave(): void
    {
    }

    /**
     * This are just samples.
     *
     */
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