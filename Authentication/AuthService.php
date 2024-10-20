<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - AuthService ==================
 * =====================================
 */

namespace celionatti\Bolt\Authentication;

use celionatti\Bolt\Model\User;
use celionatti\Bolt\Database\Database;
use celionatti\Bolt\Sessions\Handlers\DefaultSessionHandler;

class AuthService
{
    protected $maxAttempts = 3; // Max allowed login attempts
    protected $blockDuration = 5 * 60; // 5 minutes in seconds
    protected DefaultSessionHandler $session;
    protected $db;

    public function __construct()
    {
        $this->session = new DefaultSessionHandler();
        $this->db = Database::getInstance()->queryBuilder(); // Initialize QueryBuilder
    }

    public function attemptLogin($email, $password)
    {
        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user->password)) {
            return false;
        }

        // Set session for the logged-in user
        $this->session->set('user_id', $user->user_id);
        return true;
    }

    public function isBlocked($email)
    {
        $failedAttempts = $this->getFailedAttempts($email);
        if ($failedAttempts && $failedAttempts['blocked_until'] > time()) {
            return true;
        }
        return false;
    }

    public function getRemainingBlockTime($email)
    {
        $failedAttempts = $this->getFailedAttempts($email);
        $remainingTime = ($failedAttempts['blocked_until'] - time()) / 60;
        return ceil($remainingTime);
    }

    public function hasReachedMaxAttempts($email)
    {
        $failedAttempts = $this->getFailedAttempts($email);
        return $failedAttempts && $failedAttempts['attempts'] >= $this->maxAttempts;
    }

    public function blockUser($email)
    {
        $this->updateFailedAttempts($email, $this->maxAttempts, time() + $this->blockDuration);
    }

    public function incrementFailedAttempts($email)
    {
        $failedAttempts = $this->getFailedAttempts($email);

        if ($failedAttempts) {
            $attempts = $failedAttempts['attempts'] + 1;
        } else {
            $attempts = 1;
        }

        $this->updateFailedAttempts($email, $attempts);
    }

    public function resetFailedAttempts($email)
    {
        $this->clearFailedAttempts($email);
    }

    protected function getFailedAttempts($email)
    {
        // Simulating fetching data from the database
        return $this->db->from("failed_logins")
            ->where('email', '=', $email)
            ->first(); // Fetch the first result as an object
    }

    protected function updateFailedAttempts($email, $attempts, $blockedUntil = null)
    {
        // Insert or update failed login attempts
        $existing = $this->getFailedAttempts($email);

        if ($existing) {
            $this->db->update('failed_logins', [
                'attempts' => $attempts,
                'blocked_until' => $blockedUntil
            ])
            ->where('email', '=', $email)
            ->execute();
        } else {
            $this->db->insert('failed_logins', [
                'email' => $email,
                'attempts' => $attempts,
                'blocked_until' => $blockedUntil
            ])->execute();
        }
    }

    protected function clearFailedAttempts($email)
    {
        $this->db->delete('failed_logins')
            ->where('email', '=', $email)
            ->execute();
    }
}
