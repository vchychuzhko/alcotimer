<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Phrase;

class Logger extends \Awesome\Framework\Model\AbstractLogger
{
    private const EXCEPTION_LOG_FILE = 'exception.log';
    private const SYSTEM_LOG_FILE = 'system.log';

    /**
     * Write an error to log file.
     * @param Phrase|string $errorMessage
     * @return $this
     */
    public function error($errorMessage): self
    {
        return $this->write(self::EXCEPTION_LOG_FILE, (string) $errorMessage);
    }

    /**
     * Write a system info message to log file.
     * @param Phrase|string $message
     * @return $this
     */
    public function info($message): self
    {
        return $this->write(self::SYSTEM_LOG_FILE, (string) $message);
    }
}
