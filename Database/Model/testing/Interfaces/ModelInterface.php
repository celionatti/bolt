<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - ModelInterface ==============
 * ====================================
 */

namespace celionatti\Bolt\Database\Model\Interfaces;

interface ModelInterface
{
    public function save(array $options = []): bool;
    public function delete(): bool;
    public static function find(mixed $id): ?self;
    public function toArray(): array;
}