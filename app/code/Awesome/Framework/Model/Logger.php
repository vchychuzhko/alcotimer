<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

class Logger extends \Awesome\Framework\Model\AbstractLogger
{
    private const EXCEPTION_LOG_FILE = 'exception.log';
    private const SYSTEM_LOG_FILE = 'system.log';

    public const INFO_DEFAULT_LEVEL = 0;
    public const INFO_WARNING_LEVEL = 1;
    public const INFO_CRITICAL_LEVEL = 2;

    private const SEVERITY_LEVELS = [
        self::INFO_DEFAULT_LEVEL  => 'INFO',
        self::INFO_WARNING_LEVEL  => 'WARNING',
        self::INFO_CRITICAL_LEVEL => 'CRITICAL',
    ];

    /**
     * Write an error to log file.
     * @param string $errorMessage
     * @return $this
     */
    public function error(string $errorMessage): self
    {
        return $this->write(self::EXCEPTION_LOG_FILE, $errorMessage . "\n");
    }

    /**
     * Write a system info message to log file.
     * Severity level can be provided.
     * @param string $message
     * @param int $level
     * @return $this
     */
    public function info(string $message, int $level = self::INFO_DEFAULT_LEVEL): self
    {
        return $this->write(self::SYSTEM_LOG_FILE, '[' . self::SEVERITY_LEVELS[$level] . ']: ' . $message);
    }
}
