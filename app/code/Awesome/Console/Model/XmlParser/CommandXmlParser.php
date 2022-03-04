<?php
declare(strict_types=1);

namespace Awesome\Console\Model\XmlParser;

use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Helper\XmlParsingHelper;
use Awesome\Framework\Model\FileManager\XmlFileManager;

class CommandXmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const CLI_XSD_SCHEMA_PATH = '/Awesome/Console/Schema/cli.xsd';

    private XmlFileManager $xmlFileManager;

    /**
     * CommandXmlParser constructor.
     * @param XmlFileManager $xmlFileManager
     */
    public function __construct(
        XmlFileManager $xmlFileManager
    ) {
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
                    throw new XmlValidationException(__('Command "%1" is already defined', $commandName));
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
        $commandNode = $this->xmlFileManager->parseXmlFileNext($cliXmlFile, APP_DIR . self::CLI_XSD_SCHEMA_PATH);

        foreach ($commandNode['_namespace'] as $namespace) {
            foreach ($namespace['_command'] as $command) {
                if (!isset($command['disabled']) || !XmlParsingHelper::isAttributeBooleanTrue($command['disabled'])) { // @TODO: Move this to AbstractXmlParser
                    $commandName = $namespace['name'] . ':' . $command['name'];

                    $parsedNode[$commandName] = '\\' . ltrim($command['class'], '\\');
                }
            }
        }

        return $parsedNode;
    }
}
