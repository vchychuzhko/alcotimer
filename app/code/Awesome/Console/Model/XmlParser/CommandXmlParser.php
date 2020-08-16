<?php

namespace Awesome\Console\Model\XmlParser;

use Awesome\Framework\Helper\XmlParsingHelper;

class CommandXmlParser
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
                $parsedData = $this->parse($cliXmlFile);

                foreach ($parsedData as $commandName => $command) {
                    if (!$command['disabled']) {
                        if (isset($this->commands[$commandName])) {
                            throw new \LogicException(sprintf('Command "%s" is already defined', $commandName));
                        }

                        $this->commandsClasses[$commandName] = $command['class'];
                    }
                }
            }
            ksort($this->commandsClasses);
        }

        return $this->commandsClasses;
    }

    /**
     * Parse commands XML file.
     * @param string $cliXmlFile
     * @return array
     * @throws \LogicException
     */
    private function parse($cliXmlFile)
    {
        $parsedNode = [];
        $commandNode = simplexml_load_file($cliXmlFile);

        foreach ($commandNode->children() as $namespace) {
            if (!$namespaceName = (string) $namespace['name']) {
                throw new \LogicException(sprintf('Name attribute is not specified for namespace in "%s" file', $cliXmlFile));
            }

            foreach ($namespace->children() as $command) {
                if (!$commandName = XmlParsingHelper::getNodeAttribute($command)) {
                    throw new \LogicException(sprintf('Name attribute is not specified for "%s" namespace command', $namespaceName));
                }
                $commandName = $namespaceName . ':' . $commandName;

                if (isset($parsedNode[$commandName])) {
                    throw new \LogicException(sprintf('Command "%s" is defined twice in one file', $commandName));
                }
                $class = XmlParsingHelper::getNodeAttribute($command, 'class');

                if (!$class) {
                    throw new \LogicException(sprintf('Class is not specified for "%s" command', $commandName));
                }
                $class = ltrim($class, '\\');
                $disabled = XmlParsingHelper::isAttributeBooleanTrue($command);

                $parsedNode[$commandName] = [
                    'class' => $class,
                    'disabled' => $disabled
                ];
            }
        }

        return $parsedNode;
    }
}
