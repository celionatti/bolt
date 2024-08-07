<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - SessionInterface ==========
 * ==================================
 */

namespace celionatti\Bolt\Sessions;

interface SessionInterface
{
    public function start(): void;
    public function set(string $key, $value): void;
    public function get(string $key, $default = null);
    public function has(string $key): bool;
    public function remove(string $key): void;
    public function destroy();
    public function regenerate();
    public function flash(string $key, $value);
    public function getFlash(string $key, $default = null);
    public function keepFlash(string $key);
}