<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\DateTime;
use Awesome\Framework\Model\FileManager;

abstract class AbstractLogger
{
    private const LOG_DIRECTORY = '/var/log';

    /**
     * @var DateTime $date
     */
    private $datetime;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * AbstractLogger constructor.
     * @param DateTime $datetime
     * @param FileManager $fileManager
     */
    public function __construct(DateTime $datetime, FileManager $fileManager)
    {
        $this->datetime = $datetime;
        $this->fileManager = $fileManager;
    }

    /**
     * Write message to a log file.
     * @param string $logFile
     * @param string $message
     * @return $this
     */
    protected function write(string $logFile, string $message): self
    {
        $this->fileManager->writeToFile(
            BP . self::LOG_DIRECTORY . '/' . $logFile,
            '[' . $this->datetime->getCurrentTime() . '] ' . $message . "\n"
        );

        return $this;
    }
}
