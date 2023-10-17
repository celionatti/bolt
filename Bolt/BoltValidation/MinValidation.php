<?php

declare(strict_types=1);

/**
 * ================================================
 * ================             ===================
 * Min Validation
 * ================             ===================
 * ================================================
 */

namespace Bolt\Bolt\BoltValidation;

class MinValidation extends BoltValidator
{
    public function runValidation(): bool
    {
        $value = $this->_obj->{$this->field};
        return strlen($value) >= $this->rule;
    }
}
