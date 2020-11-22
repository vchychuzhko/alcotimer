<?php
declare(strict_types=1);

namespace Awesome\Console\Exception;

class NoSuchCommandException extends \RuntimeException
{
    /**
     * @var string $commandName
     */
    private $commandName;

    /**
     * NoSuchCommandException constructor.
     * @param string $commandName
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $commandName, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Command "%s" was not recognized', $commandName), $code, $previous);
        $this->commandName = $commandName;
    }

    /**
     * Get notfound command name.
     * @return string
     */
    public function getCommandName(): string
    {
        return $this->commandName;
    }
}
