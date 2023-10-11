<?php

declare(strict_types=1);

/**
 * ==============================================
 * ================         =====================
 * BoltException Class.
 * ================         =====================
 * ==============================================
 */

namespace Bolt\Bolt\BoltException;

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

    private function logErrorToFile()
    {
        $errorMessage = "[" . date("Y-m-d H:i:s") . "] ";
        $errorMessage .= "[" . strtoupper($this->errorLevel) . "] ";
        $errorMessage .= $this->getMessage() . "\n";
        $basePath = get_root_dir() . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR;
        file_put_contents($basePath . 'error.log', $errorMessage, FILE_APPEND);
    }

    private function displayErrorOnScreen()
    {
        $styles = [
            'error' => 'background-color: #FF0000; color: #FFFFFF; padding: 10px;',
            'warning' => 'background-color: #FFA500; color: #000000; padding: 10px;',
            'info' => 'background-color: #007BFF; color: #FFFFFF; padding: 10px;',
            'critical' => 'background-color: #FF0000; color: #FFFFFF; padding: 10px; font-weight: bold;',
        ];

        $style = $styles[$this->errorLevel] ?? '';

        echo '<div style="' . $style . '">';
        echo '<strong>' . strtoupper($this->errorLevel) . ':</strong> ' . $this->getMessage();
        echo '</div>';
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
