<?php

declare(strict_types=1);

/**
 * =====================================================
 * =====================        ========================
 * RateLimits
 * =====================        ========================
 * =====================================================
 */

namespace celionatti\Bolt\Illuminate\Support;

use celionatti\Bolt\Database\Database;
use celionatti\Bolt\BoltQueryBuilder\QueryBuilder;


class RateLimits
{
    protected $queryBuilder;

    public function __construct()
    {
        $database = new Database();
        $this->queryBuilder = new QueryBuilder($database->getConnection());
    }

    public function hit($key, $seconds)
    {
        $attempts = $this->getAttempts($key);
        $expiresAt = time() + $seconds;

        if ($attempts > 0) {
            $this->queryBuilder->update('rate_limits', [
                'attempts' => $attempts + 1,
                'expires_at' => $expiresAt
            ])->where('key', '=', $key)->execute();
        } else {
            $this->queryBuilder->insert('rate_limits', [
                'key' => $key,
                'attempts' => 1,
                'expires_at' => $expiresAt
            ])->execute();
        }
    }

    public function tooManyAttempts($key, $maxAttempts)
    {
        return $this->getAttempts($key) >= $maxAttempts;
    }

    public function clear($key)
    {
        $this->queryBuilder->delete('rate_limits')->where('key', '=', $key)->execute();
    }

    protected function getAttempts($key)
    {
        $result = $this->queryBuilder->select('attempts', 'expires_at')
            ->from('rate_limits')
            ->where('key', '=', $key)
            ->execute();

        if ($result && $result[0]['expires_at'] > time()) {
            return $result[0]['attempts'];
        }

        if ($result && $result[0]['expires_at'] <= time()) {
            $this->clear($key);
        }

        return 0;
    }
}
