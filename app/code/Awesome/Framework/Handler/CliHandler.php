<?php

namespace Awesome\Framework\Handler;

use Awesome\Framework\Model\Cli\Input;
use Awesome\Framework\Model\Cli\Input\InputDefinition;
use Awesome\Framework\XmlParser\CliXmlParser;

class CliHandler extends \Awesome\Framework\Model\Handler\AbstractHandler
{
    /**
     * @var CliXmlParser $cliXmlParser
     */
    private $cliXmlParser;

    /**
     * @var array $parsedHandles
     */
    private $parsedHandles = [];

    /**
     * CliHandler constructor.
     */
    function __construct()
    {
        $this->cliXmlParser = new CliXmlParser();
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function process($handle)
    {
        $className = '';
        $handle = $this->parse($handle);

        if ($this->exist($handle)) {
            $handles = $this->cliXmlParser->getHandlesClasses();
            $className = $handles[$handle];
        }

        return $className;
    }

    /**
     * Check if requested page handle exists.
     * @param string $handle
     * @return bool
     */
    public function exist($handle)
    {
        $handle = $this->parse($handle);

        return in_array($handle, $this->cliXmlParser->getHandles());
    }

    /**
     * @inheritDoc
     */
    public function parse($handle)
    {
        if (!isset($this->parsedHandles[$handle])) {
            $this->parsedHandles[$handle] = $handle;
            $possibleMatches = $this->getPossibleCandidates($handle);

            if (count($possibleMatches) === 1) {
                $this->parsedHandles[$handle] = $possibleMatches[0];
            }
        }

        return $this->parsedHandles[$handle];
    }

    /**
     * Get possible variants of the requested handle.
     * If strict is true, do not return candidates in case command is not full.
     * @param string $handle
     * @param bool $strict
     * @return array
     */
    public function getPossibleCandidates($handle, $strict = true)
    {
        $possibleCandidates = [];
        @list($namespace, $command) = explode(':', $handle);
        $consoleCommands = $this->cliXmlParser->getHandles();

        if ($command) {
            foreach ($consoleCommands as $consoleCommand) {
                [$commandNamespace, $commandCommand] = explode(':', $consoleCommand);

                if (strpos($commandNamespace, $namespace) === 0 && strpos($commandCommand, $command) === 0) {
                    $possibleCandidates[] = $commandNamespace . ':' . $commandCommand;
                }
            }
        } elseif (!$strict) {
            foreach ($consoleCommands as $consoleCommand) {
                if (strpos($consoleCommand, $namespace) === 0) {
                    $possibleCandidates[] = $consoleCommand;
                }
            }
        }

        return $possibleCandidates;
    }

    /**
     * Parse console input command, options and arguments.
     * @return Input
     * @throws \LogicException
     */
    public function parseInput()
    {
        $args = $_SERVER['argv'] ?? [];
        $command = '';
        $options = [];
        $arguments = [];

        if (isset($args[1]) && strpos($args[1], '-') !== 0) {
            $command = $this->parse($args[1]);
        }
        $exist = $this->exist($command);
        $collectedArguments = [];
        $argumentCount = 0;

        $commandData = $this->cliXmlParser->get($command);
        $commandOptions = $commandData['options'] ?? [];
        $commandArguments = $commandData['arguments'] ?? [];
        $commandShortcuts = $commandData['shortcuts'] ?? [];

        foreach (array_slice($args, 2) as $arg) {
            if (strpos($arg, '--') === 0) {
                @list($option, $value) = explode('=', str_replace_first('--', '', $arg));
                $default = true;

                if ($exist || $commandOptions) {
                    if (!isset($commandOptions[$option])) {
                        throw new \LogicException(sprintf('Unknown option "%s"', $option));
                    }
                    $default = $commandOptions[$option]['default'];
                }
                $options[$option] = $value ?: $default;
            } elseif (strpos($arg, '-') === 0) {
                $shortcuts = substr($arg, 1);

                if ($exist || $commandShortcuts) {
                    foreach (str_split($shortcuts) as $shortcut) {
                        if (!isset($commandShortcuts[$shortcut])) {
                            throw new \LogicException(sprintf('Unknown shortcut "%s" provided', $shortcut));
                        }
                        $option = $commandShortcuts[$shortcut];
                        $options[$option] = $commandOptions[$option]['default'];
                    }
                }
            } else {
                $collectedArguments[++$argumentCount] = $arg;
            }
        }

        if ($exist || $commandArguments) {
            foreach ($commandArguments as $argumentName => $argumentData) {
                $position = $argumentData['position'];

                if ($argumentData['type'] === InputDefinition::ARGUMENT_REQUIRED
                    && !isset($collectedArguments[$position])
                ) {
                    throw new \LogicException(sprintf('Required argument "%s" was not provided', $argumentName));
                } elseif ($argumentData['type'] === InputDefinition::ARGUMENT_ARRAY) {
                    $arguments[$argumentName] = $collectedArguments;
                    break;
                } elseif (isset($collectedArguments[$position])) {
                    $arguments[$argumentName] = $collectedArguments[$position];
                }
            }
        } else {
            $arguments = $collectedArguments;
        }

        return new Input($command, $options, $arguments);
    }
}
