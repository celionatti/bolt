<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - SessionHandler ============
 * ==================================
 */

namespace celionatti\Bolt\Sessions\Handlers;

use PDO;
use Illuminate\Support\Facades\Redis;
use celionatti\Bolt\Sessions\SessionHandler;

class RedisSessionHandler extends SessionHandler
{
    private $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );
        $this->start();
    }

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sessionId)
    {
        return $this->redis->get($sessionId) ?? '';
    }

    public function write($sessionId, $data)
    {
        return $this->redis->set($sessionId, $data, ini_get('session.gc_maxlifetime'));
    }

    public function destroy_redis($sessionId)
    {
        return $this->redis->del($sessionId);
    }

    public function gc($maxLifetime)
    {
        return true;
    }
}