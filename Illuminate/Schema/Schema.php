<?php

declare(strict_types=1);

/**
 * =======================================
 * Bolt - Schema Class ===================
 * =======================================
 */

namespace celionatti\Bolt\Illuminate\Schema;

class Schema
{
    protected static $connection;

    public static function setConnection($connection)
    {
        self::$connection = $connection;
    }

    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $sql = $blueprint->toSql();
        self::execute($sql);
    }

    public static function dropIfExists(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS `$table`;";
        self::execute($sql);
    }

    protected static function execute(string $sql): void
    {
        try {
            $statement = self::$connection->prepare($sql);
            $statement->execute();
        } catch (\PDOException $e) {
            self::message("SQL Error: " . $e->getMessage(), true, true, 'error');
        }
    }

    private static function message(string $message, bool $die = false, bool $timestamp = true, string $level = 'info'): void
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
}
