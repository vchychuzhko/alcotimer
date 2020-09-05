<?php

namespace Awesome\Console\Exception;

class NoSuchCommandException extends \RuntimeException
{
    /**
     * @var string $command
     */
    private $command;

    /**
     * NoSuchCommandException constructor.
     * @param string $command
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($command, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Command "%s" was not recognized', $command), $code, $previous);
        $this->command = $command;
    }

    /**
     * Get notfound command.
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }
}
