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

    public function exceedsLimit($email)
    {
        $lastAttempt = $this->getLastAttempt($email);

        if ($lastAttempt === false) {
            return false; // No previous attempts
        }

        $now = time();
        if ($now - $lastAttempt['timestamp'] < $this->resetTime) {
            return $lastAttempt['attempts'] >= $this->attemptsLimit;
        } else {
            $this->resetAttempts($email);
            return false;
        }
    }

    public function getLastAttempt($email)
    {
        $query = "SELECT * FROM login_attempts WHERE user_attempted = :user_attempted ORDER BY timestamp DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_attempted', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function incrementAttempts($email)
    {
        $lastAttempt = $this->getLastAttempt($email);

        if ($lastAttempt && (time() - $lastAttempt['timestamp'] < $this->resetTime)) {
            $this->updateExistingRecord($lastAttempt);
        } else {
            $this->insertNewRecord($email);
        }
    }

    private function updateExistingRecord($lastAttempt)
    {
        $query = "UPDATE login_attempts SET attempts = attempts + 1 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $lastAttempt['id']);
        $stmt->execute();
    }

    private function insertNewRecord($email)
    {
        $query = "INSERT INTO login_attempts (user_attempted, attempts, ip_address, user_agent) VALUES (:user_attempted, 1, :ip_address, :user_agent)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_attempted', $email);
        $stmt->bindValue(':ip_address', 127);
        $stmt->bindValue(':user_agent', 2);
        $stmt->execute();
    }


    public function resetAttempts($email)
    {
        $query = "DELETE FROM login_attempts WHERE user_attempted = :user_attempted";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_attempted', $email);
        $stmt->execute();
    }
}
