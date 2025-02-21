<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - SoftDeletesInterface ========
 * ====================================
 */

namespace celionatti\Bolt\Database\Model\Interfaces;

interface SoftDeletesInterface
{
    public function softDelete(): bool;
    public function restore(): bool;
    public function forceDelete(): bool;
    public function trashed(): bool;
}