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
    protected $basePath;
    
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
        // Initialize output string
        $output = '';

        // Format the message with initial uppercase
        $formattedMessage = ucfirst($message);

        // Calculate total message length for padding and borders
        $messageLength = strlen($formattedMessage);
        $borderLength = $messageLength + 6; // Borders on both sides

        // Create the timestamp with a more friendly format
        $friendlyTimestamp = $timestamp ? "[" . date("M d, Y - H:i:s") . "] - " : '';

        // Build the top border with asterisks
        $topBorder = str_repeat('*', $borderLength) . PHP_EOL;

        // Calculate padding for centering the message
        $padding = str_repeat(' ', intval(floor(($borderLength - $messageLength) / 2))); // Ensure integer value

        // Build the middle content with borders and padding
        $middleContent = "*{$padding}{$formattedMessage}{$padding}*" . PHP_EOL;

        // Build the bottom border with asterisks
        $bottomBorder = str_repeat('*', $borderLength) . PHP_EOL;

        // Colorize output to light blue
        $output .= "\033[1;36m"; // Light blue color

        // Concatenate all parts: top border, timestamp, middle content, bottom border
        $output .= "{$topBorder}{$friendlyTimestamp}{$middleContent}{$bottomBorder}";

        // Reset color after the message
        $output .= "\033[0m";

        // Output the formatted message
        echo $output . PHP_EOL;

        // Exit script if die flag is set
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

    protected function configure()
    {
        // Get the current file's directory
        $currentDirectory = __DIR__;

        // Navigate up the directory tree until you reach the project's root
        while (!file_exists("{$currentDirectory}/vendor")) {
            // Go up one level
            $currentDirectory = dirname($currentDirectory);

            // Check if you have reached the filesystem root (to prevent infinite loop)
            if ($currentDirectory === '/') {
                $this->message("Error: Could not find project root. Please ensure you are running this command from within a Bolt project.", true, true, "error");
                return;
            }
        }

        $this->basePath = $currentDirectory;
    }
}
