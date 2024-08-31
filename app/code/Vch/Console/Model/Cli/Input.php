<?php
declare(strict_types=1);

namespace Vch\Console\Model\Cli;

class Input
{
    /**
     * @var string $commandName
     */
    private $commandName;

    /**
     * @var array $options
     */
    private $options;

    /**
     * @var array $arguments
     */
    private $arguments;

    /**
     * Input constructor.
     * @param string|null $commandName
     * @param array $options
     * @param array $arguments
     */
    public function __construct(?string $commandName = null, array $options = [], array $arguments = [])
    {
        $this->commandName = $commandName;
        $this->options = $options;
        $this->arguments = $arguments;
    }

    /**
     * Get input command name.
     * @return string|null
     */
    public function getCommandName(): ?string
    {
        return $this->commandName;
    }

    /**
     * Get input option by name.
     * @param string $optionName
     * @return mixed
     */
    public function getOption(string $optionName)
    {
        return $this->options[$optionName] ?? null;
    }

    /**
     * Get input argument by name.
     * @param string $argumentName
     * @return mixed
     */
    public function getArgument(string $argumentName)
    {
        return $this->arguments[$argumentName] ?? null;
    }
}
