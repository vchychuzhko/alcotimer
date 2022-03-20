<?php
declare(strict_types=1);

namespace Awesome\Console\Model\Cli;

use Awesome\Console\Console\Help;
use Awesome\Console\Model\Cli\CommandFactory;
use Awesome\Console\Model\CommandInterface;
use Awesome\Console\Model\XmlParser\CliXmlParser;

class CommandResolver
{
    /**
     * @var CommandFactory $commandFactory
     */
    private $commandFactory;

    /**
     * @var CliXmlParser $cliXmlParser
     */
    private $cliXmlParser;

    /**
     * @var array $commandClasses
     */
    private $commandClasses;

    /**
     * CommandResolver constructor.
     * @param CommandFactory $commandFactory
     * @param CliXmlParser $cliXmlParser
     */
    public function __construct(CommandFactory $commandFactory, CliXmlParser $cliXmlParser)
    {
        $this->commandFactory = $commandFactory;
        $this->cliXmlParser = $cliXmlParser;
    }

    /**
     * Get command object by requested command name.
     * @param string $commandName
     * @return CommandInterface|null
     * @throws \Exception
     */
    public function getCommand(string $commandName): ?CommandInterface
    {
        $command = null;

        if ($commandClass = $this->getCommandClass($commandName)) {
            $command = $this->commandFactory->create($commandClass);
        }

        return $command;
    }

    /**
     * Get Help command object.
     * @return CommandInterface
     * @throws \Exception
     */
    public function getHelpCommand(): CommandInterface
    {
        return $this->commandFactory->create(Help::class);
    }

    /**
     * Get all available commands.
     * @return array
     * @throws \Exception
     */
    public function getCommands(): array
    {
        return array_keys($this->getCommandsClasses());
    }

    /**
     * Check if requested command exists.
     * @param string $commandName
     * @return bool
     * @throws \Exception
     */
    public function commandExist(string $commandName): bool
    {
        return in_array($commandName, $this->getCommands(), true);
    }

    /**
     * Parse requested command name into a full name if possible.
     * @param string $commandName
     * @return string
     * @throws \Exception
     */
    public function parseCommand(string $commandName): string
    {
        $possibleMatches = $this->getAlternatives($commandName);

        if (count($possibleMatches) === 1) {
            $commandName = reset($possibleMatches);
        }

        return $commandName;
    }

    /**
     * Get command class according to the requested command name if exists.
     * @param string $commandName
     * @return string|null
     * @throws \Exception
     */
    public function getCommandClass(string $commandName): ?string
    {
        $commandClasses = $this->getCommandsClasses();

        return $commandClasses[$commandName] ?? null;
    }

    /**
     * Get all declared and enabled commands and their responsible classes.
     * @return array
     * @throws \Exception
     */
    private function getCommandsClasses(): array
    {
        if (!$this->commandClasses) {
            $this->commandClasses = $this->cliXmlParser->getCommandsClasses();
        }

        return $this->commandClasses;
    }

    /**
     * Get possible alternatives of the requested command name.
     * If strict is true, both parts of command name will be required.
     * @param string $commandName
     * @param bool $strict
     * @return array
     * @throws \Exception
     */
    public function getAlternatives(string $commandName, bool $strict = true): array
    {
        $possibleCandidates = [];
        @list($namespace, $command) = explode(':', $commandName);
        $consoleCommands = $this->getCommands();

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
