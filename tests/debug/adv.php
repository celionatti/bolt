<?php

class Logger_t
{
    private $logFilePath;
    private $maxLogFileSize = 5242880; // 5MB
    private $logFileBackupCount = 5;
    
    public function __construct($logFilePath)
    {
        $this->logFilePath = $logFilePath;
        $this->rotateLogFiles();
    }
    
    public function log($message)
    {
        $logMessage = $this->formatLogMessage($message);
        file_put_contents($this->logFilePath, $logMessage, FILE_APPEND);
        $this->rotateLogFiles();
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
    
    private function rotateLogFiles()
    {
        if (file_exists($this->logFilePath) && filesize($this->logFilePath) >= $this->maxLogFileSize) {
            for ($i = $this->logFileBackupCount; $i > 0; $i--) {
                $backupIndex = $i - 1;
                $backupFileName = $this->logFilePath . '.' . $backupIndex;
                
                if ($backupIndex === 0) {
                    // Delete the oldest backup file
                    unlink($backupFileName);
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
