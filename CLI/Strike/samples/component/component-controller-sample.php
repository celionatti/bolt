<?php

declare(strict_types=1);

/**
 * Summary of namespace YourNamespace\Components
 */

namespace PhpStrike\app\components;

use celionatti\Bolt\Component\Component;

class {CLASSNAME} extends Component
{
    protected function mount(): void
    {
        // This method will run during instantiation
    }

    public function getMessage()
    {
        return $this->data['message'] ?? 'Default message';
    }
}
