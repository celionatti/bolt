<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - HandlesValidation ===========
 * ====================================
 */

namespace celionatti\Bolt\Database\Model\Traits;

trait HandlesValidation
{
    protected array $rules = [];
    protected array $validationErrors = [];

    public function validate(): bool
    {
        $validator = new Validator(
            $this->attributes,
            $this->rules,
            $this->getCasts()
        );

        if ($validator->fails()) {
            $this->validationErrors = $validator->errors();
            return false;
        }

        return true;
    }

    public function errors(): array
    {
        return $this->validationErrors;
    }
}