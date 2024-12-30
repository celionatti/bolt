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
use DateTime;
use PhpStrike\app\models\FailedLogin;
use PhpStrike\app\models\User;
use celionatti\Bolt\Sessions\Handlers\DefaultSessionHandler;

class Auth
{
    protected DefaultSessionHandler $session;
    protected FailedLogin $failedLogin;
    protected User $user;

    public function __construct()
    {
        $this->session = new DefaultSessionHandler();
        $this->failedLogin = new FailedLogin();
        $this->user = new User();
    }

    public function login(string $email, string $password): array
    {
        $user = $this->user->findBy(['email' => $email])->toArray();

        if (!$user) {
            return $this->handleFailedLogin($email, 'User does not exist.');
        }

        if ($user['is_blocked'] && $this->isBlocked($email)) {
            return ['success' => false, 'message' => 'Account is currently blocked.'];
        }

        if (!password_verify($password, $user['password'])) {
            return $this->handleFailedLogin($email, 'Invalid credentials.');
        }

        $this->resetFailedLogins($email);

        $this->session->set("user_id", $user['user_id']);

        return ['success' => true, 'message' => 'Login successful.'];
    }

    protected function handleFailedLogin(string $email, string $reason): array
    {
        $record = $this->failedLogin->findBy(['email' => $email]);

        if (!$record) {
            $this->failedLogin->create(['email' => $email, 'attempts' => 1, 'blocked_until' => null]);
        } else {
            $this->updateFailedLogin($record, $email);
        }

        return ['success' => false, 'message' => $reason];
    }

    protected function updateFailedLogin($record, string $email): void
    {
        $attempts = $record->attempts + 1;
        $blockDuration = $this->calculateBlockDuration($attempts);
        $blockedUntil = $attempts > 4 ? (new DateTime())->modify("+$blockDuration minutes")->format('Y-m-d H:i:s') : null;

        $this->failedLogin->update(['attempts' => $attempts, 'blocked_until' => $blockedUntil], $email, "email");

        if ($attempts > 4) {
            $this->user->update(['is_blocked' => 1], $email, "email");
        }
    }

    protected function resetFailedLogins(string $email): void
    {
        $this->failedLogin->delete(['email' => $email]);
        $this->user->update(['is_blocked' => 0], $email, "email");
    }

    protected function calculateBlockDuration(int $attempts): int
    {
        return min(max(($attempts - 4) * 5, 5), 60);
    }

    public function isBlocked(string $email): bool
    {
        $record = $this->failedLogin->findBy(['email' => $email])->toArray();

        if (!$record || !$record['blocked_until']) {
            return false;
        }

        return (new DateTime($record['blocked_until'])) > new DateTime();
    }
}
