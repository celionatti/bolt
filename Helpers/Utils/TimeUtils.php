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

    public static function getCurrentTimestamp(): int
    {
        return time();
    }

    public static function formatTimestamp(int $timestamp, string $format = self::DEFAULT_FORMAT): string
    {
        return date($format, $timestamp);
    }

    public static function getTimestampDifference(int $timestamp1, int $timestamp2, string $interval = 'seconds'): int|float
    {
        $diff = abs($timestamp1 - $timestamp2);

        switch ($interval) {
            case 'minutes':
                return floor($diff / 60);
            case 'hours':
                return floor($diff / 3600);
            case 'days':
                return floor($diff / 86400);
            case 'months':
                return floor($diff / (86400 * 30));
            case 'years':
                return floor($diff / (86400 * 365));
            default:
                return $diff; // seconds
        }
    }

    public static function getCurrentDate(string $format = 'Y-m-d'): string
    {
        return date($format);
    }

    public static function modifyTimestamp(int $timestamp, string $modifier, int $value): int
    {
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        $date->modify("$value $modifier");

        return $date->getTimestamp();
    }

    public static function isPast(int $timestamp): bool
    {
        return $timestamp < time();
    }

    public static function isFuture(int $timestamp): bool
    {
        return $timestamp > time();
    }

    public static function getDayOfWeek(int $timestamp): string
    {
        return date('l', $timestamp);
    }

    public static function getDaysInMonth(int $month, int $year): int
    {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    public static function convertToDateTimestamp(string $dateString, string $format = self::DEFAULT_FORMAT): int|false
    {
        $dateTime = DateTime::createFromFormat($format, $dateString);

        return $dateTime ? $dateTime->getTimestamp() : false;
    }

    public static function timeAgo($timestamp, $userTimezone = 'UTC', $suffix = ' ago'): string
    {
        // Convert string timestamp to int if needed
        $timestamp = is_numeric($timestamp) ? (int)$timestamp : self::convertToDateTimestamp($timestamp);

        $currentTime = time();
        $userTime = new DateTimeZone($userTimezone);

        $dateTime = self::createDateTimeFromTimestamp($timestamp);

        if (!$dateTime) {
            return 'Invalid timestamp';
        }

        $dateTime->setTimezone($userTime);

        $interval = $dateTime->diff(new DateTime());
        $suffix = ($interval->invert) ? $suffix : '';

        $timeUnits = [
            'year'   => $interval->y,
            'month'  => $interval->m,
            'day'    => $interval->d,
            'hour'   => $interval->h,
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

    public static function isLeapYear(int $year): bool
    {
        return ((($year % 4) === 0) && (($year % 100) !== 0) || (($year % 400) === 0));
    }

    public static function getNextWeekday(int $timestamp, int $weekday): int
    {
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        $date->modify("next {$weekday}");

        return $date->getTimestamp();
    }

    public static function getRandomDateInRange(int $startTimestamp, int $endTimestamp): int
    {
        $randomTimestamp = mt_rand($startTimestamp, $endTimestamp);

        return self::normalizeTimestamp($randomTimestamp);
    }

    private static function createDateTimeFromTimestamp($timestamp): DateTime
    {
        return DateTime::createFromFormat('U', (string)$timestamp);
    }

    private static function pluralize(int $count, string $word): string
    {
        return "$count " . (($count === 1) ? $word : "${word}s");
    }

    private static function normalizeTimestamp(int $timestamp): int
    {
        // Custom logic to normalize the timestamp, if needed
        return $timestamp;
    }
}