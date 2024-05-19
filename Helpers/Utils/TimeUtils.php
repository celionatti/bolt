<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - TimeUtils ===============
 * ================================
 */

namespace celionatti\Bolt\Helpers\Utils;

use DateTime;
use DateTimeZone;

class TimeUtils
{
    public const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    /**
     * Get the current timestamp
     *
     * @return int
     */
    public static function getCurrentTimestamp(): int
    {
        return time();
    }

    /**
     * Format a timestamp
     *
     * @param int $timestamp
     * @param string $format
     * @return string
     */
    public static function formatTimestamp(int $timestamp, string $format = self::DEFAULT_FORMAT): string
    {
        return date($format, $timestamp);
    }

    /**
     * Get the difference between two timestamps in the specified interval
     *
     * @param int $timestamp1
     * @param int $timestamp2
     * @param string $interval
     * @return int|float
     */
    public static function getTimestampDifference(int $timestamp1, int $timestamp2, string $interval = 'seconds'): int|float
    {
        $diff = abs($timestamp1 - $timestamp2);

        return match ($interval) {
            'minutes' => floor($diff / 60),
            'hours' => floor($diff / 3600),
            'days' => floor($diff / 86400),
            'months' => floor($diff / (86400 * 30)),
            'years' => floor($diff / (86400 * 365)),
            default => $diff, // seconds
        };
    }

    /**
     * Get the current date
     *
     * @param string $format
     * @return string
     */
    public static function getCurrentDate(string $format = 'Y-m-d'): string
    {
        return date($format);
    }

    /**
     * Modify a timestamp by a specified value and unit
     *
     * @param int $timestamp
     * @param string $modifier
     * @param int $value
     * @return int
     */
    public static function modifyTimestamp(int $timestamp, string $modifier, int $value): int
    {
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        $date->modify("$value $modifier");

        return $date->getTimestamp();
    }

    /**
     * Check if a timestamp is in the past
     *
     * @param int $timestamp
     * @return bool
     */
    public static function isPast(int $timestamp): bool
    {
        return $timestamp < time();
    }

    /**
     * Check if a timestamp is in the future
     *
     * @param int $timestamp
     * @return bool
     */
    public static function isFuture(int $timestamp): bool
    {
        return $timestamp > time();
    }

    /**
     * Get the day of the week for a given timestamp
     *
     * @param int $timestamp
     * @return string
     */
    public static function getDayOfWeek(int $timestamp): string
    {
        return date('l', $timestamp);
    }

    /**
     * Get the number of days in a given month and year
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    public static function getDaysInMonth(int $month, int $year): int
    {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    /**
     * Convert a date string to a timestamp
     *
     * @param string $dateString
     * @param string $format
     * @return int|false
     */
    public static function convertToDateTimestamp(string $dateString, string $format = self::DEFAULT_FORMAT): int|false
    {
        $dateTime = DateTime::createFromFormat($format, $dateString);

        return $dateTime ? $dateTime->getTimestamp() : false;
    }

    /**
     * Get a human-readable "time ago" string for a timestamp
     *
     * @param int|string $timestamp
     * @param string $userTimezone
     * @param string $suffix
     * @return string
     */
    public static function timeAgo(int|string $timestamp, string $userTimezone = 'UTC', string $suffix = ' ago'): string
    {
        $timestamp = is_numeric($timestamp) ? (int)$timestamp : self::convertToDateTimestamp($timestamp);

        if ($timestamp === false) {
            return 'Invalid timestamp';
        }

        $currentTime = new DateTime('now', new DateTimeZone($userTimezone));
        $dateTime = self::createDateTimeFromTimestamp($timestamp);
        $dateTime->setTimezone(new DateTimeZone($userTimezone));

        $interval = $dateTime->diff($currentTime);
        $suffix = ($interval->invert) ? '' : $suffix;

        $timeUnits = [
            'year' => $interval->y,
            'month' => $interval->m,
            'day' => $interval->d,
            'hour' => $interval->h,
            'minute' => $interval->i,
            'second' => $interval->s,
        ];

        foreach ($timeUnits as $unit => $value) {
            if ($value >= 1) {
                return self::pluralize($value, $unit) . $suffix;
            }
        }

        return self::pluralize($interval->s, 'second') . $suffix;
    }

    /**
     * Check if a year is a leap year
     *
     * @param int $year
     * @return bool
     */
    public static function isLeapYear(int $year): bool
    {
        return (($year % 4) === 0 && ($year % 100) !== 0) || ($year % 400 === 0);
    }

    /**
     * Get the timestamp for the next specified weekday
     *
     * @param int $timestamp
     * @param string $weekday
     * @return int
     */
    public static function getNextWeekday(int $timestamp, string $weekday): int
    {
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        $date->modify("next $weekday");

        return $date->getTimestamp();
    }

    /**
     * Get a random timestamp within a specified range
     *
     * @param int $startTimestamp
     * @param int $endTimestamp
     * @return int
     */
    public static function getRandomDateInRange(int $startTimestamp, int $endTimestamp): int
    {
        return random_int($startTimestamp, $endTimestamp);
    }

    /**
     * Create a DateTime object from a timestamp
     *
     * @param int|string $timestamp
     * @return DateTime|false
     */
    private static function createDateTimeFromTimestamp(int|string $timestamp): DateTime|false
    {
        return DateTime::createFromFormat('U', (string)$timestamp);
    }

    /**
     * Pluralize a word based on the count
     *
     * @param int $count
     * @param string $word
     * @return string
     */
    private static function pluralize(int $count, string $word): string
    {
        return "$count " . ($count === 1 ? $word : "${word}s");
    }
}
