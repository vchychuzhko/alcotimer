<?php

namespace Awesome\Framework\XmlParser;

use Awesome\Framework\Model\Cli\AbstractCommand;
use Awesome\Framework\Model\Cli\Input\InputDefinition;

class CliXmlParser extends \Awesome\Framework\Model\XmlParser\AbstractXmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const DEFAULT_HANDLE = 'help:show';

    /**
     * @var array $commands
     */
    private $commands;

    /**
     * @var array $disabledCommands
     */
    private $disabledCommands;

    /**
     * @var array $commandsData
     */
    private $commandsData;

    /**
     * @inheritDoc
     * @throws \LogicException
     */
    public function get($handle)
    {
        if (!isset($this->commandsData[$handle])) {
            $commandList = $this->getHandlesClasses();

            if (isset($commandList[$handle])) {
                $definition = new InputDefinition();
                /** @var AbstractCommand $commandClass */
                $commandClass = $commandList[$handle];
                $definition = $commandClass::configure($definition);

                $this->commandsData[$handle] = array_replace_recursive(['class' => $commandClass], $definition->getDefinition());
            }
        }

        return $this->commandsData[$handle] ?? null;
    }

    /**
     * Return default structure data applicable for all commands inherited from AbstractCommand.
     * @return array
     * @throws \LogicException
     */
    public function getDefault()
    {
        return $this->get(self::DEFAULT_HANDLE);
    }

    /**
     * @inheritDoc
     * @throws \LogicException
     */
    public function getHandles()
    {
        return array_keys($this->getHandlesClasses());
    }

    /**
     * Get available handles with their responsible classes.
     * If includeDisabled is true, return also for disabled commands.
     * @param bool $includeDisabled
     * @return array
     * @throws \LogicException
     */
    public function getHandlesClasses($includeDisabled = false)
    {
        if ($this->commands === null) {
            $this->commands = [];
            $this->disabledCommands= [];

            foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
                $cliData = simplexml_load_file($cliXmlFile);
                $parsedData = $this->parse($cliData);

                foreach ($parsedData as $commandName => $command) {
                    if (isset($this->commands[$commandName]) || isset($this->disabledCommands[$commandName])) {
                        throw new \LogicException(sprintf('Command "%s" is already defined', $commandName));
                    }

                    if (!$command['disabled']) {
                        $this->commands[$commandName] = $command['class'];
                    } else {
                        $this->disabledCommands[$commandName] = $command['class'];
                    }
                }
            }
            ksort($this->commands);
            ksort($this->disabledCommands);
        }
        $commands = $this->commands;

        if ($includeDisabled) {
            $commands = array_merge($commands, $this->disabledCommands);
        }

        return $commands;
    }

    /**
     * @inheritDoc
     * @throws \LogicException
     */
    protected function parse($node)
    {
        $parsedNode = [];

        foreach ($node->children() as $namespace) {
            foreach ($namespace->children() as $command) {
                $commandName = $this->getNodeAttribute($namespace) . ':' . $this->getNodeAttribute($command);

                if (isset($parsedNode[$commandName])) {
                    throw new \LogicException(sprintf('Command "%s" is defined twice in one file.', $commandName));
                }
                $class = ltrim($this->getNodeAttribute($command, 'class'), '\\');

                if (!$class) {
                    throw new \LogicException(sprintf('Class is not specified for "%s" command.', $commandName));
                }
                $disabled = $this->stringBooleanCheck($this->getNodeAttribute($command, 'disabled'));

                $parsedNode[$commandName] = [
                    'class' => '\\' . $class,
                    'disabled' => $disabled
                ];
            }
        }

        return $parsedNode;
    }
}
