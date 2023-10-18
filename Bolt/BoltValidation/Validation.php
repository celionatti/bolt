<?php

declare(strict_types=1);

/**
 * ==============================================
 * ==================           =================
 * Validation Class
 * ==================           =================
 * ==============================================
 */

namespace Bolt\Bolt\BoltValidation;

class Validation
{
    private $data;
    private $errors = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function validate()
    {
        $this->validateField('name', 'Name is required', '/^[\p{L} ]+$/u');
        $this->validateField('email', 'Invalid email address', FILTER_VALIDATE_EMAIL);
        $this->validateField('age', 'Age must be a number', FILTER_VALIDATE_INT);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function validateField($field, $errorMessage, $filter)
    {
        if (!isset($this->data[$field]) || empty($this->data[$field])) {
            $this->addError($field, $errorMessage);
        } else {
            $value = $this->data[$field];
            if (filter_var($value, $filter) === false) {
                $this->addError($field, $errorMessage);
            }
        }
    }

    private function addError($field, $errorMessage)
    {
        $this->errors[$field] = $errorMessage;
    }
}
