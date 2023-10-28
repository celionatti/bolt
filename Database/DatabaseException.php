<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - Database Exception ========
 * ==================================
 */

namespace celionatti\Bolt\Database;

use Throwable;
use Exception;

class DatabaseException extends Exception
{
    public function __construct($message = '', $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        $this->logErrorToFile();
        bolt_die($this->getMessage(), "Database Error");
    }

    private function logErrorToFile($maxLogSizeBytes = 1048576)
    {
        $errorMessage = "[" . date("Y-m-d H:i:s") . "] ";
        $errorMessage .= "[ Database Error ] ";
        $errorMessage .= $this->getMessage() . "\n";

        $basePath = get_root_dir() . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR;
        if (!is_dir($basePath)) {
            // Create the controller directory
            if (!mkdir($basePath, 0755, true)) {
                console_logger("Error: Unable to create the Logs directory.", true, true, 'error');
            }
        }
        $logFile = $basePath . 'database.log';

        // Check if the log file size exceeds the specified limit
        if (file_exists($logFile) && filesize($logFile) >= $maxLogSizeBytes) {
            // If the limit is reached, create a new log file
           unlink($logFile);
        }

        // Append the error message to the current or new log file
        file_put_contents($logFile, $errorMessage, FILE_APPEND);
    }
}
