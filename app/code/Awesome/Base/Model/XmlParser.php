<?php

namespace Awesome\Base\Model;

class XmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';

    /**
     * Collect data about all available console commands.
     * @return array
     */
    public function retrieveConsoleCommands()
    {
        //@TODO: implement cache functionality for all cli commands
        $commandList = [];

        foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
            $cliData = simplexml_load_file($cliXmlFile);
            $namespace = (string)$cliData['namespace'];

            if (!isset($commandList[$namespace])) {
                $commandList[$namespace] = [];
            }

            foreach ($cliData->command as $commandNode) {
                $options = [];

                foreach ($commandNode->optionList as $option) {
                    $options[(string)$option->option['name']] = [
                        'required' => $this->stringToBoolean((string)$option->option['required']),
                        'mask' => (string)$option->option->mask
                    ];
                }

                $commandList[$namespace][(string)$commandNode['name']] = [
                    'class' => (string)$commandNode['class'],
                    'description' => (string)$commandNode->description,
                    'options' => $options
                ];
            }
        }

        return $commandList;
    }

    /**
     * Convert xml true/false value into boolean.
     * @param string $valueInString
     * @return boolean
     */
    private function stringToBoolean($valueInString)
    {
        return $valueInString === 'true';
    }
}
