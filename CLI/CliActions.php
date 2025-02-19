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
    private const COLOR_PRIMARY = "\033[1;36m";
    private const COLOR_SECONDARY = "\033[0;35m";
    private const COLOR_SUCCESS = "\033[1;32m";
    private const COLOR_WARNING = "\033[1;33m";
    private const COLOR_ERROR = "\033[1;31m";
    private const COLOR_RESET = "\033[0m";

    protected string $basePath;

    public function __construct()
    {
        $this->configure();
    }

    public function message(string $message, string $type = 'info', bool $die = false): void
    {
        $formatted = $this->formatMessageBox($message, $type);
        echo $formatted . PHP_EOL;

        if ($die) {
            exit(1);
        }
    }

    public function prompt(string $question, ?string $default = null): string
    {
        $this->displayQuestionHeader($question);
        $response = $this->readInput('> ');

        return $response !== '' ? $response : (string)$default;
    }

    public function choice(string $question, array $options, ?string $default = null): string
    {
        $this->displayChoiceHeader($question, $options);

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

    protected function formatMessageBox(string $message, string $type): string
    {
        $color = match($type) {
            'success' => self::COLOR_SUCCESS,
            'warning' => self::COLOR_WARNING,
            'error' => self::COLOR_ERROR,
            default => self::COLOR_PRIMARY
        };

        $lines = explode("\n", wordwrap(ucfirst(trim($message)), 60));
        $maxLength = max(array_map('mb_strlen', $lines));
        $border = str_repeat('═', $maxLength + 4);

        $output = [
            $color . $border . self::COLOR_RESET,
            ...array_map(fn($line) => $color . '  ' . str_pad($line, $maxLength) . '  ' . self::COLOR_RESET, $lines),
            $color . $border . self::COLOR_RESET
        ];

        return implode(PHP_EOL, $output);
    }

    private function displayQuestionHeader(string $question): void
    {
        $this->displaySection(
            'Question',
            $question,
            self::COLOR_SECONDARY,
            self::COLOR_PRIMARY
        );
    }

    private function displayChoiceHeader(string $question, array $options): void
    {
        $this->displaySection(
            'Choice',
            $question,
            self::COLOR_SECONDARY,
            self::COLOR_PRIMARY
        );

        foreach ($options as $key => $value) {
            echo self::COLOR_PRIMARY . "  [$key] " . self::COLOR_RESET . $value . PHP_EOL;
        }
    }

    private function displaySection(string $title, string $content, string $titleColor, string $contentColor): void
    {
        $border = str_repeat('─', 60);
        echo PHP_EOL . $titleColor . "┌{$border}┐" . self::COLOR_RESET . PHP_EOL;
        echo $titleColor . "│ " . str_pad("[{$title}]", 58) . " │" . self::COLOR_RESET . PHP_EOL;
        echo $titleColor . "├{$border}┤" . self::COLOR_RESET . PHP_EOL;
        echo $contentColor . "│ " . str_pad($content, 58) . " │" . self::COLOR_RESET . PHP_EOL;
        echo $titleColor . "└{$border}┘" . self::COLOR_RESET . PHP_EOL;
    }

    private function readInput(string $prompt): string
    {
        echo self::COLOR_PRIMARY . $prompt . self::COLOR_RESET;
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

    protected function configure(): void
    {
        $this->basePath = $this->findProjectRoot(__DIR__);
    }

    private function findProjectRoot(string $startingDir): string
    {
        $currentDir = $startingDir;
        $maxDepth = 10;

        while ($maxDepth-- > 0) {
            if (file_exists("{$currentDir}/composer.json")) {
                return $currentDir;
            }

            $parentDir = dirname($currentDir);
            if ($parentDir === $currentDir) {
                break;
            }

            $currentDir = $parentDir;
        }

        throw new RuntimeException(
            "Project root not found. Ensure you're within a Bolt project."
        );
    }
}
