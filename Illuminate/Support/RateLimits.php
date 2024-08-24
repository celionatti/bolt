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
    protected $encryptionKey = 'bv_auth_encrypt_key_2024';
    protected $iv = 'bv_key_2024';

    public function __construct()
    {
        $database = new Database();
        $this->queryBuilder = new QueryBuilder($database->getConnection());
    }

    protected function getIpAddress()
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    protected function encrypt($data)
    {
        return openssl_encrypt($data, 'aes-256-cbc', $this->encryptionKey, 0, $this->iv);
    }

    protected function decrypt($data)
    {
        return openssl_decrypt($data, 'aes-256-cbc', $this->encryptionKey, 0, $this->iv);
    }

    protected function logRateLimit($key, $ipAddress)
    {
        // Implement your logging logic here
        $message = "Rate limit hit for key: {$key} from IP: {$ipAddress} at " . date('Y-m-d H:i:s');
        error_log($message);
    }

    public function hit($key, $seconds = 60, $userId = null)
    {
        $ipAddress = $this->encrypt($this->getIpAddress());
        $attempts = $this->getAttempts($key, $ipAddress, $userId);
        $multiplier = pow(2, $attempts - 1); // Exponential backoff
        $expiresAt = time() + ($seconds * $multiplier);

        $this->logRateLimit($key, $ipAddress);

        if ($attempts > 0) {
            $this->queryBuilder->update('rate_limits', [
                'attempts' => $attempts + 1,
                'expires_at' => $expiresAt
            ])->where('key', '=', $key)
                ->where('ip_address', '=', $ipAddress)
                ->where('user_id', '=', $userId)
                ->execute();
        } else {
            $this->queryBuilder->insert('rate_limits', [
                'key' => $key,
                'ip_address' => $ipAddress,
                'user_id' => $userId,
                'attempts' => 1,
                'expires_at' => $expiresAt
            ])->execute();
        }
    }

    public function tooManyAttempts($key, $maxAttempts = 5, $userId = null)
    {
        $ipAddress = $this->encrypt($this->getIpAddress());
        return $this->getAttempts($key, $ipAddress, $userId) >= $maxAttempts;
    }

    public function clear($key, $ipAddress = null, $userId = null)
    {
        $query = $this->queryBuilder->delete('rate_limits')
            ->where('key', '=', $key);

        if ($ipAddress) {
            $query->where('ip_address', '=', $ipAddress);
        }

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        $query->execute();
    }

    protected function getAttempts($key, $ipAddress, $userId = null)
    {
        $result = $this->queryBuilder->select('attempts', 'expires_at')
            ->from('rate_limits')
            ->where('key', '=', $key)
            ->where('ip_address', '=', $ipAddress);

        if ($userId) {
            $result->where('user_id', '=', $userId);
        }

        $result = $result->execute();

        if ($result && $result[0]['expires_at'] > time()) {
            return $result[0]['attempts'];
        }

        if ($result && $result[0]['expires_at'] <= time()) {
            $this->clear($key, $ipAddress, $userId);
        }

        return 0;
    }

    public function cleanup()
    {
        $this->queryBuilder->delete('rate_limits')
            ->where('expires_at', '<=', time())
            ->execute();
    }
}
