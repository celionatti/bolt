<?php

declare(strict_types=1);

/**
 * ==============================================
 * ================         =====================
 * BoltRuntimeException Class.
 * ================         =====================
 * ==============================================
 */

namespace celionatti\Bolt\BoltException;

use Throwable;

class BoltInvalidArgumentException extends BoltException
{
    public function __construct(
        string $message,
        int $code = 0,
        string $errorLevel = 'error',
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $errorLevel, $previous);
    }
}