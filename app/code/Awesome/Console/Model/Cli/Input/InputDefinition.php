<?php

namespace Awesome\Console\Model\Cli\Input;

class InputDefinition
{
    public const OPTION_OPTIONAL = 1;
    public const OPTION_REQUIRED = 2;
    public const OPTION_ARRAY = 3;

    public const ARGUMENT_OPTIONAL = 1;
    public const ARGUMENT_REQUIRED = 2;
    public const ARGUMENT_ARRAY = 3;

    /**
     * @var string $description
     */
    private $description = '';

    /**
     * @var array $options
     */
    private $options = [];

    /**
     * @var array $arguments
     */
    private $arguments = [];

    /**
     * @var array $shortcuts
     */
    private $shortcuts = [];

    /**
     * @var bool $lastArgumentOptional
     */
    private $lastArgumentOptional = false;

    /**
     * @var bool $hasArrayArgument
     */
    private $hasArrayArgument = false;

    /**
     * @var int $numberOfArguments
     */
    private $numberOfArguments = 0;

    /**
     * Set command description, overrides if already set.
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Parse, check and add command option.
     * @param string $name
     * @param string|null $shortcut
     * @param int $type
     * @param string $description
     * @param mixed $default
     * @return $this
     * @throws \LogicException
     */
    public function addOption(
        $name,
        $shortcut = null,
        $type = self::OPTION_OPTIONAL,
        $description = '',
        $default = true
    ) {
        if ($shortcut) {
            if ($type === self::OPTION_ARRAY) {
                throw new \LogicException(sprintf('Array option "%s" cannot have shortcut', $name));
            }
            if (isset($this->shortcuts[$shortcut])) {
                throw new \LogicException(sprintf('An option with shortcut "%s" already exists', $shortcut));
            }
            $this->shortcuts[$shortcut] = $name;
        }

        $this->options[$name] = [
            'shortcut' => $shortcut,
            'type' => $type,
            'description' => $description,
            'default' => $default
        ];

        return $this;
    }

    /**
     * Parse, check and add command argument.
     * @param string $name
     * @param int $type
     * @param string $description
     * @return $this
     * @throws \LogicException
     */
    public function addArgument($name, $type = self::ARGUMENT_REQUIRED, $description = '')
    {
        if ($this->hasArrayArgument) {
            throw new \LogicException('Argument cannot be added after array argument');
        }
        if ($this->lastArgumentOptional) {
            throw new \LogicException('Argument cannot be added after optional argument');
        }
        if ($type === self::ARGUMENT_OPTIONAL) {
            $this->lastArgumentOptional = true;
        }
        if ($type === self::ARGUMENT_ARRAY) {
            $this->hasArrayArgument = true;
        }

        $this->arguments[$name] = [
            'type' => $type,
            'position' => ++$this->numberOfArguments,
            'description' => $description
        ];

        return $this;
    }

    /**
     * Return all collected command data.
     * @return array
     */
    public function getDefinition()
    {
        return [
            'description' => $this->description,
            'options' => $this->options,
            'arguments' => $this->arguments,
            'shortcuts' => $this->shortcuts
        ];
    }

    /**
     * Reset collected command data.
     * @return $this
     */
    public function reset()
    {
        $this->description = '';
        $this->options = [];
        $this->arguments = [];
        $this->shortcuts = [];
        $this->lastArgumentOptional = false;
        $this->hasArrayArgument = false;
        $this->numberOfArguments = 0;

        return $this;
    }
}
