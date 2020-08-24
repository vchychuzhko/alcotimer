<?php

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\DateTime;
use Awesome\Framework\Model\Http\Request;

class Logger
{
    private const LOG_DIRECTORY = '/var/log';
    private const EXCEPTION_LOG_FILE = 'exception.log';
    private const VISITOR_LOG_FILE = 'visitor.log';

    /**
     * @var DateTime $date
     */
    private $datetime;

    /**
     * Logger constructor.
     * @param DateTime $datetime
     */
    public function __construct(DateTime $datetime)
    {
        $this->datetime = $datetime;
    }

    /**
     * Write an error to a log file.
     * @param \Exception $e
     * @return $this
     */
    public function error($e)
    {
        $this->write(
            self::EXCEPTION_LOG_FILE,
            get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString()
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
            $request->getUserIp() . ' - ' . $request->getUrl()
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
            '[' . $this->datetime->getCurrentTime() . '] ' . $message . "\n",
            FILE_APPEND
        );

        return $this;
    }
}
