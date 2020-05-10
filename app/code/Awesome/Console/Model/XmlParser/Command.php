<?php

namespace Awesome\Console\Model\XmlParser;

class Command
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';

    /**
     * @var array $commandsClasses
     */
    private $commandsClasses;

    /**
     * Get available commands with their responsible classes.
     * @return array
     * @throws \LogicException
     */
    public function getCommandsClasses()
    {
        if ($this->commandsClasses === null) {
            $this->commandsClasses = [];

            foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
                $cliData = simplexml_load_file($cliXmlFile);
                $parsedData = $this->parse($cliData);

                foreach ($parsedData as $commandName => $command) {
                    if (isset($this->commands[$commandName])) {
                        throw new \LogicException(sprintf('Command "%s" is already defined', $commandName));
                    }

                    if (!$command['disabled']) {
                        $this->commandsClasses[$commandName] = $command['class'];
                    }
                }
            }
            ksort($this->commandsClasses);
        }

        return $this->commandsClasses;
    }

    /**
     * Convert XML command node into data array.
     * @param \SimpleXMLElement $commandNode
     * @return array
     * @throws \LogicException
     */
    private function parse($commandNode)
    {
        $parsedNode = [];

        foreach ($commandNode->children() as $namespace) {
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
