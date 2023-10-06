<?php


class Logger_n
{
    private $logFilePath;

    public function __construct($logFilePath)
    {
        $this->logFilePath = $logFilePath;
    }

    public function log($message)
    {
        $logMessage = $this->formatLogMessage($message);
        file_put_contents($this->logFilePath, $logMessage, FILE_APPEND);
    }

    private function formatLogMessage($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] $message" . PHP_EOL;
        return $formattedMessage;
    }

    public function debug($message)
    {
        if (defined('DEBUG') && DEBUG === true) {
            $this->log("[DEBUG] $message");
        }
    }

    public function error($message)
    {
        $this->log("[ERROR] $message");
    }

    public function info($message)
    {
        $this->log("[INFO] $message");
    }

    public function warning($message)
    {
        $this->log("[WARNING] $message");
    }
}

// Usage:
$logFilePath = 'app.log';
$logger = new Logger($logFilePath);

// Log messages
$logger->info('This is an informational message.');
$logger->warning('This is a warning message.');
$logger->error('This is an error message.');

// Debug messages (only logged if DEBUG constant is defined as true)
$logger->debug('This is a debug message.');

?>
