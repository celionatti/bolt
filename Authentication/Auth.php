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

use celionatti\Bolt\Model\User;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Illuminate\Support\RateLimits;
use celionatti\Bolt\Sessions\Handlers\DefaultSessionHandler;

abstract class Auth
{
    protected $session;
    protected $rateLimiter;
    protected ?User $_user = null;

    const MAX_ATTEMPTS = 5;
    const DECAY_MINUTES = 1;
    const REMEMBER_ME_COOKIE_NAME = "_bv_remember_me";
    const BV_SESSION_NAME = "__bv_user_id";
    const REMEMBER_ME_DURATION = 86400 * 30; // 30 days

    public function __construct()
    {
        $this->session = new DefaultSessionHandler();
        $this->rateLimiter = new RateLimits();
    }

    public function attempt(array $credentials, bool $remember = false, bool $checkBlocked = true, bool $checkVerified = true): bool
    {
        $this->validateEmail($credentials['email']);

        $key = $this->throttleKey($credentials['email']);

        if ($this->rateLimiter->tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw new BoltException("Too many login attempts. Please try again later.");
        }

        /**
         * @var User $user
         */
        $user = (new User())->findBy(['email' => $credentials['email']])->first();

        if (!$user || !$this->validateUser($user, $credentials['password'], $checkBlocked, $checkVerified)) {
            $this->rateLimiter->hit($key, self::DECAY_MINUTES * 60);
            throw new BoltException("Invalid credentials.");
        }

        $this->login($user, $remember);
        $this->rateLimiter->clear($key);
        return true;
    }

    public function login(User $user, bool $remember = false): void
    {
        $this->session->set(self::BV_SESSION_NAME, $user->user_id);

        if ($remember) {
            $token = bin2hex(random_bytes(16));
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            $this->setRememberMeCookie($token);
            $user->remember_token = $hashedToken;
            $user->save();
        }
    }

    public function logout(): void
    {
        $this->clearRememberMeCookie();
        $this->session->destroy();
    }

    public function user(): ?User
    {
        $userId = $this->session->get(self::BV_SESSION_NAME);

        if ($userId) {
            return (new User())->find($userId);
        }

        return $this->getUserFromRememberMeCookie();
    }

    public function check(): bool
    {
        return $this->session->has(self::BV_SESSION_NAME) || $this->hasRememberMeCookie();
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function hasRole(string $role)
    {
        $user = $this->user();
        return $user ? $user->hasRole($role) : false;
    }

    public function authorizeRole(string $role): void
    {
        if (!$this->hasRole($role)) {
            throw new BoltException("Unauthorized. Role '{$role}' is required.");
        }
    }

    protected function throttleKey(string $email): string
    {
        return 'login_attempt_' . md5($email);
    }

    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BoltException("Invalid email format.");
        }
    }

    private function validateUser(User $user, string $password, bool $checkBlocked, bool $checkVerified): bool
    {
        if ($checkBlocked && $user->blocked) {
            throw new BoltException("User is blocked.");
        }

        if ($checkVerified && !$user->verified) {
            throw new BoltException("User is not verified.");
        }

        return password_verify($password, $user->password);
    }

    private function setRememberMeCookie(string $token): void
    {
        setcookie(self::REMEMBER_ME_COOKIE_NAME, $token, [
            'expires' => time() + self::REMEMBER_ME_DURATION,
            'path' => '/',
            'secure' => true, // Only send over HTTPS
            'httponly' => true, // Accessible only via HTTP (no JavaScript)
            'samesite' => 'Strict', // Only send the cookie for same-site requests
        ]);
    }

    private function clearRememberMeCookie(): void
    {
        if (isset($_COOKIE[self::REMEMBER_ME_COOKIE_NAME])) {
            unset($_COOKIE[self::REMEMBER_ME_COOKIE_NAME]);
            setcookie(self::REMEMBER_ME_COOKIE_NAME, '', time() - 3600, '/');
        }
    }

    private function getUserFromRememberMeCookie(): ?User
    {
        if ($this->hasRememberMeCookie()) {
            $cookieToken = $_COOKIE[self::REMEMBER_ME_COOKIE_NAME];

            /**
             * @var User $user
             */
            $user = (new User())->findBy(['remember_token' => $cookieToken])->first();

            if ($user && password_verify($cookieToken, $user->remember_token)) {
                $this->session->set(self::BV_SESSION_NAME, $user->id);
                return $user;
            }
        }

        return null;
    }

    private function hasRememberMeCookie(): bool
    {
        return isset($_COOKIE[self::REMEMBER_ME_COOKIE_NAME]);
    }
}
