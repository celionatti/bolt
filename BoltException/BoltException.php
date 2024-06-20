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
    $code = $this->getCode();
    $errorLevel = strtoupper($this->errorLevel);

    $html = <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                margin: 0;
                padding: 0;
                background-color: #000;
                font-family: Arial, sans-serif;
            }
            .error-container {
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                flex-direction: column;
            }
            .error-box {
                background-color: #FFF;
                width: 80%;
                max-width: 600px;
                border: 1px solid #E0E0E0;
                border-radius: 5px;
                padding: 20px;
                text-align: center;
            }
            h1 {
                font-size: 94px;
                margin: 0;
            }
            h2 {
                font-size: 24px;
                margin: 0;
                text-transform: uppercase;
                color: #333;
            }
            p {
                margin: 20px 0;
                color: #666;
            }
            .home-button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #000;
                color: #FFF;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
            .error-details {
                text-align: left;
                padding: 10px;
                border-top: 1px solid #E0E0E0;
                margin-top: 20px;
            }
            hr {
                margin-top: 8px;
                margin-bottom: 8px;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h3 style="{$style} border-radius: 5px; padding-inline: 45px; padding-top:10px; padding-bottom:10px;">{$errorLevel}</h3>
            <div class="error-box" style="border-radius: 5px; padding-inline: 30px; padding-top:10px; padding-bottom:10px;">
                <h2>{$this->getMessage()}</h2>
                <hr>
                <div>
                    <h1>{$code}</h1>
                </div>
                <p style="{$style} text-transform:capitalize; padding-top:10px; padding-bottom:10px; margin-bottom:5px;">The error occurred in <strong style="">{$file}</strong> on line <br><strong>{$line}</strong>.</p>
                <a href="/" class="home-button">Home Page</a>
            </div>
            <div class="error-details">
                <p style="text-align:center;">&copy; Bolt.</p>
            </div>
        </div>
    </body>
    </html>
    HTML;

    echo $html;
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
