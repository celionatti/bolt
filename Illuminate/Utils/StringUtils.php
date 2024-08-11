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
    protected $string;

    public function __construct($string = '')
    {
        $this->string = $string;
    }

    public static function create($string = '')
    {
        return new static($string);
    }

    public function excerpt($length = 100, $ending = '...')
    {
        if (mb_strlen($this->string) <= $length) {
            return $this->string;
        }
        
        return mb_substr($this->string, 0, $length) . $ending;
    }

    public function toUpper()
    {
        return mb_strtoupper($this->string);
    }

    public function toLower()
    {
        return mb_strtolower($this->string);
    }

    public function toTitle()
    {
        return mb_convert_case($this->string, MB_CASE_TITLE);
    }

    public function toCamelCase()
    {
        $str = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $this->string)));
        return lcfirst($str);
    }

    public function toSnakeCase()
    {
        $str = preg_replace('/[^A-Za-z0-9]+/', '_', $this->string);
        return mb_strtolower(trim($str, '_'));
    }

    public function toKebabCase()
    {
        $str = preg_replace('/[^A-Za-z0-9]+/', '-', $this->string);
        return mb_strtolower(trim($str, '-'));
    }

    public function toSlug($separator = '-')
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', $separator, $this->string);
        return mb_strtolower(trim($slug, $separator));
    }

    public function truncate($length, $ending = '...')
    {
        if (mb_strlen($this->string) <= $length) {
            return $this->string;
        }

        return mb_substr($this->string, 0, $length) . $ending;
    }

    public function contains($substring)
    {
        return mb_strpos($this->string, $substring) !== false;
    }

    public function startsWith($prefix)
    {
        return mb_substr($this->string, 0, mb_strlen($prefix)) === $prefix;
    }

    public function endsWith($suffix)
    {
        return mb_substr($this->string, -mb_strlen($suffix)) === $suffix;
    }

    public function replace($search, $replace)
    {
        return str_replace($search, $replace, $this->string);
    }

    public function replaceFirst($search, $replace)
    {
        $position = mb_strpos($this->string, $search);

        if ($position !== false) {
            return substr_replace($this->string, $replace, $position, mb_strlen($search));
        }

        return $this->string;
    }

    public function replaceLast($search, $replace)
    {
        $position = mb_strrpos($this->string, $search);

        if ($position !== false) {
            return substr_replace($this->string, $replace, $position, mb_strlen($search));
        }

        return $this->string;
    }

    public function split($delimiter)
    {
        return explode($delimiter, $this->string);
    }

    public function length()
    {
        return mb_strlen($this->string);
    }

    public function trim($characterMask = " \t\n\r\0\x0B")
    {
        return trim($this->string, $characterMask);
    }

    public function trimStart($characterMask = " \t\n\r\0\x0B")
    {
        return ltrim($this->string, $characterMask);
    }

    public function trimEnd($characterMask = " \t\n\r\0\x0B")
    {
        return rtrim($this->string, $characterMask);
    }

    public function stripTags($allowableTags = '')
    {
        return strip_tags($this->string, $allowableTags);
    }

    public function pad($length, $padString = ' ', $padType = STR_PAD_RIGHT)
    {
        return str_pad($this->string, $length, $padString, $padType);
    }

    public function toAscii()
    {
        $asciiString = iconv('UTF-8', 'ASCII//TRANSLIT', $this->string);
        return preg_replace('/[^\x20-\x7E]/', '', $asciiString);
    }

    public function repeat($multiplier)
    {
        return str_repeat($this->string, $multiplier);
    }

    public function reverse()
    {
        return strrev($this->string);
    }

    public function random($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function ucfirst()
    {
        return ucfirst($this->toLower());
    }

    public function lcfirst()
    {
        return lcfirst($this->string);
    }

    public function capitalizeWords()
    {
        return ucwords($this->string);
    }

    public function __toString()
    {
        return $this->string;
    }
}