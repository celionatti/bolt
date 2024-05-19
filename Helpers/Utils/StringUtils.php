<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - StringUtils =============
 * ================================
 */

namespace celionatti\Bolt\Helpers\Utils;

class StringUtils
{
    /**
     * Generate a random token
     *
     * @param int $length
     * @return string
     */
    public static function generateRandomToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate a random string of a specified length
     *
     * @param int $length
     * @return string
     */
    public static function generateRandomStr(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * Convert a string to a URL-friendly format
     *
     * @param string $input
     * @param array $options
     * @return string
     */
    public static function stringToUrl(string $input, array $options = []): string
    {
        // Set default options
        $defaults = [
            'replaceSpaces' => true,
            'removeSpecialChars' => true,
            'convertToLowercase' => true,
            'preserveDiacritics' => false,
            'separator' => '-',
        ];

        // Merge provided options with defaults
        $options = array_merge($defaults, $options);

        // Remove diacritics (accents) from characters
        if ($options['preserveDiacritics']) {
            $input = self::removeDiacritics($input);
        }

        // Replace spaces with a separator
        if ($options['replaceSpaces']) {
            $input = str_replace(' ', $options['separator'], $input);
        }

        // Remove special characters except for those commonly found in URL queries
        if ($options['removeSpecialChars']) {
            $input = preg_replace('/[^a-zA-Z0-9\-_&?=]/', '', $input);
        }

        // Convert the string to lowercase
        if ($options['convertToLowercase']) {
            $input = strtolower($input);
        }

        // Replace consecutive separators
        $input = preg_replace('/' . preg_quote($options['separator'], '/') . '+/', $options['separator'], $input);

        return $input;
    }

    /**
     * Reverse the process - Convert a URL-friendly string to the original format
     *
     * @param string $urlString
     * @param string $separator
     * @return string
     */
    public static function reverseStringToUrl(string $urlString, string $separator = '-'): string
    {
        $urlString = str_replace($separator, ' ', $urlString);
        return ucwords($urlString);
    }

    /**
     * Remove diacritics (accents) from characters
     *
     * @param string $str
     * @return string
     */
    private static function removeDiacritics(string $str): string
    {
        $str = htmlentities($str, ENT_COMPAT, 'UTF-8');
        $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde);/', '$1', $str);
        return html_entity_decode($str);
    }

    /**
     * Generate a reference number like those used in banking or ticketing systems
     *
     * @param int $length
     * @param string $prefix
     * @return string
     */
    public static function generateReferenceNumber(int $length = 10, string $prefix = 'REF'): string
    {
        $prefixLength = strlen($prefix);
        $numericLength = max(0, $length - $prefixLength);

        $numericPart = '';
        for ($i = 0; $i < $numericLength; $i++) {
            $numericPart .= random_int(0, 9);
        }

        $checksum = self::calculateChecksum($numericPart);

        return $prefix . $numericPart . $checksum;
    }

    /**
     * Calculate a simple checksum based on the numeric portion
     *
     * @param string $numericPart
     * @return int
     */
    private static function calculateChecksum(string $numericPart): int
    {
        $checksum = 0;

        for ($i = 0, $len = strlen($numericPart); $i < $len; $i++) {
            $checksum += (int)$numericPart[$i];
        }

        return $checksum % 10;
    }

    /**
     * Convert a string to uppercase
     *
     * @param string $str
     * @return string
     */
    public static function toUpperCase(string $str): string
    {
        return strtoupper($str);
    }

    /**
     * Convert a string to lowercase
     *
     * @param string $str
     * @return string
     */
    public static function toLowerCase(string $str): string
    {
        return strtolower($str);
    }

    /**
     * Replace a substring within a string
     *
     * @param string $haystack
     * @param string $needle
     * @param string $replacement
     * @return string
     */
    public static function replace(string $haystack, string $needle, string $replacement): string
    {
        return str_replace($needle, $replacement, $haystack);
    }

    /**
     * Generate an excerpt from a string
     *
     * @param string $str
     * @param int $length
     * @param string $suffix
     * @return string
     */
    public static function excerpt(string $str, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($str) <= $length) {
            return $str;
        }

        $lastSpace = strrpos(substr($str, 0, $length - strlen($suffix)), ' ');
        $excerpt = substr($str, 0, $lastSpace);

        return rtrim($excerpt, " ,.!") . $suffix;
    }

    /**
     * Check if a string contains another string
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }
}