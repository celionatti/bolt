<?php

declare(strict_types=1);

namespace Bolt\Bolt\Authentication;

use PDO;

class Authentication
{
    protected $db;
    protected $rateLimiter;

    public function __construct(PDO $db, RateLimiter $rateLimiter)
    {
        $this->db = $db;
        $this->rateLimiter = $rateLimiter;
    }

    public function login($email, $password)
    {
        // Check if the account is blocked. You can implement this logic in your user table.
        $isAccountBlocked = $this->isAccountBlocked($email);

        if ($isAccountBlocked) {
            return "Account is blocked. Please contact support.";
        }

        // If the account is not blocked, proceed with login attempt.
        if ($this->authenticate($email, $password)) {
            // Successful login. Reset the login attempts.
            $this->rateLimiter->resetAttempts($email);
            return "Login successful.";
        } else {
            // Failed login attempt. Increment login attempts.
            $this->rateLimiter->incrementAttempts($email);
            return "Login failed. Please try again.";
        }
    }

    // You can implement your own authentication logic here.
    private function authenticate($email, $password)
    {
        // Replace with your actual authentication logic.
        // This method should return true if authentication is successful and false otherwise.
        return true; // Placeholder for a successful login.
    }

    // Check if the account is blocked. You can implement this logic in your user table.
    private function isAccountBlocked($email)
    {
        // Implement the logic to check if the account is blocked (e.g., by checking a 'blocked' field in your user table).
        return false; // Placeholder for an unblocked account.
    }
}
