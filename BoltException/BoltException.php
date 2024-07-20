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
use Throwable;
use celionatti\Bolt\Helpers\Logger;

class BoltException extends Exception
{
    protected string $errorLevel;
    private Logger $logger;

    public function __construct(
        string $message,
        int $code = 0,
        string $errorLevel = 'error',
        Throwable $previous = null
    ) {
        $this->errorLevel = $errorLevel;
        $this->logger = new Logger('error.log');
        parent::__construct($message, $code, $previous);

        $this->handleException();
    }

    private function handleException(): void
    {
        $this->logError();
        $this->displayErrorOnScreen();
    }

    protected function logError(): void
    {
        $logMessage = sprintf(
            "%s in %s on line %d",
            $this->getMessage(),
            $this->getFile(),
            $this->getLine()
        );

        switch ($this->errorLevel) {
            case 'debug':
                $this->logger->debug($logMessage);
                break;
            case 'info':
                $this->logger->info($logMessage);
                break;
            case 'warning':
                $this->logger->warning($logMessage);
                break;
            case 'error':
            case 'critical':
                $this->logger->error($logMessage);
                break;
        }
    }

    protected function displayErrorOnScreen(): void
    {
        $styles = [
            'error' => 'background-color: tomato; color: #FFFFFF;',
            'warning' => 'background-color: #FFA500; color: #000000;',
            'info' => 'background-color: #007BFF; color: #FFFFFF;',
            'critical' => 'background-color: #FF0000; color: #FFFFFF; font-weight: bold;',
        ];

        $style = $styles[$this->errorLevel] ?? 'background-color: #000; color: #FFF;';
        $errorLevel = strtoupper($this->errorLevel);
        $trace = nl2br($this->getTraceAsString());

        $serverInfo = [
            'Request URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'HTTP Method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'IP Address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        ];

        $serverInfoHtml = '';
        foreach ($serverInfo as $key => $value) {
            $serverInfoHtml .= "<p><strong>{$key}:</strong> {$value}</p>";
        }

        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { 
                    background: rgb(21,69,152);
                    background: linear-gradient(90deg, rgba(21,69,152,1) 0%, rgba(22,155,173,1) 26%, rgba(235,230,232,1) 37%, rgba(99,156,173,1) 45%, rgba(37,78,149,1) 100%); 
                    font-family: Arial, sans-serif;
                    color: #FFF; 
                    display: flex; 
                    justify-content: center; 
                    align-items: center; 
                    height: 100vh; 
                    margin: 0; 
                    padding: 20px;
                    box-sizing: border-box;
                }
                .error-container { 
                    width: 90%; 
                    max-width: 1200px; 
                    background-color: #222; 
                    border: 1px solid #333; 
                    border-radius: 8px; 
                    padding: 20px;
                    overflow: auto; /* Ensure content doesn't overflow */
                    word-wrap: break-word; /* Break long words */
                    overflow-wrap: break-word; /* Break long words */
                }
                .error-header { 
                    text-align: center; 
                    {$style} 
                    padding: 10px 20px; 
                    border-radius: 5px;
                    margin-bottom: 20px;
                }
                .error-details { 
                    margin-top: 20px; 
                    text-align: left;
                }
                .error-main {
                    display: flex; 
                    flex-direction: column; 
                    align-items: center; 
                }
                .error-main h5 { 
                    font-size: .8em; 
                    margin-bottom: 9px; 
                }
                .error-main p { 
                    font-size: 1.1em; 
                    margin: 5px 0; 
                }
                .error-main .home-link {
                    color: #007BFF;
                    text-decoration: none;
                    font-weight: bold;
                    margin-top: 20px;
                }
                .error-content {
                    display: flex; 
                    justify-content: space-between; 
                    margin-top: 20px;
                }
                .trace, .server-info {
                    background-color: #333;
                    padding: 15px;
                    border-radius: 5px;
                    margin-top: 8px;
                    overflow: auto;
                    width: 48%;
                    box-sizing: border-box;
                    font-size: 13px;
                    word-wrap: break-word; /* Ensure text wraps */
                }
                .trace pre, .server-info pre {
                    white-space: pre-wrap; /* Ensure content wraps correctly */
                }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-header">{$errorLevel}</div>
                <div class="error-main">
                    <h5>{$this->getMessage()}</h5>
                    <p>Error Code: {$this->getCode()}</p>
                    <p>File: {$this->getFile()}</p>
                    <p>Line: {$this->getLine()}</p>
                    <a href="/" class="home-link">Go to Homepage</a>
                </div>
                <div class="error-content">
                    <div class="trace">
                        <h3>Stack Trace:</h3>
                        <pre>{$trace}</pre>
                    </div>
                    <div class="server-info">
                        <h3>Server Information:</h3>
                        {$serverInfoHtml}
                    </div>
                </div>
            </div>
        </body>
        </html>
        HTML;

        echo $html;
        exit(1);
    }
}
