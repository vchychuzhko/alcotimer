<?php
declare(strict_types=1);

namespace Awesome\Console\Model\Cli;

use Awesome\Framework\Helper\DataHelper;

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
     * @param bool $typeCast
     * @return mixed
     */
    public function getOption(string $optionName, bool $typeCast = false)
    {
        $value = $this->options[$optionName] ?? null;

        return $typeCast ? $this->castInputValue($value) : $value;
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
     * Cast input value to a corresponding type.
     * In case array option or argument is used, all values will be casted.
     * @param mixed $value
     * @return mixed
     */
    private function castInputValue($value)
    {
        if (is_array($value)) {
            foreach ($value as &$item) {
                $item = DataHelper::castValue($item);
            }
        } else {
            $value = DataHelper::castValue($value);
        }

        return $value;
    }
}
