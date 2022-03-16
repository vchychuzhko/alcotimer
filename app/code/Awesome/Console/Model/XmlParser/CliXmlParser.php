<?php
declare(strict_types=1);

namespace Awesome\Console\Model\XmlParser;

use Awesome\Framework\Exception\XmlValidationException;

class CliXmlParser extends \Awesome\Framework\Model\AbstractXmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const CLI_XSD_SCHEMA_PATH = '/Awesome/Console/Schema/cli.xsd';

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
                if (!$this->isDisabled($command)) {
                    $commandName = $namespace['name'] . ':' . $command['name'];

                    $parsedNode[$commandName] = '\\' . ltrim($command['class'], '\\');
                }
            }
        }

        return $parsedNode;
    }
}
