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


class Model
{
    const RULE_REQUIRED = 'required';
    const RULE_EMAIL = 'email';
    const RULE_MIN = 'min';
    const RULE_MAX = 'max';
    const RULE_MATCH = 'match';
    const RULE_UNIQUE = 'unique';

    public array $errors = [];

    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function rules()
    {
        return [];
    }

    public function validate($data)
    {
        foreach ($this->rules() as $attribute => $rules) {
            if (!empty($data)) {
                foreach ($rules as $rule) {
                    $ruleName = $rule;
                    if (!is_string($rule)) {
                        $ruleName = $rule[0];
                    }
                    var_dump($ruleName);
                }
                foreach ($data as $key => $item) {
                    $value = $this->{$attribute};
                    var_dump($key);
                }
            }
        }
        return empty($this->errors);
    }

    public function errorMessages()
    {
        return [
            self::RULE_REQUIRED => 'This field is required',
            self::RULE_EMAIL => 'This field must be valid email address',
            self::RULE_MIN => 'Min length of this field must be {min}',
            self::RULE_MAX => 'Max length of this field must be {max}',
            self::RULE_MATCH => 'This field must be the same as {match}',
            self::RULE_UNIQUE => 'Record with with this {field} already exists',
        ];
    }

    public function errorMessage($rule)
    {
        return $this->errorMessages()[$rule];
    }

    protected function addErrorByRule(string $attribute, string $rule, $params = [])
    {
        $params['field'] ??= $attribute;
        $errorMessage = $this->errorMessage($rule);
        foreach ($params as $key => $value) {
            $errorMessage = str_replace("{{$key}}", $value, $errorMessage);
        }
        $this->errors[$attribute] = $errorMessage;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
