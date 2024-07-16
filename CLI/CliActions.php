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
    protected function simplePrompt(string $prompt): string
    {
        echo $prompt;
        return trim(fgets(STDIN));
    }

    protected function prompt($question, $default = null)
    {
        $prompt = $question;
        if ($default !== null) {
            $prompt .= " [$default]";
        }
        $prompt .= ": ";

        $borderLength = strlen($prompt) + 6;
        $border = str_repeat('**', $borderLength);

        // Colors
        $colorBorder = "\033[0;35m"; // Blue
        $colorText = "\033[0;36m";   // Green
        $resetColor = "\033[0m";     // Reset color

        echo PHP_EOL . $colorBorder . $border . $resetColor . PHP_EOL;
        echo $colorBorder . "** " . $colorText . $prompt . $colorBorder . " **" . $resetColor . PHP_EOL;
        echo $colorBorder . $border . $resetColor . PHP_EOL;

        $response = function_exists('readline') ? readline('> ') : $this->simplePrompt('> ');

        if ($response === '') {
            return $default;
        }

        return $response;
    }

    protected function promptOptions($question, array $options, $default = null)
    {
        $borderLength = strlen($question) + 6;
        foreach ($options as $key => $value) {
            $optionLine = "[$key] $value";
            if (strlen($optionLine) + 6 > $borderLength) {
                $borderLength = strlen($optionLine) + 6;
            }
        }
        $border = str_repeat('**', $borderLength);

        // Colors
        $colorBorder = "\033[0;35m"; // Blue
        $colorText = "\033[0;36m";   // Green
        $resetColor = "\033[0m";     // Reset color

        echo PHP_EOL . $colorBorder . $border . $resetColor . PHP_EOL;
        echo $colorBorder . "** " . $colorText . $question . $colorBorder . " **" . $resetColor . PHP_EOL;
        echo " " . PHP_EOL;
        foreach ($options as $key => $value) {
            echo $colorBorder . "** " . $colorText . "[$key] $value" . $colorBorder . " **" . $resetColor . PHP_EOL;
            echo " " . PHP_EOL;
        }

        $prompt = "Select an option";
        if ($default !== null) {
            $prompt .= " [$default]";
        }
        $prompt .= ": ";
        $borderPrompt = str_repeat('**', strlen($prompt) + 6);

        echo $colorBorder . $borderPrompt . $resetColor . PHP_EOL;
        echo $colorBorder . "** " . $colorText . $prompt . $colorBorder . " **" . $resetColor . PHP_EOL;
        echo $colorBorder . $borderPrompt . $resetColor . PHP_EOL;

        while (true) {
            $response = function_exists('readline') ? readline('> ') : $this->simplePrompt('> ');

            if ($response === '' && $default !== null) {
                return $default;
            }

            if (isset($options[$response])) {
                return $response;
            }

            $this->output("Invalid option. Please try again.", 1);
        }
    }

    protected function output(string $message, int $indentation = 0): void
    {
        echo str_repeat(' ', $indentation * 2) . $message . PHP_EOL;
    }

    public function message(string $message, bool $die = false, bool $timestamp = true, string $level = 'info'): void
    {
        $output = '';

        if ($timestamp) {
            $output .= "[" . date("Y-m-d H:i:s") . "] - ";
        }

        $output .= ucfirst($message) . PHP_EOL;

        switch ($level) {
            case 'info':
                $output = "\033[0;32m{$output}"; // Green color for info
                break;
            case 'warning':
                $output = "\033[0;33m{$output}"; // Yellow color for warning
                break;
            case 'error':
                $output = "\033[0;31m{$output}"; // Red color for error
                break;
            default:
                break;
        }

        $output .= "\033[0m"; // Reset color

        echo $output . PHP_EOL;

        if ($die) {
            die();
        }
    }

    protected function rename_camel_case($value)
    {
        $name = preg_replace('/[_-]/', ' ', $value);
        $parts = explode(' ', $name);
        $value = '';
        foreach ($parts as $part) {
            $value .= ucfirst($part);
        }

        return $value;
    }
}
