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
    public function __construct(
        string $message,
        int $code = 0,
        string $errorLevel = 'warning',
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $errorLevel, $previous);
    }
}
