<?php

declare(strict_types=1);

/**
 * ==============================================
 * ================         =====================
 * BoltException Class.
 * ================         =====================
 * ==============================================
 */

namespace celionatti\Bolt\Exceptions;

use Exception;
use Throwable;
use celionatti\Bolt\Helpers\Logger;

class BoltException extends Exception
{
    private bool $isDebug;

    public function __construct(string $message = "", int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        // Get debug status from environment
        $this->isDebug = strtolower(bolt_env('APP_DEBUG')) === 'true';

        // Render the exception details
        $this->render();
    }

    private function render(): void
    {
        if ($this->isDebug) {
            $file = $this->getFile();
            $line = $this->getLine();
            $message = $this->getMessage();
            $code = $this->getCode();
            $trace = $this->getTrace();
            $variables = $this->getDefinedVariables();
            $frameworkDetails = $this->getFrameworkDetails();
            $codeSnippet = $this->getCodeSnippet($file, $line);
            $classOrMethod = $this->getClassOrMethod($trace);

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

            echo $this->generateDebugHtml($message, $code, $file, $line, $codeHtml, $traceHtml, $variablesHtml, $frameworkHtml, $classOrMethod);
        } else {
            echo $this->generateProductionHtml();
        }
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

    private function getFrameworkDetails(): array
    {
        $composerFile = get_root_dir() . '/composer.lock';
        $frameworkVersion = 'Unknown';

        if (file_exists($composerFile)) {
            $composerData = json_decode(file_get_contents($composerFile), true);
            if ($composerData && isset($composerData['packages'])) {
                foreach ($composerData['packages'] as $package) {
                    if ($package['name'] === 'celionatti/bolt') {
                        $frameworkVersion = $package['version'];
                        break;
                    }
                }
            }
        }

        return [
            'Framework Version' => "BOLT (PhpStrike) {$frameworkVersion}",
            'PHP Version' => phpversion(),
            'OS' => php_uname(),
        ];
    }

    private function generateProductionHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f4f4f4;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }
        h1 { color: #2E3E50; margin-bottom: 1rem; }
        p { color: #666; line-height: 1.5; }
        .reload-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2E3E50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 1rem;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
        }
        .reload-btn:hover {
            background-color: #1A2732;
        }
    </style>
    <script>
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/'; // Fallback to home page if no history
            }
        }
    </script>
</head>
<body>
    <div class="error-container">
        <h1>Oops! Something went wrong</h1>
        <p>We're sorry, but there was an error processing your request.</p>
        <p>Please try again later or contact support if the problem persists.</p>
        <button onclick="goBack()" class="reload-btn">
            Reload Page
        </button>
    </div>
</body>
</html>
HTML;
    }

    private function generateDebugHtml(
        string $message,
        int $code,
        string $file,
        int $line,
        string $codeHtml,
        string $traceHtml,
        string $variablesHtml,
        string $frameworkHtml,
        string $classOrMethod
    ): string {
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
            border: 1px solid #1A2732;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            padding: 15px;
            background: #1A2732;
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
            border-bottom: 3px solid #2E3E50;
        }
        .tab-content {
            display: none;
            padding: 15px;
        }
        .tab-content > p {
            margin: 0;
            padding: 5px 25px;
            background: #f9f9f9;
            border-bottom: 2px solid #2E3E50;
            text-wrap: wrap;
        }
        .tab-content > ul {
            list-style: square;
        }
        .tab-content > ul > li {
            color: #E1E8F0;
            background: #2E3E50;
            padding: 10px;
            margin: 5px;
            border-bottom: 1px solid #2E3E50;
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
        .flex {
            background: #2E3E50;
            color: #fff;
            margin: 5px 0;
            font-family: monospace;
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            text-align: center;
        }
    </style>
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`.tab[data-tab="\${tabId}"]`).classList.add('active');
        }
    </script>
</head>
<body>
<div class="exception-container">
    <div class="header">BOLT Exception</div>
    <div class="tabs">
        <div class="tab active" data-tab="error-details" onclick="switchTab('error-details')">Error Details</div>
        <div class="tab" data-tab="stack-trace" onclick="switchTab('stack-trace')">Stack Trace</div>
        <div class="tab" data-tab="variables" onclick="switchTab('variables')">Variables</div>
        <div class="tab" data-tab="framework-details" onclick="switchTab('framework-details')">Framework Details</div>
    </div>
    <div id="error-details" class="tab-content active">
        <p><strong>Message:</strong> {$message}</p>
        <div class="flex">
            <p><strong>Code:</strong> {$code}</p>
            <p><strong>Line:</strong> {$line}</p>
        </div>
        <div class="flex">
            <p><strong>File:</strong> {$file}</p>
            <p><strong>Class/Method:</strong> {$classOrMethod}</p>
        </div>
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
