<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

class Logger extends \Awesome\Framework\Model\AbstractLogger
{
    private const EXCEPTION_LOG_FILE = 'exception.log';
    private const SYSTEM_LOG_FILE = 'system.log';

    /**
     * Write an error to a log file.
     * @param string $errorMessage
     * @return $this
     */
    public function error(string $errorMessage): self
    {
        $this->write(self::EXCEPTION_LOG_FILE, $errorMessage);

        return $this;
    }

    /**
     * Write a system info message to a log file.
     * @param string $message
     * @return $this
     */
    public function info(string $message): self
    {
        $this->write(self::SYSTEM_LOG_FILE, $message);

        return $this;
    }
}
