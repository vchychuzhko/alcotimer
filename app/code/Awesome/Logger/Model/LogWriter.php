<?php

namespace Awesome\Logger\Model;

class LogWriter
{
    private const LOG_DIRECTORY = '/var/log';
    private const EXCEPTION_LOG_FILE = 'exception.log';
    private const VISITOR_LOG_FILE = 'visitor.log';
    private const CURRENT_TIMEZONE = 'Europe/Kiev';
    private const TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Write an error to a log file.
     * @param string $errorMessage
     * @return $this
     */
    public function error($errorMessage)
    {
        $this->write(
            self::EXCEPTION_LOG_FILE,
            $errorMessage
        );

        return $this;
    }

    /**
     * Log visited pages.
     * @return $this
     */
    public function logVisitor()
    {
        $this->write(
            self::VISITOR_LOG_FILE,
            $_SERVER['REMOTE_ADDR'] . ' - http://alcotimer.xyz' . $_SERVER['REQUEST_URI']
        );

        return $this;
    }

    /**
     * Write message to a log file.
     * @param string $logFile
     * @param string $message
     * @return $this
     */
    private function write($logFile, $message)
    {
        if (!file_exists(BP . self::LOG_DIRECTORY)) {
            mkdir(BP . self::LOG_DIRECTORY);
        }

        file_put_contents(
            BP . self::LOG_DIRECTORY . '/' . $logFile,
            $this->getCurrentTime() . ': ' . $message . "\n",
            FILE_APPEND
        );

        return $this;
    }

    /**
     * Prepare datetime according to the current timezone as a string.
     * @return string
     */
    private function getCurrentTime()
    {
        try {
            $date = new \DateTime('now', new \DateTimeZone(self::CURRENT_TIMEZONE));
            $time = $date->format(self::TIME_FORMAT);
        } catch (\Exception $e) {
            date_default_timezone_set(self::CURRENT_TIMEZONE);
            $time = date(self::TIME_FORMAT);
        }

        return $time;
    }
}
