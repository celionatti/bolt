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
	/**
     * Get the current timestamp
     *
     * @return int
     */
    public static function getCurrentTimestamp() {
        return time();
    }

    /**
     * Format a timestamp to a readable date and time
     *
     * @param int $timestamp
     * @param string $format
     * @return string
     */
    public static function formatTimestamp($timestamp, $format = 'Y-m-d H:i:s') {
        return date($format, $timestamp);
    }

    /**
     * Calculate the difference between two timestamps
     *
     * @param int $timestamp1
     * @param int $timestamp2
     * @param string $interval
     * @return int
     */
    public static function getTimestampDifference($timestamp1, $timestamp2, $interval = 'seconds') {
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

    /**
     * Get the current date in a dynamic format
     *
     * @param string $format
     * @return string
     */
    public static function getCurrentDate($format = 'Y-m-d') {
        return date($format);
    }

    /**
     * Add or subtract time to a timestamp
     *
     * @param int $timestamp
     * @param string $modifier
     * @param int $value
     * @return int
     */
    public static function modifyTimestamp($timestamp, $modifier, $value) {
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
    public static function isPast($timestamp) {
        return $timestamp < time();
    }

    /**
     * Check if a timestamp is in the future
     *
     * @param int $timestamp
     * @return bool
     */
    public static function isFuture($timestamp) {
        return $timestamp > time();
    }

    /**
     * Get the day of the week for a given timestamp
     *
     * @param int $timestamp
     * @return string
     */
    public static function getDayOfWeek($timestamp) {
        return date('l', $timestamp);
    }

    /**
     * Get the number of days in a given month
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    public static function getDaysInMonth($month, $year) {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    /**
     * Convert a date string to a timestamp
     *
     * @param string $dateString
     * @param string $format
     * @return int|false
     */
    public static function convertToDateTimestamp($dateString, $format = 'Y-m-d H:i:s') {
        $dateTime = DateTime::createFromFormat($format, $dateString);
        return $dateTime ? $dateTime->getTimestamp() : false;
    }

    /**
     * Get the time ago string for a given timestamp
     *
     * @param int $timestamp
     * @param string $userTimezone
     * @return string
     */
    public static function timeAgo($timestamp, $userTimezone = 'UTC', $suffix = ' ago') {
        $currentTime = time();
        $userTime = new DateTimeZone($userTimezone);
        $serverTime = new DateTimeZone(date_default_timezone_get());

        $dateTime = new DateTime("@$timestamp");
        $dateTime->setTimezone($userTime);

        $interval = $dateTime->diff(new DateTime("@$currentTime"));
        $suffix = ($interval->invert) ? $suffix : '';

        if ($interval->y >= 1) {
            return self::pluralize($interval->y, 'year') . $suffix;
        } elseif ($interval->m >= 1) {
            return self::pluralize($interval->m, 'month') . $suffix;
        } elseif ($interval->d >= 1) {
            return self::pluralize($interval->d, 'day') . $suffix;
        } elseif ($interval->h >= 1) {
            return self::pluralize($interval->h, 'hour') . $suffix;
        } elseif ($interval->i >= 1) {
            return self::pluralize($interval->i, 'minute') . $suffix;
        } else {
            return self::pluralize($interval->s, 'second') . $suffix;
        }
    }

    /**
     * Pluralize a word based on a count
     *
     * @param int $count
     * @param string $word
     * @return string
     */
    private static function pluralize($count, $word) {
        return $count . ' ' . (($count == 1) ? $word : $word . 's');
    }
}