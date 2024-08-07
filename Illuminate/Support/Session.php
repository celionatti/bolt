<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - Session Class =============
 * ==================================
 */

namespace celionatti\Bolt\Illuminate\Support;

class Session
{

    protected const FLASH_KEY = '__flash_messages';
    protected const FORM_FLASH_KEY = '__form_messages';
    protected const EXPIRATION_KEY = '__session_expiration';

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
        session_unset();
        session_destroy();
    }

    public function setFlash(string $key, $value): void
    {
        $flashMessages = $this->get(self::FLASH_KEY, []);
        $flashMessages[$key] = $value;
        $this->set(self::FLASH_KEY, $flashMessages);
    }

    public function getFlash(string $key, $default = false)
    {
        $flashMessages = $this->get(self::FLASH_KEY, []);
        $value = $flashMessages[$key] ?? $default;
        unset($flashMessages[$key]);
        $this->set(self::FLASH_KEY, $flashMessages);
        return $value;
    }

    public function setFormMessage($value): void
    {
        $this->set(self::FORM_FLASH_KEY, $value) ?? null;
    }

    public function getFormMessage()
    {
        $value = $this->get(self::FORM_FLASH_KEY);
        $this->remove(self::FORM_FLASH_KEY);
        return $value ?? [];
    }

    public function setExpiration(int $minutes): void
    {
        $expirationTime = time() + ($minutes * 60);
        $this->set(self::EXPIRATION_KEY, $expirationTime);
    }

    public function getExpiration(): ?int
    {
        return $this->get(self::EXPIRATION_KEY);
    }

    protected function checkExpiration(): void
    {
        $expirationTime = $this->getExpiration();
        if ($expirationTime !== null && time() > $expirationTime) {
            $this->destroy();
        }
    }

    // ... (existing methods)

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public function getAll(): array
    {
        return $_SESSION;
    }

    public function hasFlash(string $key): bool
    {
        $flashMessages = $this->get(self::FLASH_KEY, []);
        return isset($flashMessages[$key]);
    }

    public function clearAllFlashes(): void
    {
        $this->set(self::FLASH_KEY, []);
    }

    public function getFlashes(): array
    {
        return $this->get(self::FLASH_KEY, []);
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

    public function unsetArray(array $keys): void
    {
        foreach ($keys as $key) {
            unset($_SESSION[$key]);
        }
    }
}
