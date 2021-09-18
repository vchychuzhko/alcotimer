<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

class DateTime implements \Awesome\Framework\Model\SingletonInterface
{
    private const UTC_TIMEZONE = 'UTC';

    private const DEFAULT_TIMEZONE = 'Europe/Kiev';
    private const DEFAULT_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Get datetime according to the provided timezone as a formatted string.
     * @param string $format
     * @param string $timezone
     * @return string
     */
    public function getTime(string $format = self::DEFAULT_TIME_FORMAT, string $timezone = self::DEFAULT_TIMEZONE): string
    {
        try {
            $date = new \DateTime('now', new \DateTimeZone($timezone));
            $time = $date->format($format);
        } catch (\Exception $e) {
            $currentTimeZone = date_default_timezone_get();
            date_default_timezone_set($timezone);
            $time = date($format);
            date_default_timezone_set($currentTimeZone);
        }

        return $time;
    }

    /**
     * Get datetime according to UTC timezone as a formatted string.
     * @param string $format
     * @return string
     */
    public function getTimeUTC(string $format = self::DEFAULT_TIME_FORMAT): string
    {
        return $this->getTime($format, self::UTC_TIMEZONE);
    }

    /**
     * Get Unix timestamp.
     * @return int
     */
    public function getTimestamp(): int
    {
        try {
            $date = new \DateTime('now');
            $timeStamp = $date->getTimestamp();
        } catch (\Exception $e) {
            $timeStamp = time();
        }

        return $timeStamp;
    }
}