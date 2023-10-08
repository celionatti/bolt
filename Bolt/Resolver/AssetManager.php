<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Asset Manager ===============
 * ====================================
 */

namespace Bolt\Bolt\Resolver;

class AssetManager
{
    private $basePath;
    
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    public function getAssetPath($assetName)
    {
        // Sanitize the asset name to prevent directory traversal attacks
        $sanitizedAssetName = preg_replace('/\.\.\//', '', $assetName);
        
        // Combine the base path with the asset name
        return $this->basePath . '/' . $sanitizedAssetName;
    }
    
}
