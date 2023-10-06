<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - Cache ===================
 * ================================
 */

namespace Bolt\Bolt\Helpers;

class Cache
{
    protected $cacheDirectory;

    public function __construct($cacheDirectory = 'cache')
    {
        $this->cacheDirectory = $cacheDirectory;
        if (!file_exists($this->cacheDirectory)) {
            mkdir($this->cacheDirectory, 0777, true);
        }
    }

    public function set($key, $value, $ttl = 3600, $tags = [], $dependencies = [])
    {
        $cacheFile = $this->getCacheFilePath($key);
        $data = [
            'expiration' => time() + $ttl,
            'data' => $value,
            'tags' => $tags,
            'dependencies' => $dependencies,
        ];
        $encodedData = serialize($data);
        file_put_contents($cacheFile, $encodedData);
    }

    public function get($key, $default = null)
    {
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            $data = unserialize(file_get_contents($cacheFile));
            if ($data['expiration'] >= time()) {
                return $data['data'];
            }
        }
        return $default;
    }

    public function has($key)
    {
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            $data = unserialize(file_get_contents($cacheFile));
            return $data['expiration'] >= time();
        }
        return false;
    }

    public function remove($key)
    {
        $cacheFile = $this->getCacheFilePath($key);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    public function clear()
    {
        $files = glob($this->cacheDirectory . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    public function invalidateByTag($tag)
    {
        $files = glob($this->cacheDirectory . '/*.cache');
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if (in_array($tag, $data['tags'])) {
                unlink($file);
            }
        }
    }

    protected function getCacheFilePath($key)
    {
        $hashedKey = md5($key);
        return $this->cacheDirectory . '/' . $hashedKey . '.cache';
    }
}
