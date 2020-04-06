<?php

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Date;
use Awesome\Framework\Model\Http\Request;

class Logger
{
    private const LOG_DIRECTORY = '/var/log';
    private const EXCEPTION_LOG_FILE = 'exception.log';
    private const VISITOR_LOG_FILE = 'visitor.log';

    /**
     * @var Date $date
     */
    private $date;

    /**
     * LogWriter constructor.
     */
    public function __construct()
    {
        $this->date = new Date();
    }

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
     * @param Request $request
     * @return $this
     */
    public function logVisitor($request)
    {
        $this->write(
            self::VISITOR_LOG_FILE,
            $request->getUserIPAddress() . ' - ' . $request->getUrl()
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
            $this->date->getCurrentTime() . ': ' . $message . "\n",
            FILE_APPEND
        );

        return $this;
    }
}
