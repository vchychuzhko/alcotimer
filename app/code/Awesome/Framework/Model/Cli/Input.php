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

            while ($try <= $tries && !$confirm) {
                $answer = $this->read($prompt);
                $confirm = in_array($answer, $confirmOptions);
                $try++;
            }
        }

        return $confirm;
    }

    /**
     * Prompt user to select one of the provided options.
     * @param string $prompt
     * @param array $options
     * @param int $tries
     * @return string
     */
    public function choice($prompt, $options, $tries = 2)
    {
        $choice = '';

        if ($this->interactive && $options) {
            foreach ($options as $optionKey => $option) {
                $prompt .= "\n" . $optionKey . ': ' . $option;
            }
            $prompt .= "\n" . 'Your choice: ';
            $try = 1;
            $chose = false;

            while ($try <= $tries && !$chose) {
                $choice = $this->read($prompt);
                $chose = in_array($choice, array_keys($options));
                $try++;
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
     * Get input option.
     * Return all collected options if no name is specified.
     * @param string $optionName
     * @return string|array|null
     */
    public function getOption($optionName = '')
    {
        if ($optionName === '') {
            $option = $this->options;
        } else {
            $option = $this->options[$optionName] ?? null;
        }

        return $option;
    }

    /**
     * Get input argument.
     * Return all collected arguments if no name is specified.
     * @param string $argumentName
     * @return string|array|null
     */
    public function getArgument($argumentName = '')
    {
        if ($argumentName === '') {
            $argument = $this->arguments;
        } else {
            $argument = $this->arguments[$argumentName] ?? null;
        }

        return $argument;
    }
}
