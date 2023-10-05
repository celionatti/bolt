<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - Session Class =============
 * ==================================
 */

namespace Bolt\Bolt;

class Session
{

    protected $flashKey = '__flash_messages';
    protected $expirationKey = '__session_expiration';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkExpiration();
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        session_destroy();
    }

    public function setFlash(string $key, $value): void
    {
        $flashMessages = $this->get($this->flashKey, []);
        $flashMessages[$key] = $value;
        $this->set($this->flashKey, $flashMessages);
    }

    public function getFlash(string $key, $default = null)
    {
        $flashMessages = $this->get($this->flashKey, []);
        $value = $flashMessages[$key] ?? $default;
        unset($flashMessages[$key]);
        $this->set($this->flashKey, $flashMessages);
        return $value;
    }

    public function setExpiration(int $minutes): void
    {
        $expirationTime = time() + ($minutes * 60);
        $this->set($this->expirationKey, $expirationTime);
    }

    public function getExpiration(): ?int
    {
        return $this->get($this->expirationKey);
    }

    protected function checkExpiration(): void
    {
        $expirationTime = $this->getExpiration();
        if ($expirationTime !== null && time() > $expirationTime) {
            $this->destroy();
        }
    }

    // ... (existing methods)

    public function regenerateId(): void
    {
        session_regenerate_id(true);
    }

    public function clearAll(): void
    {
        session_unset();
    }

    public function getAll(): array
    {
        return $_SESSION;
    }

    public function hasFlash(string $key): bool
    {
        $flashMessages = $this->get($this->flashKey, []);
        return isset($flashMessages[$key]);
    }

    public function clearAllFlashes(): void
    {
        $this->set($this->flashKey, []);
    }

    public function getFlashes(): array
    {
        return $this->get($this->flashKey, []);
    }

    public function setArray(array $data): void
    {
        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    public function getSessionId(): string
    {
        return session_id();
    }

    // ... (existing methods)

    public function unsetArray(array $keys): void
    {
        foreach ($keys as $key) {
            unset($_SESSION[$key]);
        }
    }

    public function setAuthenticatedUser(array $user): void
    {
        $this->set('user', $user);
    }

    public function getAuthenticatedUser()
    {
        return $this->get('user');
    }

    public function isAuthenticated(): bool
    {
        return $this->has('user');
    }

    public function clearAuthenticatedUser(): void
    {
        $this->remove('user');
    }
}
