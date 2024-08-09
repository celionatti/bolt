<?php

declare(strict_types=1);

/**
 * ==================================================
 * ==================               =================
 * ExtensionCheck Class
 * ==================               =================
 * ==================================================
 */

namespace celionatti\Bolt\Illuminate\Support;

use celionatti\Bolt\BoltException\BoltException;

class ExtensionCheck
{
    private $requiredExtensions = [];

    private $config = [
        'errorHandler' => 'exception', // You can set this to 'exception' for exception-based error handling.
    ];

    public function addRequiredExtension(string $extension): void
    {
        $this->requiredExtensions[] = $extension;
    }

    public function removeRequiredExtension(string $extension): void
    {
        $index = array_search($extension, $this->requiredExtensions);
        if ($index !== false) {
            unset($this->requiredExtensions[$index]);
        }
    }

    public function setErrorHandler(string $handler): void
    {
        $this->config['errorHandler'] = $handler;
    }

    public function checkExtensions(): void
    {
        $notLoaded = $this->getNotLoadedExtensions();

        if (!empty($notLoaded)) {
            $this->handleError($notLoaded);
        }
    }

    private function getNotLoadedExtensions(): array
    {
        $notLoaded = [];

        $defaultExtensions = [
            'gd',
            'mysqli',
            'pdo_mysql',
            'pdo_sqlite',
            'curl',
            'fileinfo',
            'intl',
            'exif',
            'mbstring',
        ];

        $this->requiredExtensions = array_merge($defaultExtensions, $this->requiredExtensions);

        foreach ($this->requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $notLoaded[] = $ext;
            }
        }

        return $notLoaded;
    }

    private function handleError(array $notLoaded): void
    {
        $errorMessage = "Please load the following extensions in your php.ini file: <br>" . implode("<br>", $notLoaded);

        switch ($this->config['errorHandler']) {
            case 'exception':
                throw new BoltException($errorMessage, 400, "critical");
            default:
                bolt_die($errorMessage, "Missing Extensions.");
        }
    }
}
