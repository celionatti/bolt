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
    // Generate a random token
    public static function generateRandomToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    // Generate a random string of a specified length
    public static function generateRandomStr($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    // Convert a string to a URL-friendly format
    public static function stringToUrl($input, $options = [])
    {
        // Set default options
        $defaults = [
            'replaceSpaces' => true,
            'removeSpecialChars' => true,
            'convertToLowercase' => true,
            'preserveDiacritics' => true,
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

        // Remove special characters
        if ($options['removeSpecialChars']) {
            $input = preg_replace('/[^a-zA-Z0-9\-_]/', '', $input);
        }

        // Convert the string to lowercase
        if ($options['convertToLowercase']) {
            $input = strtolower($input);
        }

        return $input;
    }

    // Reverse the process - Convert a URL-friendly string to the original format
    public static function reverseStringToUrl($urlString, $separator = '-')
    {
        $urlString = str_replace($separator, ' ', $urlString);
        $urlString = ucwords($urlString);
        return $urlString;
    }

    // Remove diacritics (accents) from characters
    private static function removeDiacritics($str)
    {
        $str = htmlentities($str, ENT_COMPAT, 'UTF-8');
        $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde);/', '$1', $str);
        return html_entity_decode($str);
    }

    // Generate a reference number like those used in banking or ticketing systems
    public static function generateReferenceNumber($length = 10, $prefix = 'REF')
    {
        // Ensure the prefix is included in the final length
        $prefixLength = strlen($prefix);
        $numericLength = max(0, $length - $prefixLength);

        // Generate a random numeric portion
        $numericPart = '';
        for ($i = 0; $i < $numericLength; $i++) {
            $numericPart .= mt_rand(0, 9);
        }

        // Calculate a simple checksum based on the numeric portion
        $checksum = self::calculateChecksum($numericPart);

        // Combine the prefix, numeric part, and checksum
        $referenceNumber = $prefix . $numericPart . $checksum;

        return $referenceNumber;
    }

    // Calculate a simple checksum based on the numeric portion
    private static function calculateChecksum($numericPart)
    {
        $checksum = 0;

        // Sum the digits of the numeric portion
        for ($i = 0; $i < strlen($numericPart); $i++) {
            $checksum += (int)$numericPart[$i];
        }

        // Take the last digit of the sum
        $checksum = $checksum % 10;

        return $checksum;
    }

    /**
     * Convert a string to uppercase
     *
     * @param string $str
     * @return string
     */
    public static function toUpperCase($str) {
        return strtoupper($str);
    }

    /**
     * Convert a string to lowercase
     *
     * @param string $str
     * @return string
     */
    public static function toLowerCase($str) {
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
    public static function replace($haystack, $needle, $replacement) {
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
    public static function excerpt($str, $length = 100, $suffix = '...') {
        if (strlen($str) <= $length) {
            return $str;
        }
        $excerpt = substr($str, 0, $length - strlen($suffix));
        return rtrim($excerpt, " ,.!") . $suffix;
    }

    /**
     * Check if a string contains another string
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function contains($haystack, $needle) {
        return strpos($haystack, $needle) !== false;
    }
}