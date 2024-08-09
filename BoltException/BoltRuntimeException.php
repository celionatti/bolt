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


class BoltRuntimeException extends BoltException
{
    public function __construct(string $message, int $code = 0, string $level = "warning", array $errors = [], ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $level, $errors, $previous);
    }
}