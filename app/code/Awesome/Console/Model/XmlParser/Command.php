<?php

namespace Awesome\Console\Model\XmlParser;

use Awesome\Console\Model\Cli\AbstractCommand;
use Awesome\Console\Model\Cli\Input\InputDefinition;

class Command
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';

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
    private $commandsData = [];

    /**
     * Get command data according to the requested command name.
     * @param string $commandName
     * @return array
     * @throws \LogicException
     */
    public function get($commandName)
    {
        if (!isset($this->commandsData[$commandName])) {
            $commandList = $this->getCommandsClasses();

            if (isset($commandList[$commandName])) {
                $definition = new InputDefinition();
                /** @var AbstractCommand $commandClass */
                $commandClass = $commandList[$commandName];
                $definition = $commandClass::configure($definition);

                $this->commandsData[$commandName] = array_replace_recursive(['class' => $commandClass], $definition->getDefinition());
            }
        }

        return $this->commandsData[$commandName] ?? null;
    }

    /**
     * Get all available commands.
     * @return array
     * @throws \LogicException
     */
    public function getCommands()
    {
        return array_keys($this->getCommandsClasses());
    }

    /**
     * Get available commands with their responsible classes.
     * If includeDisabled is true, return also disabled commands.
     * @param bool $includeDisabled
     * @return array
     * @throws \LogicException
     */
    public function getCommandsClasses($includeDisabled = false)
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
     * Convert XML command node into data array.
     * @param \SimpleXMLElement $node
     * @return array
     * @throws \LogicException
     */
    protected function parse($node)
    {
        $parsedNode = [];

        foreach ($node->children() as $namespace) {
            if (!$namespaceName = (string) $namespace['name']) {
                throw new \LogicException(sprintf('Name attribute is not specified for namespace.'));
            }

            foreach ($namespace->children() as $command) {
                if (!$commandName = (string) $command['name']) {
                    throw new \LogicException(sprintf('Name attribute is not specified for "%s" namespace command.', $namespaceName));
                }
                $commandName = $namespaceName . ':' . (string) $command['name'];

                if (isset($parsedNode[$commandName])) {
                    throw new \LogicException(sprintf('Command "%s" is defined twice in one file.', $commandName));
                }
                $class = ltrim((string) $command['class'], '\\');

                if (!$class) {
                    throw new \LogicException(sprintf('Class is not specified for "%s" command.', $commandName));
                }
                $disabled = strtolower((string) $command['disabled']) === 'true';

                $parsedNode[$commandName] = [
                    'class' => '\\' . $class,
                    'disabled' => $disabled
                ];
            }
        }

        return $parsedNode;
    }
}
