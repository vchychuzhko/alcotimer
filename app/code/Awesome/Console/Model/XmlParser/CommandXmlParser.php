<?php
declare(strict_types=1);

namespace Awesome\Console\Model\XmlParser;

use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Helper\XmlParsingHelper;
use Awesome\Framework\Model\FileManager\XmlFileManager;

class CommandXmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';

    /**
     * @var XmlFileManager $xmlFileManager
     */
    private $xmlFileManager;

    /**
     * CommandXmlParser constructor.
     * @param XmlFileManager $xmlFileManager
     */
    public function __construct(XmlFileManager $xmlFileManager)
    {
        $this->xmlFileManager = $xmlFileManager;
    }

    /**
     * Get available commands with their responsible classes.
     * @return array
     * @throws \Exception
     */
    public function getCommandsClasses(): array
    {
        $commandsClasses = [];

        foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
            $parsedData = $this->parse($cliXmlFile);

            foreach ($parsedData as $commandName => $commandClass) {
                if (isset($this->commands[$commandName])) {
                    throw new XmlValidationException(sprintf('Command "%s" is already defined', $commandName));
                }

                $commandsClasses[$commandName] = $commandClass;
            }
        }
        ksort($commandsClasses);

        return $commandsClasses;
    }

    /**
     * Parse commands XML file.
     * @param string $cliXmlFile
     * @return array
     * @throws \Exception
     */
    private function parse(string $cliXmlFile): array
    {
        $parsedNode = [];
        $commandNode = $this->xmlFileManager->parseXmlFile($cliXmlFile);

        foreach ($commandNode->children() as $namespace) {
            if (!$namespaceName = XmlParsingHelper::getNodeAttributeName($namespace)) {
                throw new XmlValidationException(
                    sprintf('Name attribute is not specified for namespace in "%s" file', $cliXmlFile)
                );
            }

            foreach ($namespace->children() as $command) {
                if (!XmlParsingHelper::isDisabled($command)) {
                    if (!$commandName = XmlParsingHelper::getNodeAttributeName($command)) {
                        throw new XmlValidationException(
                            sprintf('Name attribute is not specified for "%s" namespace command', $namespaceName)
                        );
                    }
                    $commandName = $namespaceName . ':' . $commandName;

                    if (isset($parsedNode[$commandName])) {
                        throw new XmlValidationException(
                            sprintf('Command "%s" is defined twice in one file', $commandName)
                        );
                    }
                    if (!$class = ltrim(XmlParsingHelper::getNodeAttribute($command, 'class'), '\\')) {
                        throw new XmlValidationException(
                            sprintf('Class is not specified for "%s" command', $commandName)
                        );
                    }

                    $parsedNode[$commandName] = $class;
                }
            }
        }

        return $parsedNode;
    }
}
