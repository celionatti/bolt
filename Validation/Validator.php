<?php

declare(strict_types=1);

/**
 * ==================================================
 * ==================               =================
 * Validator Class
 * ==================               =================
 * ==================================================
 */

namespace celionatti\Bolt\Validation;

use celionatti\Bolt\BoltException\BoltException;


class Validator
{
    protected $data;
    protected $rules;
    protected $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function passes()
    {
        foreach ($this->rules as $field => $rules) {
            $rules = explode('|', $rules);
            foreach ($rules as $rule) {
                $method = 'validate' . ucfirst($rule);
                if (!method_exists($this, $method)) {
                    throw new BoltException("Validation rule {$rule} does not exist.");
                }
                $this->$method($field);
            }
        }
        return empty($this->errors);
    }

    public function fails()
    {
        return !$this->passes();
    }

    public function errors()
    {
        return $this->errors;
    }

    protected function validateRequired($field)
    {
        if (!isset($this->data[$field]) || empty($this->data[$field])) {
            $this->errors[$field][] = "{$field} is required.";
        }
    }

    protected function validateEmail($field)
    {
        if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "{$field} must be a valid email address.";
        }
    }
}
