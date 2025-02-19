<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - ConsoleLogger ===========
 * ================================
 */

namespace celionatti\Bolt\Helpers\Console;

class ConsoleLogger
{
    private const ANSI_RESET = "\033[0m";
    private const ANSI_BOLD = "\033[1m";
    private const ANSI_BLUE = "\033[34m";
    private const ANSI_CYAN = "\033[36m";
    private const ANSI_WHITE_BG_BLUE = "\033[97;44m";
    private const ANSI_YELLOW = "\033[33m";

    public static function log(string $message, bool $die = false, bool $timestamp = true, string $title = ''): void
    {
        $output = self::createOutput(self::formatMessage($message), $title, $timestamp);

        echo $output . PHP_EOL;

        if ($die) {
            exit(1);
        }
    }

    private static function createOutput(string $message, string $title, bool $timestamp): string
    {
        $output = [];

        if ($title !== '') {
            $output[] = self::createTitleSection($title);
        }

        $output[] = self::createMessageBox(
            $message,
            $timestamp ? self::formatTimestamp() : ''
        );

        return implode(PHP_EOL, $output);
    }

    private static function createTitleSection(string $title): string
    {
        $formattedTitle = strtoupper(trim($title));
        $padding = str_repeat(' ', 2);

        return self::applyStyle(
            "{$padding}{$formattedTitle}{$padding}",
            self::ANSI_WHITE_BG_BLUE
        );
    }

    private static function createMessageBox(string $message, string $timestamp): string
    {
        $messageLength = mb_strlen($message);
        $borderLength = $messageLength + 4;
        $border = self::createBorder($borderLength);
        $padding = str_repeat(' ', (int)(($borderLength - $messageLength) / 2);

        $box = [
            $border,
            self::applyStyle("  {$padding}{$message}{$padding}  ", self::ANSI_BLUE),
            $border
        ];

        if ($timestamp !== '') {
            array_unshift($box, self::applyStyle($timestamp, self::ANSI_YELLOW));
        }

        return implode(PHP_EOL, $box);
    }

    private static function createBorder(int $length): string
    {
        return self::applyStyle(str_repeat('═', $length), self::ANSI_CYAN);
    }

    private static function formatMessage(string $message): string
    {
        return ucfirst(trim($message));
    }

    private static function formatTimestamp(): string
    {
        return sprintf("[%s]", date("M d, Y - H:i:s"));
    }

    private static function applyStyle(string $text, string $style): string
    {
        return self::ANSI_BOLD . $style . $text . self::ANSI_RESET;
    }
}