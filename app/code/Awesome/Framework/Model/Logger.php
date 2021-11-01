<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

class Logger extends \Awesome\Framework\Model\AbstractLogger
{
    private const EXCEPTION_LOG_FILE = 'exception.log';
    private const SYSTEM_LOG_FILE = 'system.log';

    /**
     * Write an error to log file.
     * @param string $errorMessage
     * @return $this
     */
    public function error(string $errorMessage): self
    {
        return $this->write(self::EXCEPTION_LOG_FILE, (string) $errorMessage);
    }

    /**
     * Write a system info message to log file.
     * @param string $message
     * @return $this
     */
    public function info(string $message): self
    {
        return $this->write(self::SYSTEM_LOG_FILE, (string) $message);
    }
}
