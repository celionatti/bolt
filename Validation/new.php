<?php

declare(strict_types=1);

namespace celionatti\Bolt\Validation;

use celionatti\Bolt\Database\Database;
use celionatti\Bolt\BoltException\BoltException;

class Validator
{
    protected $data;
    protected $rules;
    protected $errors = [];
    protected static $customValidators = [];

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
                $parameters = explode(':', $rule, 2); // Split into max two parts
                $ruleName = $parameters[0];
                $param = $parameters[1] ?? null;

                $method = 'validate' . ucfirst($ruleName);
                if (method_exists($this, $method)) {
                    $this->$method($field, $param);
                } elseif (isset(self::$customValidators[$ruleName])) {
                    call_user_func(self::$customValidators[$ruleName], $this, $field, $param);
                } else {
                    throw new BoltException("Validation rule {$ruleName} does not exist.");
                }
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
        if (!isset($this->data[$field]) {
            $this->addError($field, 'required');
        } elseif (is_string($this->data[$field]) {
            if (trim($this->data[$field]) === '') {
                $this->addError($field, 'required');
            }
        } elseif (empty($this->data[$field])) {
            $this->addError($field, 'required');
        }
    }

    protected function validateEmail($field)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
        }
    }

    protected function validateString($field)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        if (!is_string($this->data[$field])) {
            $this->addError($field, 'string');
        }
    }

    protected function validateMin($field, $value)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        if (strlen((string)$this->data[$field]) < $value) {
            $this->addError($field, 'min', ['value' => $value]);
        }
    }

    protected function validateMax($field, $value)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        if (strlen((string)$this->data[$field]) > $value) {
            $this->addError($field, 'max', ['value' => $value]);
        }
    }

    protected function validateNumeric($field)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        if (!is_numeric($this->data[$field])) {
            $this->addError($field, 'numeric');
        }
    }

    protected function validateBoolean($field)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        if (!is_bool($this->data[$field])) {
            $this->addError($field, 'boolean');
        }
    }

    protected function validateArray($field)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        if (!is_array($this->data[$field])) {
            $this->addError($field, 'array');
        }
    }

    protected function validateDatetime($field)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        if (!strtotime($this->data[$field])) {
            $this->addError($field, 'datetime');
        }
    }

    protected function validateConfirmed($field)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        $confirmationField = "{$field}_confirm";
        $value = $this->data[$field];

        if (!isset($this->data[$confirmationField]) {
            $this->addError($field, 'confirmed');
        } elseif ($value !== $this->data[$confirmationField]) {
            $this->addError($field, 'confirmed');
        }
    }

    protected function validateUnique($field, $tableColumnCondition)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        $parts = explode(',', $tableColumnCondition);
        list($table, $column) = explode('.', $parts[0]);
        $value = $this->data[$field];

        $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = :value";
        $params = ['value' => $value];

        if (isset($parts[1])) {
            $additionalCondition = trim($parts[1]);
            if (preg_match('/(\w+)\s*(=|!=|<|>|<=|>=)\s*(.+)/', $additionalCondition, $matches)) {
                $conditionField = $matches[1];
                $conditionOperator = $matches[2];
                $conditionValue = trim($matches[3], "'\""); // Remove quotes

                $query .= " AND {$conditionField} {$conditionOperator} :{$conditionField}";
                $params[$conditionField] = is_numeric($conditionValue) ? (float)$conditionValue : $conditionValue;
            } else {
                throw new BoltException("Invalid condition format in unique validation.");
            }
        }

        $stmt = Database::getInstance()->getConnection()->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($result->count > 0) {
            $this->addError($field, 'unique');
        }
    }

    protected function validateIn($field, $values)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        $allowedValues = explode(',', $values);
        if (!in_array($this->data[$field], $allowedValues)) {
            $this->addError($field, 'in', ['values' => implode(', ', $allowedValues)]);
        }
    }

    protected function validatePassword($field)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        $value = $this->data[$field];
        if (!preg_match('/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d@_-]+$/', $value)) {
            $this->addError($field, 'password');
        }
    }

    // Example new validators:
    protected function validateUrl($field)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        if (!filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url');
        }
    }

    protected function validateRegex($field, $pattern)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        if (!preg_match($pattern, $this->data[$field])) {
            $this->addError($field, 'regex');
        }
    }

    protected function validateDate($field, $format)
    {
        if (!$this->isFieldPresentAndFilled($field)) return;

        $date = \DateTime::createFromFormat($format, $this->data[$field]);
        if (!$date || $date->format($format) !== $this->data[$field]) {
            $this->addError($field, 'date', ['format' => $format]);
        }
    }

    // Helper methods
    protected function isFieldPresentAndFilled($field)
    {
        return isset($this->data[$field]) && !is_null($this->data[$field]) && $this->data[$field] !== '';
    }

    protected function addError($field, $rule, $params = [])
    {
        $messages = [
            'required' => "{field} is required.",
            'email' => "{field} must be a valid email address.",
            'string' => "{field} must be a string.",
            'min' => "{field} must be at least {value} characters.",
            'max' => "{field} must not exceed {value} characters.",
            'numeric' => "{field} must be a number.",
            'boolean' => "{field} must be true or false.",
            'array' => "{field} must be an array.",
            'datetime' => "{field} must be a valid datetime.",
            'confirmed' => "{field} confirmation does not match.",
            'unique' => "{field} must be unique.",
            'in' => "{field} must be one of {values}.",
            'password' => "{field} must contain at least one uppercase letter, one number, and allowed symbols (@, _, -).",
            'url' => "{field} must be a valid URL.",
            'regex' => "{field} format is invalid.",
            'date' => "{field} must be a valid date in the format {format}.",
        ];

        $message = $messages[$rule] ?? "{field} is invalid.";
        $message = str_replace('{field}', $this->formatFieldName($field), $message);

        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        $this->errors[$field] = $message;
    }

    protected function formatFieldName(string $field): string
    {
        return ucwords(str_replace('_', ' ', $field));
    }

    public static function addCustomValidator($name, callable $callback)
    {
        self::$customValidators[$name] = $callback;
    }
}