<?php

declare(strict_types=1);

/**
 * ============================================
 * Bolt - CliActions =============================
 * ============================================
 */

namespace celionatti\Bolt\CLI;


class CliActions
{
    protected const COLORS = [
        'primary' => "\033[1;36m",
        'secondary' => "\033[0;35m",
        'success' => "\033[1;32m",
        'warning' => "\033[1;33m",
        'error' => "\033[1;31m",
        'reset' => "\033[0m"
    ];

    protected readonly string $basePath;

    public function __construct()
    {
        $this->basePath = $this->findProjectRoot(__DIR__);
    }

    public function message(string $message, string $type = 'info', bool $die = false): void
    {
        echo $this->formatMessageBox($message, $type) . PHP_EOL;

        if ($die) {
            exit(1);
        }
    }

    public function prompt(string $question, ?string $default = null): string
    {
        $this->displayBox('Question', $question);
        echo PHP_EOL; // Add line break before input
        $response = $this->readInput('> ');

        return $response !== '' ? $response : (string)$default;
    }

    public function choice(string $question, array $options, ?string $default = null): string
    {
        $this->displayBox('Choice', $question);
        $this->displayOptions($options);
        echo PHP_EOL; // Add line break before input

        while (true) {
            $response = $this->readInput('Select: ');

            if ($response === '' && $default !== null) {
                return $default;
            }

            if (isset($options[$response])) {
                return $response;
            }

            $this->message("Invalid option: {$response}", 'error');
        }
    }

    public function confirm(string $question, bool $default = true): bool
    {
        $suffix = $default ? ' [Y/n]' : ' [y/N]';
        $response = strtolower($this->prompt($question . $suffix));

        if ($response === '') {
            return $default;
        }

        return str_starts_with($response, 'y');
    }

    public function output(string $message): void
    {
        echo $message . PHP_EOL;
    }

    protected function formatMessageBox(string $message, string $type): string
    {
        $color = self::COLORS[$type] ?? self::COLORS['primary'];
        $lines = explode("\n", wordwrap(ucfirst(trim($message)), 60));
        $maxLength = max(array_map('mb_strlen', $lines));
        $border = str_repeat('═', $maxLength + 4);

        return implode(PHP_EOL, [
            $color . $border . self::COLORS['reset'],
            ...array_map(
                fn($line) => $color . '║ ' . str_pad($line, $maxLength) . ' ║' . self::COLORS['reset'],
                $lines
            ),
            $color . $border . self::COLORS['reset']
        ]);
    }

    protected function displayBox(string $title, string $content): void
    {
        $border = str_repeat('─', 60);
        $titleColor = self::COLORS['secondary'];
        $contentColor = self::COLORS['primary'];
        $reset = self::COLORS['reset'];

        $output = [
            $titleColor . "┌{$border}┐" . $reset,
            $titleColor . "│ " . str_pad("[{$title}]", 58) . " │" . $reset,
            $titleColor . "├{$border}┤" . $reset,
            $contentColor . "│ " . str_pad($content, 58) . " │" . $reset,
            $titleColor . "└{$border}┘" . $reset,
        ];

        echo implode(PHP_EOL, $output);
    }

    protected function displayOptions(array $options): void
    {
        echo PHP_EOL; // Add line break before options
        foreach ($options as $key => $value) {
            echo self::COLORS['primary'] . "  [$key] " .
                 self::COLORS['reset'] . $value . PHP_EOL;
        }
    }

    protected function readInput(string $prompt): string
    {
        echo self::COLORS['primary'] . $prompt . self::COLORS['reset'] . ' ';
        return trim(fgets(STDIN) ?: '');
    }

    protected function pascalCase(string $value): string
    {
        if ($value === '') {
            return '';
        }

        return str_replace(' ', '', ucwords(
            preg_replace('/[^a-zA-Z0-9\x7f-\xff]++/', ' ', $value)
        ));
    }

    private function findProjectRoot(string $startingDir): string
    {
        $dir = $startingDir;
        $maxDepth = 10;

        while ($maxDepth-- > 0) {
            if (file_exists("{$dir}/composer.json")) {
                return $dir;
            }

            $parentDir = dirname($dir);
            if ($parentDir === $dir) {
                break;
            }

            $dir = $parentDir;
        }

        throw new RuntimeException(
            "Project root not found. Ensure you're within a Bolt project."
        );
    }
}
