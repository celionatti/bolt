<?php

declare(strict_types=1);

/**
 * =====================================================
 * =====================        ========================
 * Rate Limiter
 * =====================        ========================
 * =====================================================
 */

namespace celionatti\Bolt\Authentication;

use PDO;

class RateLimiter
{
    protected $db;
    private $attemptsLimit = 5; // Maximum login attempts allowed

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function incrementAttempts($email)
    {
        $attempts = $this->getAttemptsCount($email);

        if ($attempts >= $this->attemptsLimit) {
            $this->blockAccount($email, 1);
        } else {
            $this->updateAttemptsCount($email, $attempts + 1);
        }
    }

    public function blockAccount($email, $blocked)
    {
        // Implement the logic to block the account, e.g., update a 'blocked' field in your user table.
        // You should also reset the login attempts at this point.
        $query = "UPDATE users SET is_blocked = :is_blocked WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':is_blocked', $blocked, PDO::PARAM_INT);
        $stmt->execute();

        $this->resetAttempts($email);
    }

    public function getAttemptsCount($email)
    {
        $query = "SELECT attempts FROM login_attempts WHERE user_attempted = :user_attempted";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_attempted', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['attempts'];
        }

        return 0;
    }

    private function updateAttemptsCount($email, $attempts)
    {
        // Check if a record for the provided email already exists
        $existingAttempts = $this->getAttemptsCount($email);

        if ($existingAttempts > 0) {
            // If a record exists, update it
            $query = "UPDATE login_attempts SET attempts = :attempts, ip_address = :ip_address, user_agent = :user_agent WHERE user_attempted = :user_attempted";
        } else {
            // If no record exists, insert a new record
            $query = "INSERT INTO login_attempts (user_attempted, attempts, ip_address, user_agent) VALUES (:user_attempted, :attempts, :ip_address, :user_agent)";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_attempted', $email);
        $stmt->bindParam(':attempts', $attempts);
        $stmt->bindValue(':ip_address', '127.0.0.1');
        $stmt->bindValue(':user_agent', 'windows');
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
