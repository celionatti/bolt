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

    /**
     * Auth constructor.
     *
     * @param Database $db
     * @param RateLimits $rateLimiter
     */
    public function __construct(Database $db, RateLimits $rateLimiter)
    {
        $this->db = $db;
        $this->rateLimiter = $rateLimiter;
    }

    /**
     * Attempt to log in a user with the given credentials.
     *
     * @param array $credentials
     * @return bool
     * @throws Exception
     */
    public function login(array $credentials): bool
    {
        $identifier = $credentials[$this->identifierColumn];

        // Check if the user is locked
        if ($this->rateLimiter->isLocked($identifier)) {
            throw new Exception("This account is locked. Please wait {$this->rateLimiter->retryDelayMinutes($identifier)} minutes.");
        }

        // Check if there are too many login attempts
        if ($this->rateLimiter->tooManyAttempts($identifier)) {
            $this->rateLimiter->lock($identifier);
            throw new Exception('Too many login attempts. Please try again later.');
        }

        // Fetch user from the database based on the identifier
        $user = $this->getUserByIdentifier($identifier);

        if (!$user || !$this->verifyPassword($credentials['password'], $user->password)) {
            // Increment failed login attempts
            $this->rateLimiter->hit($identifier);

            throw new Exception('Invalid credentials.');
        }

        // Clear attempts after successful login
        $this->rateLimiter->clearAttempts($identifier);

        // Login the user (e.g., start a session)
        $this->setUserSession($user);

        return true;
    }

    /**
     * Log out the authenticated user.
     *
     * @return void
     */
    public function logout(): void
    {
        // Clear user session or authentication tokens
        $this->clearUserSession();
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
        // Assuming we store session or token for the logged-in user
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['user_id'], $_SESSION['email']);
        // Clear the session data related to the user
        $_SESSION = [];

        // Destroy the session completely
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }

        // Finally, destroy the session
        session_destroy(); // Optional: Destroy the entire session
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
