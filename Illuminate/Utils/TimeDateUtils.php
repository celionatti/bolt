<?php

declare(strict_types=1);

/**
 * ================================
 * Bolt - TimeUtils ===============
 * ================================
 */

namespace celionatti\Bolt\Illuminate\Utils;

use DateTime;
use DateTimeZone;
use Exception;

class TimeDateUtils
{
    protected $dateTime;

    public function __construct($dateTime = 'now', $timeZone = 'UTC')
    {
        try {
            $this->dateTime = new DateTime($dateTime, new DateTimeZone($timeZone));
        } catch (Exception $e) {
            // Handle invalid date/time format
            $this->dateTime = new DateTime('now', new DateTimeZone('UTC'));
        }
    }

    public static function create($dateTime = 'now', $timeZone = 'UTC')
    {
        return new static($dateTime, $timeZone);
    }

    public function toAgoFormat()
    {
        $now = new DateTime('now', $this->dateTime->getTimezone());
        $diff = $now->diff($this->dateTime);

        if ($diff->y > 0) {
            return $diff->y . ' ' . ($diff->y > 1 ? 'years' : 'year') . ' ago';
        }

        if ($diff->m > 0) {
            return $diff->m . ' ' . ($diff->m > 1 ? 'months' : 'month') . ' ago';
        }

        if ($diff->d > 0) {
            return $diff->d . ' ' . ($diff->d > 1 ? 'days' : 'day') . ' ago';
        }

        if ($diff->h > 0) {
            return $diff->h . ' ' . ($diff->h > 1 ? 'hours' : 'hour') . ' ago';
        }

        if ($diff->i > 0) {
            return $diff->i . ' ' . ($diff->i > 1 ? 'minutes' : 'minute') . ' ago';
        }

        return $diff->s . ' ' . ($diff->s > 1 ? 'seconds' : 'second') . ' ago';
    }

    public function toBlogFormat()
    {
        return $this->dateTime->format('d-F-Y');
    }

    public function toCustomFormat($format)
    {
        return $this->dateTime->format($format);
    }

    public function toISO8601()
    {
        return $this->dateTime->format(DateTime::ATOM);
    }

    public function toRFC2822()
    {
        return $this->dateTime->format(DateTime::RFC2822);
    }

    public function toUnixTimestamp()
    {
        return $this->dateTime->getTimestamp();
    }

    public function addDays($days)
    {
        $this->dateTime->modify("+{$days} days");
        return $this;
    }

    public function subDays($days)
    {
        $this->dateTime->modify("-{$days} days");
        return $this;
    }

    public function addHours($hours)
    {
        $this->dateTime->modify("+{$hours} hours");
        return $this;
    }

    public function subHours($hours)
    {
        $this->dateTime->modify("-{$hours} hours");
        return $this;
    }

    public function addMinutes($minutes)
    {
        $this->dateTime->modify("+{$minutes} minutes");
        return $this;
    }

    public function subMinutes($minutes)
    {
        $this->dateTime->modify("-{$minutes} minutes");
        return $this;
    }

    public function setTimezone($timeZone)
    {
        $this->dateTime->setTimezone(new DateTimeZone($timeZone));
        return $this;
    }

    public function __toString()
    {
        return $this->dateTime->format('Y-m-d H:i:s');
    }
}
