<?php

namespace Awesome\Console\Model\Handler;

use Awesome\Console\Model\Cli;
use Awesome\Console\Model\XmlParser\Command as CommandXmlParser;

class Command
{
    /**
     * @var CommandXmlParser $commandXmlParser
     */
    private $commandXmlParser;

    /**
     * @var array $parsedHandles
     */
    private $parsedHandles = [];

    /**
     * CommandHandler constructor.
     */
    function __construct()
    {
        $this->commandXmlParser = new CommandXmlParser();
    }

    /**
     * Get command data according to requested command name.
     * Return data for default command if no name is provided.
     * @param string $commandName
     * @return array
     */
    public function process($commandName)
    {
        if ($commandName === '') {
            $commandData = $this->commandXmlParser->get(Cli::DEFAULT_COMMAND);
        } else {
            $commandData = $this->commandXmlParser->get($commandName);
        }

        return $commandData;
    }

    /**
     * Check if requested command exists.
     * @param string $commandName
     * @return bool
     */
    public function exist($commandName)
    {
        $exist = false;

        if ($commandName) {
            $commandName = $this->parse($commandName);
            $exist = in_array($commandName, $this->commandXmlParser->getCommands());
        }

        return $exist;
    }

    /**
     * Parse requested command name in to a full name.
     * @param string $handle
     * @return string
     */
    public function parse($handle)
    {
        if (!isset($this->parsedHandles[$handle])) {
            $this->parsedHandles[$handle] = $handle;
            $possibleMatches = $this->getAlternatives($handle);

            if (count($possibleMatches) === 1) {
                $this->parsedHandles[$handle] = $possibleMatches[0];
            }
        }

        return $this->parsedHandles[$handle];
    }

    /**
     * Get command class name.
     * @param string $command
     * @return string
     */
    public function getCommandClass($command)
    {
        $command = $this->parse($command);
        $handles = $this->commandXmlParser->getCommandsClasses();

        return $handles[$command] ?? null;
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
        $consoleCommands = $this->commandXmlParser->getCommands();

        foreach ($consoleCommands as $consoleCommand) {
            [$commandNamespace, $commandCommand] = explode(':', $consoleCommand);

            if (strpos($commandNamespace, $namespace) === 0
                && (($command && strpos($commandCommand, $command) === 0) || !$strict)
            ) {
                $possibleCandidates[] = $consoleCommand;
            }
        }

        return $possibleCandidates;
    }
}
