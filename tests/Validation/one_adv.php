<?php

class DataValidator {
    private $errors = [];

    public function validate($data, $rules) {
        foreach ($rules as $field => $fieldRules) {
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
                    throw new Exception("Validation rule '$ruleName' does not exist.");
                }
            }
        }

        return empty($this->errors);
    }

    private function addError($field, $rule, $params = [], $message = null) {
        if ($message === null) {
            $message = "Field '$field' failed validation rule '$rule' with parameters: " . implode(', ', $params);
        }
        $this->errors[] = $message;
    }

    private function checkConditions($conditions, $data) {
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
}

// Example usage:
$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 25,
    'is_student' => true,
];

$rules = [
    'name' => [
        ['rule' => 'required', 'message' => 'Name is required.'],
        ['rule' => 'maxLength', 'params' => [255], 'message' => 'Name is too long.'],
    ],
    'email' => [
        ['rule' => 'required', 'message' => 'Email is required.'],
        ['rule' => 'maxLength', 'params' => [100], 'message' => 'Email is too long.'],
    ],
    'age' => [
        ['rule' => 'required', 'message' => 'Age is required.', 'conditions' => [['field' => 'is_student', 'value' => false]]],
        ['rule' => 'minValue', 'params' => [18], 'message' => 'Age must be at least 18.', 'conditions' => [['field' => 'is_student', 'value' => false]]],
    ],
    'is_student' => [
        ['rule' => 'required', 'message' => 'Student status is required.'],
    ],
    'password' => [
        ['rule' => 'required', 'message' => 'Password is required.'],
        ['rule' => 'passwordStrength', 'message' => 'Password is not strong enough.'],
    ],
    'password_confirmation' => [
        ['rule' => 'required', 'message' => 'Password confirmation is required.'],
        ['rule' => 'passwordsMatch', 'message' => 'Passwords do not match.'],
    ],
    'custom_field' => [
        ['rule' => function ($value) { return strpos($value, 'custom') !== false; }, 'message' => 'Value must contain "custom".'],
    ],
];


$validator = new DataValidator();
if ($validator->validate($data, $rules)) {
    echo "Data is valid!";
} else {
    echo "Validation errors:";
    print_r($validator->getErrors());
}
