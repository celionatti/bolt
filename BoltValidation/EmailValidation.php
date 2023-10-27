<?php

declare(strict_types=1);

/**
 * ================================================
 * ================             ===================
 * Email Validation
 * ================             ===================
 * ================================================
 */

namespace celionatti\Bolt\BoltValidation;

class EmailValidation extends BoltValidator
{
    public function runValidation()
    {
        $email = $this->_obj->{$this->field};
        $pass = true;
        if (!empty($email)) {
            $pass = filter_var($email, FILTER_VALIDATE_EMAIL);
        }
        return $pass;
    }
}
