<?php

<?php

class Csrf_ty
{
    protected $token;
    protected $tokenName = '_csrf_token';
    protected $tokenLifetime = 3600; // Token lifetime in seconds (default: 1 hour, 1800: 30mins)

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
            $this->token = $_SESSION[$this->tokenName];
        }
    }

    public function generateToken()
    {
        // Generate a random token
        $this->token = bin2hex(random_bytes(32));

        // Store the token in the session with an expiration timestamp
        $_SESSION[$this->tokenName] = [
            'token' => $this->token,
            'expires' => time() + $this->tokenLifetime,
        ];
    }

    public function getToken()
    {
        return $this->token;
    }

    public function validateToken($submittedToken, $requestMethod = 'POST')
    {
        // Check if the token is expired
        if (isset($_SESSION[$this->tokenName]['expires']) &&
            $_SESSION[$this->tokenName]['expires'] < time()) {
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
        // Generate a new token and update the expiration timestamp
        $this->generateToken();
    }

    public function generateToken()
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[$this->tokenName] = [
            'value' => $token,
            'expiration' => time() + $this->tokenLifetime
        ];
        return $token;
    }

    public function getToken()
    {
        if (!isset($_SESSION[$this->tokenName])) {
            return $this->generateToken();
        }

        $tokenData = $_SESSION[$this->tokenName];
        if ($tokenData['expiration'] < time()) {
            $this->unsetToken();
            return $this->generateToken();
        }

        return $tokenData['value'];
    }

    public function validateToken($token)
    {
        if (!isset($_SESSION[$this->tokenName])) {
            return false;
        }

        $tokenData = $_SESSION[$this->tokenName];
        if ($tokenData['expiration'] < time()) {
            $this->unsetToken();
            return false;
        }

        return hash_equals($tokenData['value'], $token);
    }

    public function regenerateToken()
    {
        $this->unsetToken();
        return $this->generateToken();
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
