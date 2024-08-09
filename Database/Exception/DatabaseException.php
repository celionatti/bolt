<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - Database Exception ========
 * ==================================
 */

namespace celionatti\Bolt\Database\Exception;

use Throwable;
use celionatti\Bolt\BoltException\BoltException;

class DatabaseException extends BoltException
{
    public function __construct(string $message, int $code = 0, string $level = "warning", array $errors = [], ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $level, $errors, $previous);
    }
}
