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
    /**
     * here is how to define const
     */
    const GREETING = "Bolt {CLASSNAME}";

    /**
     * Also for defining variables
     */
    public string $name = "Bolt";
    public string $version;
    private $isInsertion = false; // Default to the login scenario

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

        // Additional custom initialization code
    }

    public static function tableName():string
    {
        return "{TABLENAME}";
    }

    public function setIsInsertionScenario($isInsertion)
    {
        $this->isInsertion = $isInsertion;
    }

    public function isInsertionScenario(): bool
    {
        return $this->isInsertion;
    }

    /**
     * Validation Mode -- Option One.
     *
     */
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

    /**
     * Validation Mode -- Option Two.
     *
     */
    // public function rules(): array
    // {
    //     return [
    //         'username' => [
    //             ['rule' => 'required', 'message' => 'Username is required.'],
    //             ['rule' => 'maxLength', 'params' => [15], 'message' => 'Username characters is too long.'],
    //             ['rule' => 'alpha', 'message' => 'Only Alphabet characters are allowed.'],
    //         ],
    //         'name' => [
    //             ['rule' => 'required', 'message' => 'Name is required.'],
    //             ['rule' => 'maxLength', 'params' => [255], 'message' => 'Name characters is too long.'],
    //         ],
    //         'email' => [
    //             ['rule' => 'required', 'message' => 'Email is required.'],
    //             ['rule' => 'maxLength', 'params' => [100], 'message' => 'Email is too long.'],
    //             ['rule' => 'email', 'message' => 'Email must be valid email address.'],
    //         ],
    //         'phone' => [
    //             ['rule' => 'required', 'message' => 'Phone Number is Required.'],
    //             ['rule' => 'numeric', 'message' => 'Only Numbers are allowed.'],
    //         ],
    //         'password' => [
    //             ['rule' => 'required', 'message' => 'Password is Required.'],
    //             ['rule' => 'securePassword', 'message' => 'Password is not strong enough.'],
    //         ],
    //         'confirm_password' => [
    //             ['rule' => 'required', 'message' => 'Confirm Password is Required.'],
    //             ['rule' => 'passwordsMatch', 'params' => ['password'], 'message' => 'Passwords do not match.'],
    //         ],
    //     ];
    // }

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

    public function others()
    {
        
    }
}