<?php

declare(strict_types=1);

/**
 * =====================================================
 * =====================        ========================
 * RateLimits
 * =====================        ========================
 * =====================================================
 */

namespace celionatti\Bolt\Illuminate\Support;

use DateTime;
use celionatti\Bolt\Database\Database;


class RateLimits
{
    protected string $keyPrefix = 'rate_limiter_';
    protected int $maxAttempts;
    protected int $decayMinutes;
    protected Database $db;

    /**
     * RateLimiter constructor.
     *
     * @param int $maxAttempts
     * @param int $decayMinutes
     */
    public function __construct(int $maxAttempts = 5, int $decayMinutes = 1)
    {
        $this->db = Database::getInstance();
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    /**
     * Generate a unique key for the identifier.
     *
     * @param string $identifier
     * @return string
     */
    protected function getKey(string $identifier): string
    {
        return $this->keyPrefix . $identifier;
    }

    /**
     * Increment the number of attempts for the given identifier.
     *
     * @param string $identifier
     */
    public function hit(string $identifier): void
    {
        $key = $this->getKey($identifier);

        $this->db->query("
            INSERT INTO rate_limits (rate_key, attempts, last_attempt_at)
            VALUES (:key, 1, NOW())
            ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt_at = NOW()
        ", [
            'key' => $key
        ]);
    }

    /**
     * Determine if the given identifier has exceeded the maximum number of attempts.
     *
     * @param string $identifier
     * @return bool
     */
    public function tooManyAttempts(string $identifier): bool
    {
        $key = $this->getKey($identifier);

        // Get the number of attempts and the timestamp of the last attempt
        $result = $this->db->query("
            SELECT attempts, last_attempt_at
            FROM rate_limits
            WHERE rate_key = :key
        ", ['key' => $key]);

        if ($result) {
            $attempts = (int) $result[0]->attempts;
            $lastAttemptAt = new DateTime($result[0]->last_attempt_at);
            $currentDate = new DateTime();

            // Check if decay time has passed
            if ($currentDate->diff($lastAttemptAt)->i >= $this->decayMinutes) {
                $this->clearAttempts($identifier);
                return false; // Decay time has passed, reset attempts
            }

            return $attempts >= $this->maxAttempts;
        }

        return false; // No attempts yet
    }

    /**
     * Clear the number of attempts for the given identifier.
     *
     * @param string $identifier
     */
    public function clearAttempts(string $identifier): void
    {
        $key = $this->getKey($identifier);

        $this->db->query("DELETE FROM rate_limits WHERE rate_key = :key", ['key' => $key]);
    }

    /**
     * Lock the identifier to prevent further attempts.
     *
     * @param string $identifier
     */
    public function lock(string $identifier): void
    {
        $key = $this->getKey($identifier);
        $lockTime = (new DateTime())->format('Y-m-d H:i:s');

        // Insert lock data or update an existing lock timestamp
        $this->db->query("
            INSERT INTO rate_limits (rate_key, attempts, last_attempt_at, locked_at)
            VALUES (:key, :attempts, NOW(), :lock_time)
            ON DUPLICATE KEY UPDATE locked_at = :lock_time
        ", [
            'key' => $key,
            'attempts' => $this->maxAttempts,
            'lock_time' => $lockTime
        ]);
    }

    /**
     * Check if the identifier is currently locked.
     *
     * @param string $identifier
     * @return bool
     */
    public function isLocked(string $identifier): bool
    {
        $key = $this->getKey($identifier);
        $result = $this->db->query("
            SELECT locked_at
            FROM rate_limits
            WHERE rate_key = :key
        ", ['key' => $key]);

        if ($result && $result[0]->locked_at) {
            $lockedAt = new DateTime($result[0]->locked_at);
            $currentDate = new DateTime();

            // Check if the lock time is still valid (within decay period)
            $unlockTime = $lockedAt->modify("+{$this->decayMinutes} minutes");
            if ($currentDate < $unlockTime) {
                return true; // Still locked
            } else {
                // Lock has expired, clear lock
                $this->clearLock($identifier);
            }
        }

        return false; // Not locked
    }

    /**
     * Clear the lock for a given identifier.
     *
     * @param string $identifier
     */
    public function clearLock(string $identifier): void
    {
        $key = $this->getKey($identifier);

        $this->db->query("
            UPDATE rate_limits
            SET locked_at = NULL
            WHERE rate_key = :key
        ", ['key' => $key]);
    }

    /**
     * Get the number of remaining attempts before lockout.
     *
     * @param string $identifier
     * @return int
     */
    public function remainingAttempts(string $identifier): int
    {
        $key = $this->getKey($identifier);
        $result = $this->db->query("
            SELECT attempts
            FROM rate_limits
            WHERE rate_key = :key
        ", ['key' => $key]);

        if ($result) {
            $attempts = (int) $result[0]->attempts;
            return max($this->maxAttempts - $attempts, 0);
        }

        return $this->maxAttempts;
    }

    /**
     * Get the retry delay in minutes for a given identifier.
     *
     * @param string $identifier
     * @return int
     */
    public function retryDelayMinutes(string $identifier): int
    {
        $key = $this->getKey($identifier);
        $result = $this->db->query("
            SELECT last_attempt_at
            FROM rate_limits
            WHERE rate_key = :key
        ", ['key' => $key]);

        if ($result) {
            $lastAttemptAt = new DateTime($result[0]->last_attempt_at);
            $currentDate = new DateTime();
            $elapsedMinutes = $currentDate->diff($lastAttemptAt)->i;

            // If within the decay period, return remaining delay time
            if ($elapsedMinutes < $this->decayMinutes) {
                return $this->decayMinutes - $elapsedMinutes;
            }
        }

        return 0;
    }
}
