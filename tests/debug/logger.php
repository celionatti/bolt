<?php

class Logger_ds
{
    private $logFilePath;
    private $maxLogFileSize = 5242880; // 5MB
    private $logFileBackupCount = 5;
    private $logLevels = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];
    private $logDestination = 'file'; // 'file' or 'stdout'
    
    public function __construct($logFilePath)
    {
        $this->logFilePath = $logFilePath;
        $this->rotateLogFiles();
    }
    
    public function log($message, $logLevel = 'INFO')
    {
        if (!in_array($logLevel, $this->logLevels)) {
            return; // Log level not recognized
        }
        
        $logMessage = $this->formatLogMessage($message, $logLevel);
        
        if ($this->logDestination === 'file') {
            file_put_contents($this->logFilePath, $logMessage, FILE_APPEND);
        } elseif ($this->logDestination === 'stdout') {
            echo $logMessage;
        }
        
        $this->rotateLogFiles();
    }
    
    private function formatLogMessage($message, $logLevel)
    {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] [$logLevel] $message" . PHP_EOL;
        return $formattedMessage;
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

// Set log destination to 'stdout'
$logger->setLogDestination('stdout');

// Set log level to 'WARNING' (logs WARNING and ERROR messages)
$logger->setLogLevel('WARNING');

// Log messages
$logger->log('This is an informational message.', 'INFO');
$logger->log('This is a warning message.', 'WARNING');
$logger->log('This is an error message.', 'ERROR');
$logger->log('This is a debug message.', 'DEBUG');

?>
