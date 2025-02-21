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
        'primary' => "\033[1;36m",     // Bright Cyan
        'secondary' => "\033[0;35m",    // Purple
        'success' => "\033[1;32m",      // Bright Green
        'warning' => "\033[1;33m",      // Bright Yellow
        'error' => "\033[1;31m",        // Bright Red
        'info' => "\033[1;34m",         // Bright Blue
        'white' => "\033[1;37m",        // Bright White
        'reset' => "\033[0m"            // Reset
    ];

    protected const BOX_CHARS = [
        'horizontal' => '═',
        'vertical' => '║',
        'top-left' => '╔',
        'top-right' => '╗',
        'bottom-left' => '╚',
        'bottom-right' => '╝',
        'middle-left' => '╠',
        'middle-right' => '╣',
        'middle-cross' => '╬',
        'middle-horizontal' => '═',
        'bullet' => '►'
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
        $this->displayEnhancedBox('Question', $question, 'secondary');
        $response = $this->readInput('Answer');

        return $response !== '' ? $response : (string)$default;
    }

    public function choice(string $question, array $options, ?string $default = null): string
    {
        $this->displayEnhancedBox('Choice', $question, 'secondary');
        $this->displayEnhancedOptions($options, $default);

        while (true) {
            $response = $this->readInput('Select');

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
        $reset = self::COLORS['reset'];
        $lines = explode("\n", wordwrap(ucfirst(trim($message)), 56));
        $maxLength = max(array_map('mb_strlen', $lines));
        $border = str_repeat(self::BOX_CHARS['horizontal'], $maxLength + 4);

        $output = [
            $color . self::BOX_CHARS['top-left'] . $border . self::BOX_CHARS['top-right'] . $reset
        ];

        foreach ($lines as $line) {
            $output[] = $color . self::BOX_CHARS['vertical'] . ' ' .
                       self::COLORS['white'] . str_pad($line, $maxLength + 2) .
                       $color . self::BOX_CHARS['vertical'] . $reset;
        }

        $output[] = $color . self::BOX_CHARS['bottom-left'] . $border . self::BOX_CHARS['bottom-right'] . $reset;

        return implode(PHP_EOL, $output);
    }

    protected function displayEnhancedBox(string $title, string $content, string $type = 'primary'): void
    {
        $color = self::COLORS[$type];
        $reset = self::COLORS['reset'];
        $width = 60;

        // Calculate paddings
        $titlePadding = $width - strlen($title) - 4; // -4 for "[ ]" and spaces
        $contentLines = explode("\n", wordwrap($content, $width - 4));

        // Build the box
        $output = [
            // Top border with title
            $color . self::BOX_CHARS['top-left'] . str_repeat(self::BOX_CHARS['horizontal'], $width) . self::BOX_CHARS['top-right'] . $reset,
            $color . self::BOX_CHARS['vertical'] . " [ " . self::COLORS['white'] . $title . $color . " ]" .
                str_repeat(' ', $titlePadding) . self::BOX_CHARS['vertical'] . $reset,
            $color . self::BOX_CHARS['middle-left'] . str_repeat(self::BOX_CHARS['horizontal'], $width) . self::BOX_CHARS['middle-right'] . $reset,
        ];

        // Add content lines
        foreach ($contentLines as $line) {
            $output[] = $color . self::BOX_CHARS['vertical'] . " " . self::COLORS['white'] .
                       str_pad($line, $width - 2) . $color . self::BOX_CHARS['vertical'] . $reset;
        }

        // Add bottom border
        $output[] = $color . self::BOX_CHARS['bottom-left'] .
                   str_repeat(self::BOX_CHARS['horizontal'], $width) .
                   self::BOX_CHARS['bottom-right'] . $reset;

        echo implode(PHP_EOL, $output) . PHP_EOL;
    }

    protected function displayEnhancedOptions(array $options, ?string $default = null): void
    {
        $color = self::COLORS['primary'];
        $reset = self::COLORS['reset'];
        $width = 58; // Matches the box width from displayEnhancedBox

        echo $color . self::BOX_CHARS['middle-left'] .
             str_repeat(self::BOX_CHARS['horizontal'], $width) .
             self::BOX_CHARS['middle-right'] . $reset . PHP_EOL;

        foreach ($options as $key => $value) {
            $isDefault = $key === $default;
            $marker = $isDefault ? self::COLORS['success'] . self::BOX_CHARS['bullet'] : " ";

            $optionText = sprintf(
                "%s %s: %s",
                $marker,
                self::COLORS['info'] . $key . self::COLORS['white'],
                $value
            );

            echo $color . self::BOX_CHARS['vertical'] . " " .
                 str_pad($optionText, $width - 2, ' ') .
                 $color . self::BOX_CHARS['vertical'] . $reset . PHP_EOL;
        }

        echo $color . self::BOX_CHARS['bottom-left'] .
             str_repeat(self::BOX_CHARS['horizontal'], $width) .
             self::BOX_CHARS['bottom-right'] . $reset . PHP_EOL;
    }

    protected function readInput(string $prompt): string
    {
        echo self::COLORS['primary'] . $prompt . ": " . self::COLORS['white'];
        $input = trim(fgets(STDIN) ?: '');
        echo self::COLORS['reset'];
        return $input;
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
            if (file_exists("{$dir}/vendor")) {
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
