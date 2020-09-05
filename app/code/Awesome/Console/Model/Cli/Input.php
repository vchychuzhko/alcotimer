<?php

namespace Awesome\Console\Model\Cli;

class Input
{
    /**
     * @var bool $interactive
     */
    private $interactive = true;

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
     * Read user input.
     * @param string $prompt
     * @return string
     */
    public function read($prompt = '')
    {
        $line = '';

        if ($this->interactive) {
            $line = readline($prompt);
            readline_add_history($line);
        }

        return $line;
    }

    /**
     * Prompt user confirmation.
     * @param string $prompt
     * @param array $confirmOptions
     * @return bool
     */
    public function confirm($prompt, $confirmOptions = ['y', 'yes'])
    {
        $confirm = true;

        if ($this->interactive) {
            $answer = $this->read($prompt);
            $confirm = in_array($answer, $confirmOptions, true);
        }

        return $confirm;
    }

    /**
     * Prompt user to select one of the provided options.
     * @param string $prompt
     * @param array $options
     * @return string
     */
    public function choice($prompt, $options)
    {
        $choice = null;

        if ($this->interactive && $options) {
            foreach ($options as $optionKey => $option) {
                $prompt .= "\n" . $optionKey . ': ' . $option;
            }
            $prompt .= "\n" . 'Your choice: ';
            $answer = $this->read($prompt);

            if (array_key_exists($answer, $options)) {
                $choice = $answer;
            }
        }

        return $choice;
    }

    /**
     * Disable interaction.
     * @return $this
     */
    public function disableInteraction()
    {
        $this->interactive = false;

        return $this;
    }

    /**
     * Enable interaction.
     * @return $this
     */
    public function enableInteraction()
    {
        $this->interactive = true;

        return $this;
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
