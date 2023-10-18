<?php

declare(strict_types=1);

/**
 * ================================================
 * ================             ===================
 * Required Validation
 * ================             ===================
 * ================================================
 */

namespace Bolt\Bolt\BoltValidation;

class RequiredValidation extends BoltValidator
{
    public function runValidation(): bool
    {
        $value = trim($this->_obj->{$this->field});
        // return $value != '';
        return "" !== $value;
    }
}