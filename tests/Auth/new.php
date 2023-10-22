<?php

declare(strict_types=1);

namespace Bolt\Bolt\Authentication;

use PDO;

class RateLimiter
{
    protected $db;
    private $attemptsLimit = 5;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function incrementAttempts($email)
    {
        $attempts = $this->getAttemptsCount($email);

        if ($attempts >= $this->attemptsLimit) {
            $this->blockAccount($email);
        } else {
            $this->updateAttemptsCount($email, $attempts + 1);
        }
    }

    public function blockAccount($email)
    {
        // Implement the logic to block the account, e.g., update a 'blocked' field in your user table.
        // You should also reset the login attempts at this point.
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
        $query = "INSERT INTO login_attempts (user_attempted, attempts) VALUES (:user_attempted, :attempts) ON DUPLICATE KEY UPDATE attempts = :attempts";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_attempted', $email);
        $stmt->bindParam(':attempts', $attempts);
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
