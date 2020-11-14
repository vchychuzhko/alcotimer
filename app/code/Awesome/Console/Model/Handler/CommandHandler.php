<?php
declare(strict_types=1);

namespace Awesome\Console\Model\Handler;

use Awesome\Console\Model\Cli\AbstractCommand;
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
     * @param CommandXmlParser $commandXmlParser
     */
    public function __construct(CommandXmlParser $commandXmlParser)
    {
        $this->commandXmlParser = $commandXmlParser;
    }

    /**
     * Get command data for requested command name.
     * @param string $commandName
     * @return array|null
     */
    public function getCommandData(string $commandName): ?array
    {
        $commandData = null;

        if ($commandClass = $this->getCommandClass($commandName)) {
            /** @var AbstractCommand $commandClass */
            $definition = $commandClass::configure();

            $commandData = array_merge($definition->getDefinition(), ['class' => $commandClass]);
        }

        return $commandData;
    }

    /**
     * Get all available commands.
     * @return array
     */
    public function getCommands(): array
    {
        return array_keys($this->commandXmlParser->getCommandsClasses());
    }

    /**
     * Check if requested command exists.
     * @param string $commandName
     * @return bool
     */
    public function commandExist(string $commandName): bool
    {
        return in_array($commandName, $this->getCommands(), true);
    }

    /**
     * Parse requested command name into a full name.
     * @param string $commandName
     * @return string
     */
    public function parseCommand(string $commandName): string
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
     * @return string|null
     */
    public function getCommandClass(string $commandName): ?string
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
    public function getAlternatives(string $handle, bool $strict = true): array
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
