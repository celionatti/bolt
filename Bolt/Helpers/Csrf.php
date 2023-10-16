<?php

declare(strict_types=1);

/**
 * ============================================
 * Bolt - Csrf ================================
 * ============================================
 */

namespace Bolt\Bolt\Helpers;

class Csrf
{
    protected $token = "";
    protected $tokenName = '_csrf_token';
    protected $tokenLifetime = 3600; // Token lifetime in seconds (default: 1 hour, 3600 seconds)

    public function __construct()
    {
        // Initialize the session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Generate or retrieve the CSRF token
        if (!isset($_SESSION[$this->tokenName])) {
            $this->generateToken();
        } else {
            $tokenData = $_SESSION[$this->tokenName];
            if ($tokenData['expiration'] < time()) {
                $this->regenerateToken();
            } else {
                $this->token = $tokenData['value'];
            }
        }
    }

    public function generateToken()
    {
        // Generate a random token
        $token = bin2hex(random_bytes(32));

        // Store the token in the session with an expiration timestamp
        $_SESSION[$this->tokenName] = [
            'value' => $token,
            'expiration' => time() + $this->tokenLifetime,
        ];

        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function validateToken($submittedToken, $requestMethod = 'POST')
    {
        // Check if the token is expired
        if (
            isset($_SESSION[$this->tokenName]['expiration']) &&
            $_SESSION[$this->tokenName]['expiration'] < time()
        ) {
            $this->regenerateToken();
            return false;
        }

        // Validate the submitted token based on the request method
        if ($requestMethod === 'POST' || $requestMethod === 'PUT' || $requestMethod === 'DELETE') {
            return hash_equals($this->token, $submittedToken);
        }

        // GET and other request methods are considered safe and don't require token validation
        return true;
    }

    public function regenerateToken()
    {
        //unset the previous token
        $this->unsetToken();
        // Generate a new token and update the expiration timestamp
        $this->generateToken();
    }

    public function unsetToken()
    {
        unset($_SESSION[$this->tokenName]);
    }

    public function setTokenLifetime($seconds)
    {
        $this->tokenLifetime = $seconds;
    }

    public function getTokenLifetime()
    {
        return $this->tokenLifetime;
    }
}
