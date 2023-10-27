<?php

declare(strict_types=1);

/**
 * ================================================
 * ================             ===================
 * Numeric Validation
 * ================             ===================
 * ================================================
 */

namespace celionatti\Bolt\BoltValidation;

class NumericValidation extends BoltValidator
{
    public function runValidation(): bool
    {
        $value = $this->_obj->{$this->field};
        $pass = true;
        if (!empty($value)) {
            $pass = is_numeric($value) && preg_match("/^[0-9]+$/", $value);
        }
        return $pass;
    }
}
