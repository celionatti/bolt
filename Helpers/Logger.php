<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - Logger ==================
 * ================================
 */

namespace celionatti\Bolt\Helpers;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    private $logFilePath;
    private $maxLogFileSize = 5242880; // 5MB
    private $logFileBackupCount = 5;
    private $logLevels = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];
    private $logDestination = 'file'; // 'file' or 'stdout'
    private $monolog;   

    public function __construct($logFilePath)
    {
        $this->logFilePath = get_root_dir() . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR . $logFilePath;
        $this->rotateLogFiles();

        // Set up Monolog
        $this->monolog = new MonologLogger('app_logger');
        $streamHandler = new StreamHandler($this->logFilePath, MonologLogger::DEBUG);

        // Optional: Customize the log format
        $formatter = new LineFormatter(null, null, true, true);
        $streamHandler->setFormatter($formatter);

        $this->monolog->pushHandler($streamHandler);
    }

    public function log($message, $logLevel = 'INFO')
    {
        if (!in_array($logLevel, $this->logLevels)) {
            return; // Log level not recognized
        }

        $logMessage = $this->formatLogMessage($message, $logLevel);

        if ($this->logDestination === 'file') {
            $this->monolog->log(constant(MonologLogger::class . '::' . $logLevel), $logMessage);
        } elseif ($this->logDestination === 'stdout') {
            echo $logMessage;
        }

        $this->rotateLogFiles();
    }

    private function formatLogMessage($message, $logLevel)
    {
        $timestamp = date('Y-m-d H:i:s');
        return "[$timestamp] [$logLevel] $message";
    }

    public function setLogDestination($destination)
    {
        if ($destination === 'file' || $destination === 'stdout') {
            $this->logDestination = $destination;
        }
    }

    public function setLogLevel($logLevel)
    {
        if (in_array($logLevel, $this->logLevels)) {
            $this->logLevels = array_slice($this->logLevels, array_search($logLevel, $this->logLevels));
        }
    }

    private function rotateLogFiles()
    {
        if (file_exists($this->logFilePath) && filesize($this->logFilePath) >= $this->maxLogFileSize) {
            for ($i = $this->logFileBackupCount; $i > 0; $i--) {
                $backupIndex = $i - 1;
                $backupFileName = "{$this->logFilePath}.{$backupIndex}";

                if ($backupIndex === 0) {
                    // Delete the oldest backup file
                    @unlink($backupFileName);
                } else {
                    // Rename previous backup file to the current index
                    $previousBackupFileName = $this->logFilePath . '.' . ($backupIndex - 1);
                    if (file_exists($previousBackupFileName)) {
                        rename($previousBackupFileName, $backupFileName);
                    }
                }
            }

            // Rename the current log file to .0
            rename($this->logFilePath, $this->logFilePath . '.0');
        }
    }

    public function debug($message)
    {
        $this->log($message, 'DEBUG');
    }

    public function error($message)
    {
        $this->log($message, 'ERROR');
    }

    public function info($message)
    {
        $this->log($message, 'INFO');
    }

    public function warning($message)
    {
        $this->log($message, 'WARNING');
    }
}
