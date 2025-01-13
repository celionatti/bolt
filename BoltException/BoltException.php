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
    public function __construct(string $message = "", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        // Render the exception details automatically
        $this->render();
    }

    private function render(): void
    {
        $file = $this->getFile();
        $line = $this->getLine();
        $message = $this->getMessage();
        $code = $this->getCode();
        $trace = $this->getTrace();
        $variables = $this->getDefinedVariables();
        $frameworkDetails = $this->getFrameworkDetails();
        $codeSnippet = $this->getCodeSnippet($file, $line);
        $classOrMethod = $this->getClassOrMethod($trace);

        // Generate the HTML
        $html = $this->generateHtml($message, $code, $file, $line, $codeSnippet, $trace, $variables, $frameworkDetails, $classOrMethod);

        // Output and terminate execution
        echo $html;
        exit;
    }

    private function getClassOrMethod(array $trace): string
    {
        foreach ($trace as $item) {
            if (isset($item['class'], $item['function'])) {
                return "{$item['class']}::{$item['function']}()";
            }
        }
        return 'Global Scope';
    }

    private function getCodeSnippet(string $file, int $line, int $context = 5): array
    {
        if (!is_readable($file)) {
            return ['Code snippet unavailable.'];
        }

        $lines = file($file);
        $start = max($line - $context - 1, 0);
        $end = min($line + $context - 1, count($lines));

        return array_slice($lines, $start, $end - $start + 1, true);
    }

    private function getDefinedVariables(): array
    {
        $excludedKeys = ['_ENV', '_SERVER', 'GLOBALS', '__composer_autoload_files'];
        $variables = $GLOBALS;

        foreach ($excludedKeys as $key) {
            if (isset($variables[$key])) {
                unset($variables[$key]);
            }
        }

        return $variables;
    }

    private function isDevelopment(): bool
    {
        return in_array(getenv('APP_ENV'), ['development', 'local'], true);
    }

    private function getFrameworkDetails(): array
    {
        $composerFile = get_root_dir() . '/composer.lock'; // Adjust path as needed
        $frameworkVersion = 'Unknown';

        if (file_exists($composerFile)) {
            $composerData = json_decode(file_get_contents($composerFile), true);
            foreach ($composerData['packages'] as $package) {
                if ($package['name'] === 'celionatti/bolt') {
                    $frameworkVersion = $package['version'];
                    break;
                }
            }
        }

        return [
            'BOLT (PhpStrike) Version' => $frameworkVersion,
            'PHP Version' => phpversion(),
            'OS' => php_uname(),
        ];
    }

    private function generateHtml(
        string $message,
        int $code,
        string $file,
        int $line,
        array $codeSnippet,
        array $trace,
        array $variables,
        array $frameworkDetails,
        string $classOrMethod
    ): string {
        $codeHtml = '';
        foreach ($codeSnippet as $lineNumber => $lineContent) {
            $isHighlighted = $lineNumber + 1 === $line;
            $codeHtml .= sprintf(
                '<div class="%s"><span class="line-number">%d</span> <span class="code-text">%s</span></div>',
                $isHighlighted ? 'highlight' : '',
                $lineNumber + 1,
                htmlspecialchars($lineContent, ENT_QUOTES | ENT_HTML5, 'UTF-8')
            );
        }

        $traceHtml = '';
        foreach ($trace as $item) {
            $traceHtml .= sprintf(
                '<li>%s in %s:%d</li>',
                htmlspecialchars($item['function'] ?? 'unknown', ENT_QUOTES | ENT_HTML5),
                htmlspecialchars($item['file'] ?? 'unknown', ENT_QUOTES | ENT_HTML5),
                $item['line'] ?? 0
            );
        }

        $variablesHtml = '';
        foreach ($variables as $key => $value) {
            $variablesHtml .= sprintf(
                '<li><strong>%s:</strong> %s</li>',
                htmlspecialchars($key, ENT_QUOTES | ENT_HTML5),
                htmlspecialchars(json_encode($value), ENT_QUOTES | ENT_HTML5)
            );
        }

        $frameworkHtml = '';
        foreach ($frameworkDetails as $key => $value) {
            $frameworkHtml .= sprintf(
                '<li><strong>%s:</strong> %s</li>',
                htmlspecialchars($key, ENT_QUOTES | ENT_HTML5),
                htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5)
            );
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exception Caught</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .exception-container {
            max-width: 90%;
            margin: 20px auto;
            background: #fff;
            border: 1px solid tomato;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            padding: 15px;
            background: tomato;
            color: #fff;
            font-size: 1.5rem;
            text-align: center;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
        }
        .tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            background: #f4f4f4;
            transition: background 0.3s;
        }
        .tab:hover, .tab.active {
            background: #fff;
            border-bottom: 3px solid tomato;
        }
        .tab-content {
            display: none;
            padding: 15px;
        }
        .tab-content.active {
            display: block;
        }
        .code {
            font-family: monospace;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow-x: auto;
            padding: 10px;
        }
        .highlight {
            background: #ffdddd;
        }
        .line-number {
            color: #999;
            margin-right: 10px;
        }
    </style>
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`.tab[data-tab="${tabId}"]`).classList.add('active');
        }
    </script>
</head>
<body>
<div class="exception-container">
    <div class="header">Unhandled Exception: {$message}</div>
    <div class="tabs">
        <div class="tab active" data-tab="error-details" onclick="switchTab('error-details')">Error Details</div>
        <div class="tab" data-tab="stack-trace" onclick="switchTab('stack-trace')">Stack Trace</div>
        <div class="tab" data-tab="variables" onclick="switchTab('variables')">Variables</div>
        <div class="tab" data-tab="framework-details" onclick="switchTab('framework-details')">Framework Details</div>
    </div>
    <div id="error-details" class="tab-content active">
        <p><strong>Message:</strong> {$message}</p>
        <p><strong>Code:</strong> {$code}</p>
        <p><strong>File:</strong> {$file}</p>
        <p><strong>Line:</strong> {$line}</p>
        <p><strong>Class/Method:</strong> {$classOrMethod}</p>
        <div class="code">{$codeHtml}</div>
    </div>
    <div id="stack-trace" class="tab-content">
        <ul>{$traceHtml}</ul>
    </div>
    <div id="variables" class="tab-content">
        <ul>{$variablesHtml}</ul>
    </div>
    <div id="framework-details" class="tab-content">
        <ul>{$frameworkHtml}</ul>
    </div>
</div>
</body>
</html>
HTML;
    }
}
