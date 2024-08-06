<?php

declare(strict_types=1);

/**
 * ============================================
 * Bolt - Csrf ================================
 * ============================================
 */

namespace celionatti\Bolt\Helpers\CSRF;

class Csrf
{
    private $sessionKey = '_csrf_token';
    private $sessionExpiryKey = '_csrf_token_expiry';
    private $tokenLifetime = 3600; // Token lifetime in seconds (e.g., 1 hour)

    public function __construct()
    {
        $this->generateToken();
    }

    public function generateToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[$this->sessionKey] = $token;
        $_SESSION[$this->sessionExpiryKey] = time() + $this->tokenLifetime;

        return $token;
    }

    public function getToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION[$this->sessionKey] ?? null;
    }

    public function validateToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $storedToken = $_SESSION[$this->sessionKey] ?? null;
        $expiryTime = $_SESSION[$this->sessionExpiryKey] ?? null;

        if (!$storedToken || !$expiryTime) {
            return false;
        }

        if (time() > $expiryTime) {
            // Token expired
            $this->clearToken();
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    private function clearToken()
    {
        unset($_SESSION[$this->sessionKey], $_SESSION[$this->sessionExpiryKey]);
    }
}