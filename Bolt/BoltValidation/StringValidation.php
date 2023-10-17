<?php

declare(strict_types=1);

/**
 * ================================================
 * ================             ===================
 * String Validation
 * ================             ===================
 * ================================================
 */

namespace Bolt\Bolt\BoltValidation;

class StringValidation extends BoltValidator
{
    public function runValidation(): bool|int
    {
        $value = $this->_obj->{ $this->field};
        $pass = true;
        if (!empty($value)) {
            $pass = preg_match("/^[a-zA-Z]+$/", $value);
        }
        return $pass;
    }
}
