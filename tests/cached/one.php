<?php

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

    public function set($key, $value, $ttl = 3600)
    {
        $cacheFile = $this->getCacheFilePath($key);
        $data = [
            'expiration' => time() + $ttl,
            'data' => $value,
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

    protected function getCacheFilePath($key)
    {
        $hashedKey = md5($key);
        return $this->cacheDirectory . '/' . $hashedKey . '.cache';
    }
}
