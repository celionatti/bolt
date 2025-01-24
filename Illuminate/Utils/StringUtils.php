<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - StringUtils =============
 * ================================
 */

namespace celionatti\Bolt\Illuminate\Utils;

class StringUtils
{
    protected string $string;

    public function __construct(string $string = '')
    {
        $this->string = $string;
    }

    public static function create(string $string = ''): self
    {
        return new static($string);
    }

    public function excerpt(int $length = 100, string $ending = '...'): string
    {
        return mb_strlen($this->string) <= $length
            ? $this->string
            : mb_substr($this->string, 0, $length) . $ending;
    }

    public function toUpper(): string
    {
        return mb_strtoupper($this->string);
    }

    public function toLower(): string
    {
        return mb_strtolower($this->string);
    }

    public function toTitle(): string
    {
        return mb_convert_case($this->string, MB_CASE_TITLE);
    }

    public function toCamelCase(): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $this->string))));
    }

    public function toSnakeCase(): string
    {
        $str = preg_replace('/[^A-Za-z0-9]+/', '_', $this->string);
        return mb_strtolower(trim($str, '_'));
    }

    public function toKebabCase(): string
    {
        $str = preg_replace('/[^A-Za-z0-9]+/', '-', $this->string);
        return mb_strtolower(trim($str, '-'));
    }

    public function toSlug(string $separator = '-'): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', $separator, $this->string);
        return mb_strtolower(trim($slug, $separator));
    }

    public function truncate(int $length, string $ending = '...'): string
    {
        return mb_strlen($this->string) <= $length
            ? $this->string
            : mb_substr($this->string, 0, $length) . $ending;
    }

    public function contains(string $substring): bool
    {
        return mb_strpos($this->string, $substring) !== false;
    }

    public function startsWith(string $prefix): bool
    {
        return mb_substr($this->string, 0, mb_strlen($prefix)) === $prefix;
    }

    public function endsWith(string $suffix): bool
    {
        return mb_substr($this->string, -mb_strlen($suffix)) === $suffix;
    }

    public function replace(string $search, string $replace): string
    {
        return str_replace($search, $replace, $this->string);
    }

    public function replaceFirst(string $search, string $replace): string
    {
        $position = mb_strpos($this->string, $search);
        return $position !== false
            ? substr_replace($this->string, $replace, $position, mb_strlen($search))
            : $this->string;
    }

    public function replaceLast(string $search, string $replace): string
    {
        $position = mb_strrpos($this->string, $search);
        return $position !== false
            ? substr_replace($this->string, $replace, $position, mb_strlen($search))
            : $this->string;
    }

    public function split(string $delimiter): array
    {
        return explode($delimiter, $this->string);
    }

    public function length(): int
    {
        return mb_strlen($this->string);
    }

    public function trim(string $characterMask = " \t\n\r\0\x0B"): string
    {
        return trim($this->string, $characterMask);
    }

    public function trimStart(string $characterMask = " \t\n\r\0\x0B"): string
    {
        return ltrim($this->string, $characterMask);
    }

    public function trimEnd(string $characterMask = " \t\n\r\0\x0B"): string
    {
        return rtrim($this->string, $characterMask);
    }

    public function stripTags(string $allowableTags = ''): string
    {
        return strip_tags($this->string, $allowableTags);
    }

    public function pad(int $length, string $padString = ' ', int $padType = STR_PAD_RIGHT): string
    {
        return str_pad($this->string, $length, $padString, $padType);
    }

    public function toAscii(): string
    {
        $asciiString = iconv('UTF-8', 'ASCII//TRANSLIT', $this->string);
        return preg_replace('/[^\x20-\x7E]/', '', $asciiString);
    }

    public function repeat(int $multiplier): string
    {
        return str_repeat($this->string, $multiplier);
    }

    public function reverse(): string
    {
        return strrev($this->string);
    }

    public function ucfirst(): string
    {
        return ucfirst($this->toLower());
    }

    public function lcfirst(): string
    {
        return lcfirst($this->string);
    }

    public function capitalizeWords(): string
    {
        return ucwords($this->string);
    }

    // New methods
    public function limit(int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($this->string) <= $limit) {
            return $this->string;
        }
        return mb_substr($this->string, 0, $limit) . $end;
    }

    public function sanitize(bool $stripTags = true): string
    {
        $string = $stripTags ? strip_tags($this->string) : $this->string;
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public function wordCount(): int
    {
        return str_word_count($this->string);
    }

    public function mask(int $startLength = 3, int $endLength = 3, string $maskChar = '*'): string
    {
        $strLength = mb_strlen($this->string);

        if ($strLength <= $startLength + $endLength) {
            return $this->string;
        }

        $start = mb_substr($this->string, 0, $startLength);
        $end = mb_substr($this->string, -$endLength);
        $maskLength = $strLength - $startLength - $endLength;

        return $start . str_repeat($maskChar, $maskLength) . $end;
    }

    public function __toString(): string
    {
        return $this->string;
    }
}