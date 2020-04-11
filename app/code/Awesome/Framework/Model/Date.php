<?php

namespace Awesome\Framework\Model;

class Date
{
    private const TIMEZONE = 'Europe/Kiev';
    private const TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Prepare datetime according to the current timezone as a string.
     * @return string
     */
    public function getCurrentTime()
    {
        try {
            $date = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
            $time = $date->format(self::TIME_FORMAT);
        } catch (\Exception $e) {
            $currentTimeZone = date_default_timezone_get();
            date_default_timezone_set(self::TIMEZONE);
            $time = date(self::TIME_FORMAT);
            date_default_timezone_set($currentTimeZone);
        }

        return $time;
    }
}
