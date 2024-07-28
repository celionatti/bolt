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

use celionatti\Bolt\Illuminate\Support\Session;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Illuminate\Support\RateLimits;

class Auth
{
    protected $session;
    protected $rateLimiter;

    const MAX_ATTEMPTS = 5;
    const DECAY_MINUTES = 1;

    public function __construct()
    {
        $this->session = new Session();
        $this->rateLimiter = new RateLimits();
    }

    public function attempt($credentials, $remember = false, $checkBlocked = true, $checkVerified = true)
    {
        if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            throw new BoltException("Invalid email format.");
        }

        $key = $this->throttleKey($credentials['email']);

        if ($this->rateLimiter->tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw new BoltException("Too many login attempts. Please try again later.");
        }

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            $this->rateLimiter->hit($key, self::DECAY_MINUTES * 60);
            throw new BoltException("Invalid credentials.");
        }

        if ($checkBlocked && $user->blocked) {
            throw new BoltException("User is blocked.");
        }

        if ($checkVerified && !$user->verified) {
            throw new BoltException("User is not verified.");
        }

        if (password_verify($credentials['password'], $user->password)) {
            $this->login($user, $remember);
            $this->rateLimiter->clear($key);
            return true;
        }

        $this->rateLimiter->hit($key, self::DECAY_MINUTES * 60);
        throw new BoltException("Invalid credentials.");
    }

    public function login(User $user, $remember = false)
    {
        $this->session->set('user_id', $user->id);

        if ($remember) {
            $token = bin2hex(random_bytes(16));
            setcookie('remember_me', $token, time() + 86400 * 30, "/"); // 30 days
            $user->remember_token = $token;
            $user->save();
        }
    }

    public function logout()
    {
        if (isset($_COOKIE['remember_me'])) {
            unset($_COOKIE['remember_me']);
            setcookie('remember_me', '', time() - 3600, '/'); // Expire the cookie
        }

        $this->session->destroy();
    }

    public function user()
    {
        $userId = $this->session->get('user_id');

        if ($userId) {
            return User::find($userId);
        }

        if (isset($_COOKIE['remember_me'])) {
            $user = User::where('remember_token', $_COOKIE['remember_me'])->first();

            if ($user) {
                $this->session->set('user_id', $user->id);
                return $user;
            }
        }

        return null;
    }

    public function check()
    {
        return $this->session->has('user_id') || isset($_COOKIE['remember_me']);
    }

    public function guest()
    {
        return !$this->check();
    }

    protected function throttleKey($email)
    {
        return 'login_attempt_' . md5($email);
    }
}
