<?php

namespace Awesome\Console\Model\Handler;

use Awesome\Console\Model\Cli\AbstractCommand;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\XmlParser\CommandXmlParser;

class CommandHandler
{
    /**
     * @var CommandXmlParser $commandXmlParser
     */
    private $commandXmlParser;

    /**
     * @var array $parsedCommands
     */
    private $parsedCommands = [];

    /**
     * CommandHandler constructor.
     */
    function __construct()
    {
        $this->commandXmlParser = new CommandXmlParser();
    }

    /**
     * Get command data for requested command name.
     * @param string $commandName
     * @return array
     */
    public function getCommandData($commandName)
    {
        $commandData = null;

        if ($commandClass = $this->getCommandClass($commandName)) {
            $definition = new InputDefinition();
            /** @var AbstractCommand $commandClass */
            $definition = $commandClass::configure($definition);

            $commandData = array_replace_recursive($definition->getDefinition(), ['class' => $commandClass]);
        }

        return $commandData;
    }

    /**
     * Get all available commands.
     * @return array
     * @throws \LogicException
     */
    public function getCommands()
    {
        return array_keys($this->commandXmlParser->getCommandsClasses());
    }

    /**
     * Check if requested command exists.
     * @param string $commandName
     * @return bool
     */
    public function commandExist($commandName)
    {
        return in_array($commandName, $this->getCommands());
    }

    /**
     * Parse requested command name into a full name.
     * @param string $commandName
     * @return string
     */
    public function parseCommand($commandName)
    {
        if (!isset($this->parsedHandles[$commandName])) {
            $this->parsedCommands[$commandName] = $commandName;
            $possibleMatches = $this->getAlternatives($commandName);

            if (count($possibleMatches) === 1) {
                $this->parsedCommands[$commandName] = reset($possibleMatches);
            }
        }

        return $this->parsedCommands[$commandName];
    }

    /**
     * Get command class according to the requested command name.
     * @param string $commandName
     * @return string
     * @throws \LogicException
     */
    public function getCommandClass($commandName)
    {
        $commandClasses = $this->commandXmlParser->getCommandsClasses();

        return $commandClasses[$commandName] ?? null;
    }

    /**
     * Get possible alternatives of the requested command name.
     * If strict is true, both parts of command name will be required.
     * @param string $handle
     * @param bool $strict
     * @return array
     */
    public function getAlternatives($handle, $strict = true)
    {
        $possibleCandidates = [];
        @list($namespace, $command) = explode(':', $handle);
        $consoleCommands = $this->getCommands();

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
}
