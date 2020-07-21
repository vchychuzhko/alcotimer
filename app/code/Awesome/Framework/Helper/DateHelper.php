<?php

namespace Awesome\Framework\Helper;

class DateHelper
{
    private const UTC_TIMEZONE = 'UTC';
    private const DEFAULT_TIMEZONE = 'Europe/Kiev';
    private const DEFAULT_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Get datetime according to the provided timezone as a string.
     * @param string $format
     * @param string $timezone
     * @return string
     */
    public static function getCurrentTime($format = self::DEFAULT_TIME_FORMAT, $timezone = self::DEFAULT_TIMEZONE)
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
     * Get datetime according to UTC timezone as a string.
     * @return string
     */
    public static function getCurrentTimeUTC()
    {
        return self::getCurrentTime(self::UTC_TIMEZONE);
    }
}
