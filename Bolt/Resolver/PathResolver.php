<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Path Resolver ===============
 * ====================================
 */

namespace Bolt\Bolt\Resolver;

class PathResolver
{
    private $basePath;

    public function __construct($basePath = null)
    {
        if ($basePath === null) {
            $this->basePath = dirname(__DIR__);
        } else {
            $this->basePath = $basePath;
        }
    }

    public function base_path($path = ''): string
    {
        $path = ltrim($path, '/'); // Remove leading slashes from the path
        return $this->basePath . DIRECTORY_SEPARATOR . $path;
    }

    public function config_path($path = ''): string
    {
        $configPath = $this->basePath . DIRECTORY_SEPARATOR . 'config';
        $path = ltrim($path, '/'); // Remove leading slashes from the path
        return $configPath . DIRECTORY_SEPARATOR . $path;
    }

    public function storage_path($path = ''): string
    {
        $storagePath = $this->basePath . DIRECTORY_SEPARATOR . 'storage';
        $path = ltrim($path, '/'); // Remove leading slashes from the path
        return $storagePath . DIRECTORY_SEPARATOR . $path;
    }

    public function router_path($path = ''): string
    {
        $routerPath = $this->basePath . DIRECTORY_SEPARATOR . 'routes';
        $path = ltrim($path, '/'); // Remove leading slashes from the path
        return $routerPath . DIRECTORY_SEPARATOR . $path;
    }
}
