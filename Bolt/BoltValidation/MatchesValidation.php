<?php

declare(strict_types=1);

/**
 * ================================================
 * ================             ===================
 * Matches Validation
 * ================             ===================
 * ================================================
 */

namespace Bolt\Bolt\BoltValidation;

class MatchesValidation extends BoltValidator
{
    public function runValidation(): bool
    {
        $value = $this->_obj->{$this->field};
        return $value == $this->rule;
    }
}
