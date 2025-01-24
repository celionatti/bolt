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
use DateInterval;
use Exception;

class TimeDateUtils
{
    protected DateTime $dateTime;

    /**
     * Constructor for TimeDateUtils
     *
     * @param string $dateTime Default is 'now'
     * @param string $timeZone Default is 'UTC'
     */
    public function __construct(string $dateTime = 'now', string $timeZone = 'UTC')
    {
        try {
            $this->dateTime = new DateTime($dateTime, new DateTimeZone($timeZone));
        } catch (Exception $e) {
            // Fallback to current time in UTC if parsing fails
            $this->dateTime = new DateTime('now', new DateTimeZone('UTC'));
        }
    }

    /**
     * Static factory method for creating instances
     *
     * @param string $dateTime
     * @param string $timeZone
     * @return static
     */
    public static function create(string $dateTime = 'now', string $timeZone = 'UTC'): self
    {
        return new static($dateTime, $timeZone);
    }

    /**
     * Converts time difference to human-readable "ago" format
     *
     * @return string
     */
    public function toAgoFormat(): string
    {
        $now = new DateTime('now', $this->dateTime->getTimezone());
        $diff = $now->diff($this->dateTime);

        $periods = [
            'y' => ['year', 'years'],
            'm' => ['month', 'months'],
            'd' => ['day', 'days'],
            'h' => ['hour', 'hours'],
            'i' => ['minute', 'minutes'],
            's' => ['second', 'seconds']
        ];

        foreach ($periods as $key => $labels) {
            $value = $diff->$key;
            if ($value > 0) {
                $label = $value > 1 ? $labels[1] : $labels[0];
                return "{$value} {$label} ago";
            }
        }

        return 'just now';
    }

    /**
     * Formats date for blog-style display
     *
     * @return string
     */
    public function toBlogFormat(): string
    {
        return $this->dateTime->format('d-F-Y');
    }

    /**
     * Formats date with custom format
     *
     * @param string $format
     * @return string
     */
    public function toCustomFormat(string $format): string
    {
        return $this->dateTime->format($format);
    }

    /**
     * Returns ISO 8601 formatted date
     *
     * @return string
     */
    public function toISO8601(): string
    {
        return $this->dateTime->format(DateTime::ATOM);
    }

    /**
     * Returns RFC 2822 formatted date
     *
     * @return string
     */
    public function toRFC2822(): string
    {
        return $this->dateTime->format(DateTime::RFC2822);
    }

    /**
     * Returns Unix timestamp
     *
     * @return int
     */
    public function toUnixTimestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }

    /**
     * Adds days to the current date
     *
     * @param int $days
     * @return $this
     */
    public function addDays(int $days): self
    {
        $this->dateTime->add(new DateInterval("P{$days}D"));
        return $this;
    }

    /**
     * Subtracts days from the current date
     *
     * @param int $days
     * @return $this
     */
    public function subDays(int $days): self
    {
        $this->dateTime->sub(new DateInterval("P{$days}D"));
        return $this;
    }

    /**
     * Adds hours to the current date
     *
     * @param int $hours
     * @return $this
     */
    public function addHours(int $hours): self
    {
        $this->dateTime->add(new DateInterval("PT{$hours}H"));
        return $this;
    }

    /**
     * Subtracts hours from the current date
     *
     * @param int $hours
     * @return $this
     */
    public function subHours(int $hours): self
    {
        $this->dateTime->sub(new DateInterval("PT{$hours}H"));
        return $this;
    }

    /**
     * Adds minutes to the current date
     *
     * @param int $minutes
     * @return $this
     */
    public function addMinutes(int $minutes): self
    {
        $this->dateTime->add(new DateInterval("PT{$minutes}M"));
        return $this;
    }

    /**
     * Subtracts minutes from the current date
     *
     * @param int $minutes
     * @return $this
     */
    public function subMinutes(int $minutes): self
    {
        $this->dateTime->sub(new DateInterval("PT{$minutes}M"));
        return $this;
    }

    /**
     * Sets the timezone for the current date
     *
     * @param string $timeZone
     * @return $this
     */
    public function setTimezone(string $timeZone): self
    {
        $this->dateTime->setTimezone(new DateTimeZone($timeZone));
        return $this;
    }

    /**
     * Compares the current date with another date
     *
     * @param DateTime|string $compareDate
     * @return int Returns -1 if current date is earlier, 0 if equal, 1 if later
     */
    public function compareTo($compareDate): int
    {
        $comparisonDate = $compareDate instanceof DateTime
            ? $compareDate
            : new DateTime($compareDate);

        return $this->dateTime <=> $comparisonDate;
    }

    /**
     * Checks if the current date is in the past
     *
     * @return bool
     */
    public function isPast(): bool
    {
        return $this->dateTime < new DateTime('now');
    }

    /**
     * Checks if the current date is in the future
     *
     * @return bool
     */
    public function isFuture(): bool
    {
        return $this->dateTime > new DateTime('now');
    }

    /**
     * String representation of the date
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->dateTime->format('Y-m-d H:i:s');
    }

    /**
     * Returns the underlying DateTime object
     *
     * @return DateTime
     */
    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }
}
