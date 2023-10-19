<?php

declare(strict_types=1);

/**
 * ==================================================
 * ==================               =================
 * Model Class
 * ==================               =================
 * ==================================================
 */

namespace Bolt\Bolt;

use Bolt\Bolt\BoltException\BoltException;

class Model
{
    public array $errors = [];

    public function validate($data)
    {
        foreach ($this->rules() as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $ruleName = $rule['rule'];
                $params = $rule['params'] ?? [];
                $message = $rule['message'] ?? null;
                $conditions = $rule['conditions'] ?? null;

                if (method_exists($this, $ruleName)) {
                    if (!$this->checkConditions($conditions, $data)) {
                        continue;
                    }

                    $isValid = $this->$ruleName($data[$field], ...$params);

                    if (!$isValid) {
                        $this->addError($field, $ruleName, $params, $message);
                    }
                } elseif (is_callable($ruleName)) {
                    $isValid = $ruleName($data[$field], $data);

                    if (!$isValid) {
                        $this->addError($field, 'custom', [], $message);
                    }
                } else {
                    throw new BoltException("Validation rule '$ruleName' does not exist.");
                }
            }
        }

        return empty($this->errors);
    }

    public function rules()
    {
        return [];
    }

    private function checkConditions($conditions, $data)
    {
        if ($conditions === null) {
            return true;
        }

        foreach ($conditions as $condition) {
            $field = $condition['field'];
            $value = $condition['value'];

            if ($data[$field] != $value) {
                return false;
            }
        }

        return true;
    }

    private function addError($field, $rule, $params = [], $message = null)
    {
        if ($message === null) {
            $message = "Field '$field' failed validation rule '$rule' with parameters: " . implode(', ', $params);
        }
        $this->errors[$field] = $message;
    }

    public function createError($field, $message)
    {
        $this->errors[$field] = $message;
    }

    private function required($value)
    {
        return !empty($value);
    }

    private function maxLength($value, $max)
    {
        return strlen($value) <= $max;
    }

    private function minValue($value, $min)
    {
        return $value >= $min;
    }

    private function maxValue($value, $max)
    {
        return $value <= $max;
    }

    private function email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function numeric($value)
    {
        return is_numeric($value);
    }

    private function alpha($value)
    {
        return ctype_alpha($value);
    }

    private function alphanumeric($value)
    {
        return ctype_alnum($value);
    }

    private function regex($value, $pattern)
    {
        return preg_match($pattern, $value) === 1;
    }

    private function url($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function date($value, $format = 'Y-m-d')
    {
        $date = \DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value;
    }

    private function alphaNumericWithSpaces($value)
    {
        return preg_match('/^[a-zA-Z0-9\s]+$/', $value);
    }

    private function inList($value, $list)
    {
        return in_array($value, $list);
    }

    private function securePassword($value, $minLength = 6, $requireUppercase = true, $requireLowercase = true, $requireDigits = true, $requireSpecialChars = false)
    {
        if (strlen($value) < $minLength) {
            return false;
        }

        if ($requireUppercase && !preg_match('/[A-Z]/', $value)) {
            return false;
        }

        if ($requireLowercase && !preg_match('/[a-z]/', $value)) {
            return false;
        }

        if ($requireDigits && !preg_match('/[0-9]/', $value)) {
            return false;
        }

        if ($requireSpecialChars && !preg_match('/[^A-Za-z0-9]/', $value)) {
            return false;
        }

        return true;
    }

    private function passwordsMatch($password, $confirmPassword)
    {
        return $password !== $confirmPassword;
    }

    public function passwordsMatchValidation($password, $confirmPassword, $msgParam = 'confirm_password')
    {
        if ($password !== $confirmPassword) {
            $this->addError($msgParam, '', [],'Passwords do not match.');
            return false;
        }

        return true;
    }

    private function unique($value, $field, $data)
    {
        // Check if the value is unique among existing records in a database, for example.
        // You'd need to implement this logic based on your application's data source.
        return true; // Replace with actual implementation
    }

    private function customValidationRule($value)
    {
        // Implement a custom validation rule here based on your specific requirements.
        // Return true if the value passes the validation, or false otherwise.
    }

    // Add more validation rules as needed

    public function getErrors()
    {
        return $this->errors;
    }
}
