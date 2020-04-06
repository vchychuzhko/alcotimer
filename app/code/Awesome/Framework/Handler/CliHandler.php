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
     * @var string $command
     */
    private $command;

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
     * Get responsible class by requested handle.
     * @inheritDoc
     */
    public function process($handle)
    {
        $handle = $this->parse($handle);
        $handles = $this->cliXmlParser->getHandlesClasses();

        return $handles[$handle] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function exist($handle)
    {
        $exist = false;

        if ($handle) {
            $handle = $this->parse($handle);
            $exist = in_array($handle, $this->cliXmlParser->getHandles());
        }

        return $exist;
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

        foreach ($consoleCommands as $consoleCommand) {
            list($commandNamespace, $commandCommand) = explode(':', $consoleCommand);

            if (strpos($commandNamespace, $namespace) === 0
                && (($command && strpos($commandCommand, $command) === 0) || !$strict)
            ) {
                $possibleCandidates[] = $consoleCommand;
            }
        }

        return $possibleCandidates;
    }

    /**
     * Get input command.
     * @return string
     */
    public function getCommand()
    {
        if (!$this->command) {
            $argv = $_SERVER['argv'];

            if (isset($argv[1]) && strpos($argv[1], '-') !== 0) {
                $this->command = $this->parse($argv[1]);
            }
        }

        return $this->command;
    }

    /**
     * Parse console input command, options and arguments.
     * @return Input
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function parseInput()
    {
        $argv = $_SERVER['argv'];
        $options = [];
        $arguments = [];
        $collectedArguments = [];
        $argumentPosition = 1;

        if ($command = $this->getCommand()) {
            unset($argv[1]);
            $commandData = $this->cliXmlParser->get($command);
        } else {
            $commandData = $this->cliXmlParser->getDefault();
        }
        $commandOptions = $commandData['options'];
        $commandShortcuts = $commandData['shortcuts'];
        $commandArguments = $commandData['arguments'];

        foreach (array_slice($argv, 1) as $arg) {
            if (strpos($arg, '--') === 0) {
                @list($option, $value) = explode('=', str_replace_first('--', '', $arg));

                if (!isset($commandOptions[$option])) {
                    throw new \RuntimeException(sprintf('Unknown option "%s"', $option));
                }
                $value = $value ?: $commandOptions[$option]['default'];

                if ($commandOptions[$option]['type'] === InputDefinition::OPTION_ARRAY) {
                    if (!isset($options[$option])) {
                        $options[$option] = [];
                    }
                    $options[$option][] = $value;
                } else {
                    $options[$option] = $value;
                }
            } elseif (strpos($arg, '-') === 0) {
                $shortcuts = substr($arg, 1);

                foreach (str_split($shortcuts) as $shortcut) {
                    if (!isset($commandShortcuts[$shortcut])) {
                        throw new \RuntimeException(sprintf('Unknown shortcut "%s"', $shortcut));
                    }
                    $option = $commandShortcuts[$shortcut];
                    $options[$option] = $commandOptions[$option]['default'];
                }
            } else {
                $collectedArguments[$argumentPosition++] = $arg;
            }
        }

        if ($commandOptions) {
            foreach ($commandOptions as $optionName => $optionData) {
                if ($optionData['type'] === InputDefinition::OPTION_REQUIRED
                    && !isset($options[$optionName])
                ) {
                    throw new \RuntimeException(sprintf('Required option "%s" was not provided', $optionName));
                }
            }
        }

        if ($commandArguments) {
            foreach ($commandArguments as $argumentName => $argumentData) {
                $position = $argumentData['position'];

                if ($argumentData['type'] === InputDefinition::ARGUMENT_REQUIRED
                    && !isset($collectedArguments[$position])
                ) {
                    throw new \RuntimeException(sprintf('Required argument "%s" was not provided', $argumentName));
                } elseif ($argumentData['type'] === InputDefinition::ARGUMENT_ARRAY) {
                    $arguments[$argumentName] = array_slice($collectedArguments, $position - 1);
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
