<?php
declare(strict_types=1);

namespace Awesome\Console\Model\Cli;

class Input
{
    /**
     * @var string $command
     */
    private $command;

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
     * @param string|null $command
     * @param array $options
     * @param array $arguments
     */
    public function __construct(?string $command = null, array $options = [], array $arguments = [])
    {
        $this->command = $command;
        $this->options = $options;
        $this->arguments = $arguments;
    }

    /**
     * Get input command.
     * @return string|null
     */
    public function getCommand(): ?string
    {
        return $this->command;
    }

    /**
     * Get input option by name.
     * @param string $optionName
     * @param bool $typeCast
     * @return mixed
     */
    public function getOption(string $optionName, bool $typeCast = false)
    {
        $value = $this->options[$optionName] ?? null;

        return $typeCast ? $this->castInputValue($value) : $value;
    }

    /**
     * Get all input options.
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get input argument by name.
     * @param string $argumentName
     * @param bool $typeCast
     * @return mixed
     */
    public function getArgument(string $argumentName, bool $typeCast = false)
    {
        $value = $this->arguments[$argumentName] ?? null;

        return $typeCast ? $this->castInputValue($value) : $value;
    }

    /**
     * Get all input arguments.
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Cast input value to a corresponding type.
     * @param mixed $value
     * @return mixed
     */
    private function castInputValue($value)
    {
        if (is_numeric($value)) {
            $value += 0;
        } elseif (in_array(strtolower($value), ['true', 'false'], true)) {
            $value = strtolower($value) === 'true';
        }

        return $value;
    }
}
