<?php

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
     * @param string $command
     * @param array $options
     * @param array $arguments
     */
    public function __construct($command, $options = [], $arguments = [])
    {
        $this->command = $command;
        $this->options = $options;
        $this->arguments = $arguments;
    }

    /**
     * Get input command.
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Get input option by name.
     * @param string $optionName
     * @param bool $typeCast
     * @return mixed
     */
    public function getOption($optionName, $typeCast = false)
    {
        $value = $this->options[$optionName] ?? null;

        return $typeCast ? $this->castInputValue($value) : $value;
    }

    /**
     * Get all input options.
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get input argument by name.
     * @param string $argumentName
     * @param bool $typeCast
     * @return mixed
     */
    public function getArgument($argumentName, $typeCast = false)
    {
        $value = $this->arguments[$argumentName] ?? null;

        return $typeCast ? $this->castInputValue($value) : $value;
    }

    /**
     * Get all input arguments.
     * @return array
     */
    public function getArguments()
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
