<?php

declare(strict_types=1);

/**
 * ==============================================
 * ================         =====================
 * SecurityException Class.
 * ================         =====================
 * ==============================================
 */

namespace celionatti\Bolt\Exceptions;

use Throwable;


class SecurityException extends BoltException
{
    public function __construct(string $message, int $code = 0, string $level = "warning", array $errors = [], ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $level, $errors, $previous);
    }
}