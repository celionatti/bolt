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

abstract class AuthCopy
{
    protected $session;
    protected $rateLimiter;
    protected $_user;

    const MAX_ATTEMPTS = 5;
    const DECAY_MINUTES = 1;
    const REMEMBER_ME_COOKIE_NAME = '_bv_remember_me';
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
        $this->session->set('user_id', $user->id);

        if ($remember) {
            $token = bin2hex(random_bytes(16));
            $this->setRememberMeCookie($token);
            $user->remember_token = $token;
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
        $userId = $this->session->get('user_id');

        if ($userId) {
            return (new User())->find($userId);
        }

        return $this->getUserFromRememberMeCookie();
    }

    public function check(): bool
    {
        return $this->session->has('user_id') || $this->hasRememberMeCookie();
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    // public function hasRole(string $role)
    // {
    //     // $user = $this->user();
    //     // return $user ? $user->hasRole($role) : false;
    // }

    // public function hasPermission(string $permission)
    // {
    //     // $user = $this->user();
    //     // return $user ? $user->hasPermission($permission) : false;
    // }

    // public function authorizeRole(string $role): void
    // {
    //     if (!$this->hasRole($role)) {
    //         throw new BoltException("Unauthorized. Role '{$role}' is required.");
    //     }
    // }

    // public function authorizePermission(string $permission): void
    // {
    //     if (!$this->hasPermission($permission)) {
    //         throw new BoltException("Unauthorized. Permission '{$permission}' is required.");
    //     }
    // }

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
        setcookie(self::REMEMBER_ME_COOKIE_NAME, $token, time() + self::REMEMBER_ME_DURATION, "/");
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
            /**
             * @var User $user
             */
            $user = (new User())->findBy(['remember_token' => $_COOKIE[self::REMEMBER_ME_COOKIE_NAME]])->first();

            if ($user) {
                $this->session->set('user_id', $user->id);
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
