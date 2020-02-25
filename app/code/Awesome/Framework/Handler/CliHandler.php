<?php

namespace Awesome\Framework\Handler;

use Awesome\Framework\Model\Cli\Input;
use Awesome\Framework\XmlParser\CliXmlParser;

class CliHandler extends \Awesome\Framework\Model\Handler\AbstractHandler
{
    /**
     * @var CliXmlParser $cliXmlParser
     */
    private $cliXmlParser;

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
            $handles = $this->cliXmlParser->getHandlesWithClasses();
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
        $command = $handle;
        $possibleMatches = $this->getPossibleCandidates($handle);

        if (count($possibleMatches) === 1) {
            $command = $possibleMatches[0];
        }

        return $command;
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
                list($commandNamespace, $commandCommand) = explode(':', $consoleCommand);

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
     */
    public function parseInput()
    {
        $args = $_SERVER['argv'] ?? [];
        $command = '';
        $options = [];
        $arguments = [];

        if (isset($args[1]) && strpos($args[1], '-') !== 0) {
            $command = $args[1];
        }
        $optionsShortcutMap = [];

        /** @var AbstractCommand $className */
        $className = $this->exist($command) ? $this->process($command) : AbstractCommand::class;
        $shortcutMap = array_filter($className::getConfiguration()['options'], function ($option) {
            return $option['shortcut'];
        });

        foreach ($shortcutMap as $name => $option) {
            $optionsShortcutMap[$option['shortcut']] = $name;
        }

        foreach (array_slice($args, 1) as $arg) {
            if (strpos($arg, '--') === 0) {
                @list($option, $value) = explode('=', str_replace_first('--', '', $arg));
                $options[$option][] = $value ?: true;
                //@TODO: set option default value instead of 'true'
            } elseif (strpos($arg, '-') === 0) {
                $optionShortcuts = substr($arg, 1);

                foreach (str_split($optionShortcuts) as $optionShortcut) {
                    if (isset($optionsShortcutMap[$optionShortcut])) {
                        $options[$optionsShortcutMap[$optionShortcut]] = true;
                    }
                }
            } else {
                $arguments[] = $arg;
                //@TODO: use argument count to work with position
            }
        }

        return new Input($command, $options, $arguments);
    }
}
