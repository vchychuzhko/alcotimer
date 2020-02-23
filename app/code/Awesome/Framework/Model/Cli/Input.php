<?php

namespace Awesome\Framework\Model\Cli;

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
    public function __construct($command, $options, $arguments)
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
     * @param int $tries
     * @return bool
     */
    public function confirm($prompt, $confirmOptions = ['y', 'yes'], $tries = 2)
    {
        $confirm = true;

        if ($this->interactive) {
            $confirm = false;
            $try = 1;
            $answer = '';

            while ($try <= $tries && !in_array($answer, $confirmOptions)) {
                $answer = $this->read($prompt);
                $try++;
            }
        }

        return $confirm;
    }

    /**
     * Prompt user to select one of provided options.
     * @param string $prompt
     * @param array $options
     * @return bool
     */
    public function choice($prompt, $options)
    {
        $choice = '';

        if ($this->interactive) {
            $prompt .= "\n" . implode("\n", $options);
            $choice = $this->read($prompt);
            //@TODO: add an ability to use kay-value arrays
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
     * Get requested command.
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Get input option.
     * Return all collected options if no name is specified.
     * @param string $optionName
     * @return string
     */
    public function getOption($optionName = '')
    {
        if ($optionName) {
            $option = $this->options[$optionName] ?? '';
        } else {
            $option = $this->options;
        }

        return $option;
    }

    /**
     * Get input argument by provided position.
     * Return all collected arguments if no position is specified.
     * @param int $argumentPosition
     * @return string|array
     */
    public function getArgument($argumentPosition = 0)
    {
        if ($argumentPosition) {
            $argument = $this->arguments[$argumentPosition - 1] ?? '';
            //@TODO: update this for Handler to map arguments by name instead of position, see parseInput()
        } else {
            $argument = $this->arguments;
        }

        return $argument;
    }
}
