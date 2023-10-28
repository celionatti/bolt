<?php

declare(strict_types=1);

/**
 * ==============================================
 * ================         =====================
 * BoltException Class.
 * ================         =====================
 * ==============================================
 */

namespace celionatti\Bolt\BoltException;

use Exception;

class BoltException extends Exception
{
    private $errorLevel = "error";

    public function __construct($message, $code = 0, $errorLevel = 'error', Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errorLevel = $errorLevel;

        // Log the error to a file with different log levels
        $this->logErrorToFile();

        // Display the error on the screen with different styling based on error level
        $this->displayErrorOnScreen();

        // Send email notifications for critical errors
        if ($this->errorLevel === 'critical') {
            $this->sendErrorEmail();
        }
    }

    private function logErrorToFile($maxLogSizeBytes = 1048576)
    {
        $errorMessage = "[" . date("Y-m-d H:i:s") . "] ";
        $errorMessage .= "[" . strtoupper($this->errorLevel) . "] ";
        $errorMessage .= $this->getMessage() . "\n";
        $basePath = get_root_dir() . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR;
        if (!is_dir($basePath)) {
            // Create the controller directory
            if (!mkdir($basePath, 0755, true)) {
                console_logger("Error: Unable to create the Logs directory.", true, true, 'error');
            }
        }
        $logFile = $basePath . "error.log";

        // Check if the log file size exceeds the specified limit
        if (file_exists($logFile) && filesize($logFile) >= $maxLogSizeBytes) {
            // If the limit is reached, create a new log file
            unlink($logFile);
        }

        file_put_contents($logFile, $errorMessage, FILE_APPEND);
    }

    private function displayErrorOnScreen()
    {
        $styles = [
            'error' => 'background-color: tomato; color: #FFFFFF;',
            'warning' => 'background-color: #FFA500; color: #000000;',
            'info' => 'background-color: #007BFF; color: #FFFFFF;',
            'critical' => 'background-color: #FF0000; color: #FFFFFF; font-weight: bold;',
        ];

        $style = $styles[$this->errorLevel] ?? '';

        $file = $this->getFile();
        $line = $this->getLine();

        echo '<html>';
        echo '<head>';
        echo '<style>';
        echo 'body {';
        echo '  margin: 0;';
        echo '  padding: 0;';
        echo '  background-color: #F0F0F0;';
        echo '}';
        echo '.error-container {';
        echo '  display: flex;';
        echo '  align-items: center;';
        echo '  justify-content: center;';
        echo '  height: 100vh;';
        echo '}';
        echo '.error-box {';
        echo '  background-color: #FFF;';
        echo '  width: 80%;';
        echo '  max-width: 600px;';
        echo '  border: 1px solid #E0E0E0;';
        echo '  border-radius: 5px;';
        echo '  padding: 20px;';
        echo '  text-align: center;';
        echo '}';
        echo 'h2 {';
        echo '  text-transform: uppercase;';
        echo '  color: #333;';
        echo '}';
        echo '.error-details {';
        echo '  text-align: left;';
        echo '  padding: 10px;';
        echo '}';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="error-container">';
        echo '<div class="error-box">';
        echo '<h2>Bolt Error</h2>';
        echo '<div style="' . $style . 'border-radius: 5px; padding: 10px; margin-top: 10px;">';
        echo '<strong>' . strtoupper($this->errorLevel) . ':</strong> ' . $this->getMessage();
        echo '<p><strong>File:</strong> ' . $file . '</p>';
        echo '<p><strong>Line:</strong> ' . $line . '</p>';
        echo '</div>';
        echo '<div class="error-details">';
        echo '<p style="text-align:center;">copyright Bolt.</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
        exit(1);
    }

    private function sendErrorEmail()
    {
        $to = 'admin@example.com';
        $subject = 'Critical Error Notification';
        $message = 'A critical error has occurred: ' . $this->getMessage();
        $headers = 'From: error@example.com';

        mail($to, $subject, $message, $headers);
    }
}
