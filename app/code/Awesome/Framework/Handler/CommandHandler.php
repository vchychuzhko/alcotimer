<?php

namespace Awesome\Framework\Handler;

use Awesome\Framework\XmlParser\CommandXmlParser;

class CommandHandler extends \Awesome\Framework\Model\Handler\AbstractHandler
{
    public const DEFAULT_COMMAND = 'help:show';

    /**
     * @var CommandXmlParser $commandXmlParser
     */
    private $commandXmlParser;

    /**
     * @var array $parsedHandles
     */
    private $parsedHandles = [];

    /**
     * CliHandler constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->commandXmlParser = new CommandXmlParser();
    }

    /**
     * Get responsible class by requested handle.
     * @inheritDoc
     */
    public function process($handle)
    {
        if ($handle === '') {
            $commandData = $this->commandXmlParser->get(self::DEFAULT_COMMAND);
        } else {
            $commandData = $this->commandXmlParser->get($handle);
        }

        return $commandData;
    }

    /**
     * @inheritDoc
     */
    public function exist($handle)
    {
        $exist = false;

        if ($handle) {
            $handle = $this->parse($handle);
            $exist = in_array($handle, $this->commandXmlParser->getHandles());
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
     * Get command class name.
     * @param string $command
     * @return string
     */
    public function getCommandClass($command)
    {
        $command = $this->parse($command);
        $handles = $this->commandXmlParser->getHandlesClasses();

        return $handles[$command] ?? null;
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
        $consoleCommands = $this->commandXmlParser->getHandles();

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
