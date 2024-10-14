<?php

declare(strict_types=1);

/**
 * =====================================================
 * =====================        ========================
 * Auth
 * =====================        ========================
 * =====================================================
 */

namespace celionatti\Bolt\Authentication;

use Exception;
use celionatti\Bolt\Database\Database;
use celionatti\Bolt\Illuminate\Support\RateLimits;

class Auth
{
    protected Database $db;
    protected RateLimits $rateLimiter;
    protected string $identifierColumn = 'email'; // Default identifier is email
    protected array $errors = []; // Store form errors

    /**
     * Auth constructor.
     *
     * @param RateLimits $rateLimiter
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->rateLimiter = new RateLimits();
    }

    public function rateLimiter()
    {
        return $this->rateLimiter;
    }

    /**
     * Attempt to log in a user with the given credentials.
     *
     * @param array $credentials
     * @param bool $rememberMe
     * @return bool
     */
    public function login(array $credentials, bool $rememberMe = false): bool
    {
        $identifier = $credentials[$this->identifierColumn];
        $password = $credentials['password'];

        if ($this->rateLimiter->isLocked($identifier)) {
            $this->addError("This account is locked. Please wait {$this->rateLimiter->retryDelayMinutes($identifier)} minutes.");
            return false;
        }

        if ($this->rateLimiter->tooManyAttempts($identifier)) {
            $this->rateLimiter->lock($identifier);
            $this->addError('Too many login attempts. Please try again later.');
            return false;
        }

        $user = $this->getUserByIdentifier($identifier);

        if (!$user || !$this->verifyPassword($password, $user->password)) {
            $this->rateLimiter->hit($identifier);
            $this->addError('Invalid credentials.');
            return false;
        }

        $this->rateLimiter->clearAttempts($identifier);

        // Login the user
        $this->setUserSession($user);

        // Handle "remember me" functionality
        if ($rememberMe) {
            $this->setRememberMeToken($user);
        }

        return true;
    }

    /**
     * Log out the authenticated user and clear remember-me cookie.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->clearUserSession();
        $this->clearRememberMeToken();
    }

    /**
     * Get form errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Lock the user manually.
     *
     * @param string $identifier
     * @return void
     */
    public function lock(string $identifier): void
    {
        $this->rateLimiter->lock($identifier);
    }

    /**
     * Get user by identifier (e.g., email).
     *
     * @param string $identifier
     * @return object|null
     */
    protected function getUserByIdentifier(string $identifier): ?object
    {
        $result = $this->db->query("
            SELECT *
            FROM users
            WHERE {$this->identifierColumn} = :identifier
            LIMIT 1
        ", ['identifier' => $identifier]);

        return $result[0] ?? null;
    }

    /**
     * Verify that the given password matches the stored hash.
     *
     * @param string $password
     * @param string $hashedPassword
     * @return bool
     */
    protected function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }

    /**
     * Set the authenticated user in the session.
     *
     * @param object $user
     * @return void
     */
    protected function setUserSession(object $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
    }

    /**
     * Clear the authenticated user's session.
     *
     * @return void
     */
    protected function clearUserSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['user_id'], $_SESSION['user_name']);
        session_destroy();
    }

    /**
     * Set a remember-me token in a cookie.
     *
     * @param object $user
     * @return void
     */
    protected function setRememberMeToken(object $user): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = password_hash($token, PASSWORD_DEFAULT);

        // Store hashed token in the database
        $this->db->query("
            UPDATE users
            SET remember_token = :token
            WHERE id = :id
        ", ['token' => $hashedToken, 'id' => $user->id]);

        // Set the cookie with the raw token
        setcookie('remember_me', "{$user->id}|{$token}", time() + (86400 * 30), "/", "", true, true);
    }

    /**
     * Clear the remember-me cookie and token in the database.
     *
     * @return void
     */
    protected function clearRememberMeToken(): void
    {
        if (isset($_COOKIE['remember_me'])) {
            list($userId, $token) = explode('|', $_COOKIE['remember_me']);

            // Remove the token from the database
            $this->db->query("
                UPDATE users
                SET remember_token = NULL
                WHERE id = :id
            ", ['id' => $userId]);

            // Expire the cookie
            setcookie('remember_me', '', time() - 3600, "/", "", true, true);
        }
    }

    /**
     * Check if the user is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get the authenticated user's ID.
     *
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Add error to the form errors array.
     *
     * @param string $error
     * @return void
     */
    protected function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Set a custom identifier column (e.g., 'username').
     *
     * @param string $column
     * @return void
     */
    public function setIdentifierColumn(string $column): void
    {
        $this->identifierColumn = $column;
    }
}
