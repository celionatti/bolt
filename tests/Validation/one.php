<?php

class DataValidator {
    private $errors = [];

    public function validate($data, $rules) {
        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $ruleName = $rule['rule'];
                $params = $rule['params'] ?? [];

                if (method_exists($this, $ruleName)) {
                    $isValid = $this->$ruleName($data[$field], ...$params);

                    if (!$isValid) {
                        $this->addError($field, $ruleName, $params);
                    }
                } else {
                    throw new Exception("Validation rule '$ruleName' does not exist.");
                }
            }
        }

        return empty($this->errors);
    }

    private function addError($field, $rule, $params = []) {
        $this->errors[] = "Field '$field' failed validation rule '$rule' with parameters: " . implode(', ', $params);
    }

    private function required($value) {
        return !empty($value);
    }

    private function maxLength($value, $max) {
        return strlen($value) <= $max;
    }

    // Add more validation rules as needed

    public function getErrors() {
        return $this->errors;
    }
}

// Example usage:
$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
];

$rules = [
    'name' => [
        ['rule' => 'required'],
        ['rule' => 'maxLength', 'params' => [255]],
    ],
    'email' => [
        ['rule' => 'required'],
        ['rule' => 'maxLength', 'params' => [100]],
    ],
];

$validator = new DataValidator();
if ($validator->validate($data, $rules)) {
    echo "Data is valid!";
} else {
    echo "Validation errors:";
    print_r($validator->getErrors());
}
