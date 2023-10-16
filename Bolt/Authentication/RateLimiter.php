<?php

declare(strict_types=1);

/**
 * =====================================================
 * =====================        ========================
 * Rate Limiter
 * =====================        ========================
 * =====================================================
 */

namespace Bolt\Bolt\Authentication;

use Bolt\Bolt\Database\Database;
use PDO;

class RateLimiter
{
    protected $db;
    private $attemptsLimit = 5; // Maximum login attempts allowed
    private $resetTime = 600; // Reset attempts after 10 minutes (600 seconds)

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function exceedsLimit($uuid)
    {
        $lastAttempt = $this->getLastAttempt($uuid);

        if ($lastAttempt === false) {
            return false; // No previous attempts
        }

        $now = time();
        if ($now - $lastAttempt['timestamp'] < $this->resetTime) {
            return $lastAttempt['attempts'] >= $this->attemptsLimit;
        } else {
            $this->resetAttempts($uuid);
            return false;
        }
    }

    public function getLastAttempt($uuid)
    {
        $query = "SELECT * FROM login_attempts WHERE uuid = :uuid ORDER BY timestamp DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $uuid);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function incrementAttempts($uuid)
    {
        $lastAttempt = $this->getLastAttempt($uuid);

        if ($lastAttempt && (time() - $lastAttempt['timestamp'] < $this->resetTime)) {
            // Update the existing record
            $query = "UPDATE login_attempts SET attempts = attempts + 1 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $lastAttempt['id']);
            $stmt->execute();
        } else {
            // Insert a new record
            $query = "INSERT INTO login_attempts (uuid, attempts, timestamp) VALUES (:uuid, 1, :timestamp)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':timestamp', time());
            $stmt->execute();
        }
    }

    public function resetAttempts($uuid)
    {
        $query = "DELETE FROM login_attempts WHERE uuid = :uuid";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':uuid', $uuid);
        $stmt->execute();
    }
}
