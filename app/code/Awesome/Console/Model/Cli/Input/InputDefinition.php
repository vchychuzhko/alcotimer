<?php
declare(strict_types=1);

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
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get command description.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
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
        string $name,
        ?string $shortcut = null,
        int $type = self::OPTION_OPTIONAL,
        string $description = '',
        $default = true
    ): self
    {
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
            'shortcut'    => $shortcut,
            'type'        => $type,
            'description' => $description,
            'default'     => $default,
        ];

        return $this;
    }

    /**
     * Get command options.
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get command options shortcuts.
     * @return array
     */
    public function getShortcuts(): array
    {
        return $this->shortcuts;
    }

    /**
     * Parse, check and add command argument.
     * @param string $name
     * @param int $type
     * @param string $description
     * @return $this
     * @throws \LogicException
     */
    public function addArgument(string $name, int $type = self::ARGUMENT_REQUIRED, string $description = ''): self
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
            'type'        => $type,
            'position'    => ++$this->numberOfArguments,
            'description' => $description,
        ];

        return $this;
    }

    /**
     * Get command arguments.
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Reset collected command data.
     * @return $this
     */
    public function reset(): self
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
