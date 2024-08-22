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

    public function attempt()
    {
        
    }
}
