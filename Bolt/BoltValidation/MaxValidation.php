<?php

declare(strict_types=1);

/**
 * ================================================
 * ================             ===================
 * Max Validation
 * ================             ===================
 * ================================================
 */

namespace Bolt\Bolt\BoltValidation;

class MaxValidation extends BoltValidator
{
    public function runValidation(): bool
    {
        $value = $this->_obj->{$this->field};
        return strlen($value) <= $this->rule;
    }
}
