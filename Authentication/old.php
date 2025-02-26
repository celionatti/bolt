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
    protected $loggedInUser;

    public function __construct()
    {
        $this->session = new DefaultSessionHandler();
        $this->failedLogin = new FailedLogin();
        $this->user = new User();
    }

    public function user(): ?array
    {
        if ($this->loggedInUser !== null) {
            return $this->loggedInUser;
        }

        $userId = $this->session->get("user_id");

        if (!$userId) {
            $user = $this->autoLogin();
            if ($user) {
                $this->loggedInUser = $user;
                return $this->loggedInUser;
            }

            return null;
        }

        $user = $this->user->find($userId);
        if ($user) {
            $this->loggedInUser = $user->toArray();
            return $this->loggedInUser;
        }

        return null;
    }

    public function login(string $email, string $password, bool $rememberMe = false): array
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
            setFormMessage(["email" => "Invalid or empty credentials.", "password" => ""]);
            redirect(URL_ROOT . "/login");
        }

        $user = $this->user->findBy(['email' => $email]);

        if (!$user || !is_array($user) && !is_object($user)) {
            return $this->handleFailedLogin($email, 'User does not exist.');
        }

        if (is_object($user)) {
            $user = $user->toArray();
        }

        if ($user['is_blocked'] && $this->isBlocked($email)) {
            return ['success' => false, 'message' => 'Account is currently blocked.', 'type' => 'warning'];
        }

        if (!password_verify($password, $user['password'])) {
            return $this->handleFailedLogin($email, 'Invalid credentials.');
        }

        $this->resetFailedLogins($email);

        session_regenerate_id(true);
        $this->session->set("user_id", $user['user_id']);

        if ($rememberMe) {
            $this->setRememberMeToken($user['user_id']);
        }

        return ['success' => true, 'message' => 'Login successful.', 'type' => 'success'];
    }

    protected function setRememberMeToken($userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        $this->user->update(['remember_token' => $hashedToken], $userId, "user_id");

        setcookie(REMEMBER_ME_NAME, $token, [
            'expires' => time() + (30 * 24 * 60 * 60),
            'path' => '/',
            'httponly' => true,
            'secure' => false, // Set to true in production with HTTPS
            'samesite' => 'Lax', // Or 'Strict'/'None' based on your requirements
        ]);
    }

    public function autoLogin(): ?array
    {
        if ($this->session->get("user_id")) {
            $user = $this->user->find($this->session->get("user_id"));
            if ($user) {
                $this->loggedInUser = $user->toArray();
                return $this->loggedInUser;
            }
        }

        if (isset($_COOKIE[REMEMBER_ME_NAME])) {
            $token = $_COOKIE[REMEMBER_ME_NAME];
            $hashedToken = hash('sha256', $token);

            $user = $this->user->findBy(['remember_token' => $hashedToken]);

            if ($user) {
                session_regenerate_id(true);
                $this->session->set("user_id", $user['user_id']);
                $this->loggedInUser = $user->toArray();

                return $this->loggedInUser;
            }
        }

        return null;
    }

    public function logout(): void
    {
        $userId = $this->session->get("user_id");

        if ($userId) {
            // Regenerate remember token to invalidate old one
            $this->user->update(['remember_token' => null], $userId, "user_id");
        }

        $this->session->remove("user_id");

        if (isset($_COOKIE[REMEMBER_ME_NAME])) {
            setcookie(REMEMBER_ME_NAME, '', time() - 3600, '/');
        }

        session_regenerate_id(true); // Prevent session fixation
    }

    protected function handleFailedLogin(string $email, string $reason): array
    {
        $userExists = $this->user->findBy(['email' => $email]);
        if (!$userExists) {
            return ['success' => false, 'message' => 'User does not exist.', 'type' => 'info'];
        }

        $record = $this->failedLogin->findBy(['email' => $email]);

        if (!$record) {
            $this->failedLogin->create(['email' => $email, 'attempts' => 1, 'blocked_until' => null]);
        } else {
            $this->updateFailedLogin($record, $email);
        }

        return ['success' => false, 'message' => $reason, 'type' => 'warning'];
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
        $record = $this->failedLogin->findBy(['email' => $email]);

        if (!$record || !$record['blocked_until']) {
            return false;
        }

        return (new DateTime($record['blocked_until'])) > new DateTime();
    }
}
