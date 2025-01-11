<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - Debug ==================
 * ================================
 */

namespace celionatti\Bolt\Debug;

class Debug
{
    public static function dump($variable)
    {
        ob_start();
        var_dump($variable);
        $dumpedOutput = ob_get_clean();

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $caller = $backtrace[1];
        $file = $caller['file'];
        $line = $caller['line'];

        $codeSnippet = self::getCodeSnippet($file, $line);
        $stackTrace = self::formatStackTrace($backtrace);
        $frameworkDetails = self::getFrameworkDetails();

        $html = self::generateHtml($dumpedOutput, $stackTrace, $file, $line, $codeSnippet, $frameworkDetails);

        // Output and terminate execution
        echo $html;
        exit;
    }

    private static function getCodeSnippet($file, $line, $context = 5)
    {
        if (!is_readable($file)) {
            return 'Code snippet unavailable.';
        }

        $lines = file($file);
        $start = max($line - $context - 1, 0);
        $end = min($line + $context - 1, count($lines));

        $snippet = array_slice($lines, $start, $end - $start + 1, true);

        return array_map(fn($codeLine, $index) => [
            'number' => $index + 1,
            'code' => $codeLine
        ], $snippet, array_keys($snippet));
    }

    private static function formatStackTrace($backtrace)
    {
        $formattedTrace = [];
        foreach ($backtrace as $index => $trace) {
            $formattedTrace[] = sprintf(
                "#%d %s%s%s(%s) called at [%s:%d]",
                $index,
                $trace['class'] ?? '',
                $trace['type'] ?? '',
                $trace['function'] ?? '',
                implode(', ', array_map('gettype', $trace['args'] ?? [])),
                $trace['file'] ?? 'unknown file',
                $trace['line'] ?? 0
            );
        }

        return implode("\n", $formattedTrace);
    }

    private static function getFrameworkDetails()
    {
        return [
            'Framework Version' => '1.0.0', // Example version, update this dynamically if possible
            'PHP Version' => phpversion(),
            'Server Time' => date('Y-m-d H:i:s'),
            'OS' => PHP_OS,
            'Memory Usage' => self::formatBytes(memory_get_usage(true)),
            'Loaded Extensions' => implode(', ', get_loaded_extensions())
        ];
    }

    private static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private static function generateHtml($dump, $stackTrace, $file, $line, $codeSnippet, $frameworkDetails)
    {
        $traceHtml = nl2br(htmlspecialchars($stackTrace, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        $codeHtml = '';
        foreach ($codeSnippet as $lineInfo) {
            $isHighlighted = $lineInfo['number'] == $line;
            $codeHtml .= sprintf(
                '<div class="%s"><span class="line-number">%d</span> <span class="code-text">%s</span></div>',
                $isHighlighted ? 'highlight' : '',
                $lineInfo['number'],
                htmlspecialchars($lineInfo['code'], ENT_QUOTES | ENT_HTML5, 'UTF-8')
            );
        }

        $frameworkDetailsHtml = '';
        foreach ($frameworkDetails as $key => $value) {
            $frameworkDetailsHtml .= sprintf(
                '<div class="detail"><strong>%s:</strong> %s</div>',
                $key,
                htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')
            );
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Dump</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            color: #333;
        }
        .debug-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 90%;
            padding: 20px;
            border: 1px solid tomato;
        }
        .tabs {
            display: flex;
            background: tomato;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .tab {
            flex: 1;
            text-align: center;
            padding: 10px 15px;
            cursor: pointer;
            color: white;
        }
        .tab.active {
            background: white;
            color: tomato;
            border-bottom: 2px solid tomato;
        }
        .tab-content {
            display: none;
            padding: 20px;
        }
        .tab-content.active {
            display: block;
        }
        .stack-trace {
            font-family: monospace;
            background: #ffe8e5;
            padding: 10px;
            border-radius: 8px;
            white-space: pre-wrap;
        }
        .code {
            font-family: monospace;
            background: #f9f9f9;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            overflow-x: auto;
        }
        .code .line-number {
            color: #999;
            margin-right: 10px;
        }
        .code .code-text {
            color: #333;
        }
        .code .highlight {
            background: #ffdddd;
            color: black;
        }
        .framework-details {
            background: #eaf0f0;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        .detail {
            margin-bottom: 10px;
        }
        .detail strong {
            color: tomato;
        }
    </style>
</head>
<body>
<div class="debug-container">
    <div class="tabs">
        <div class="tab active" data-tab="variables">Variables</div>
        <div class="tab" data-tab="stack-trace">Stack Trace</div>
        <div class="tab" data-tab="code">Code</div>
        <div class="tab" data-tab="framework-details">Framework Details</div>
    </div>
    <div class="tab-content active" id="variables">
        <pre>{$dump}</pre>
    </div>
    <div class="tab-content" id="stack-trace">
        <div class="stack-trace">{$traceHtml}</div>
    </div>
    <div class="tab-content" id="code">
        <div class="code">{$codeHtml}</div>
    </div>
    <div class="tab-content" id="framework-details">
        <div class="framework-details">
            {$frameworkDetailsHtml}
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(tab.getAttribute('data-tab')).classList.add('active');
        });
    });
</script>
</body>
</html>
HTML;
    }
}
