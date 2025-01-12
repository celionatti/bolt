<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - Error ===================
 * ================================
 */

namespace celionatti\Bolt\Debug;

class Error
{
    public static function render($errorCode = 404, $errorMessage = 'Page Not Found', $errorDetails = [])
    {
        $html = self::generateHtml($errorCode, $errorMessage, $errorDetails);

        // Output and terminate execution
        echo $html;
        exit;
    }

    private static function generateHtml($errorCode, $errorMessage, $errorDetails)
    {
        // Framework and system details
        $phpVersion = phpversion();
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
        $frameworkVersion = 'Bolt v2.0.0'; // Replace with your framework's version dynamically.

        $errorDetailsHtml = '';
        if (!empty($errorDetails)) {
            foreach ($errorDetails as $key => $value) {
                $errorDetailsHtml .= "<li><strong>{$key}:</strong> {$value}</li>";
            }
        }

        $errorDetailsSection = !empty($errorDetails) ? <<<HTML
        <div class="error-details">
            <h3>Error Details</h3>
            <ul>
                {$errorDetailsHtml}
            </ul>
        </div>
HTML : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error {$errorCode}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
            text-align: center;
        }
        .error-container {
            background: #fff;
            border-radius: 10px;
            padding: 40px 30px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            max-width: 1200px;
            width: 90%;
            border: 3px solid tomato;
        }
        .error-icon {
            font-size: 80px;
            color: tomato;
            margin-bottom: 20px;
        }
        .error-code {
            font-size: 30px;
            font-weight: bold;
            color: tomato;
            margin: 0;
        }
        .error-message {
            font-size: 18px;
            color: #555;
            margin: 10px 0 20px;
        }
        .error-details {
            margin-top: 10px;
            text-align: left;
            background: transparent;
            padding: 10px;
            border-radius: 8px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        .error-details h3 {
            text-align: center;
        }
        .error-details ul {
            list-style: square;
            padding: 0 15px;
            margin: 0;
            color: limegreen;
        }
        .error-details ul li {
            margin: 8px 0;
            word-wrap: break-word;
        }
        .error-actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .error-actions a {
            text-decoration: none;
            padding: 12px 25px;
            background: tomato;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            transition: background 0.3s;
        }
        .error-actions a:hover {
            background: darkred;
        }
        .error-system-info {
            margin-top: 20px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Error Icon -->
        <div class="error-icon">🚨</div>

        <!-- Error Code -->
        <div class="error-code">{$errorCode}</div>

        <!-- Error Message -->
        <div class="error-message">{$errorMessage}</div>

        <!-- Error Details -->
        {$errorDetailsSection}

        <!-- Error Actions -->
        <div class="error-actions">
            <a href="/">Go to Homepage</a>
            <a href="javascript:history.back()">Go Back</a>
        </div>

        <!-- System Information -->
        <div class="error-system-info">
            <p><strong>Framework:</strong> {$frameworkVersion}</p>
            <p><strong>PHP Version:</strong> {$phpVersion}</p>
            <p><strong>Server:</strong> {$serverSoftware}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}