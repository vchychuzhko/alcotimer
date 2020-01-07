<?php

namespace Awesome\Console\Model\XmlParser;

use Awesome\Cache\Model\Cache;

class CliXmlParser extends \Awesome\Framework\Model\AbstractXmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const CLI_CACHE_TAG = 'cli';

    /**
     * Collect data about all available console commands.
     * @return array
     */
    public function retrieveConsoleCommands()
    {
        if (!$commandList = $this->cache->get(Cache::ETC_CACHE_KEY, self::CLI_CACHE_TAG)) {
            foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
                $cliData = simplexml_load_file($cliXmlFile);

                $parsedData = $this->parseCliNode($cliData);
                $commandList = array_replace_recursive($commandList, $parsedData);
            }

            $this->cache->save(Cache::ETC_CACHE_KEY, self::CLI_CACHE_TAG, $commandList);
        }

        return $commandList;
    }

    /**
     * Convert Cli XML node into array.
     * @param \SimpleXMLElement $xmlNode
     * @return array
     */
    private function parseCliNode($xmlNode)
    {
        $parsedNode = [];

        foreach ($xmlNode->children() as $namespace) {
            $namespaceName = (string) $namespace['name'];
            $parsedNode[$namespaceName] = [];

            foreach ($namespace->children() as $command) {
                $class = (string) $command['class'];
                $disabled = $this->stringBooleanCheck((string) $command['disabled']);
                $description = (string) $command->description;
                $options = [];

                foreach ($command->children() as $commandField) {
                    if ($commandField->getName() === 'option') {
                        $options[(string) $commandField['name']] = [
                            'required' => $this->stringBooleanCheck((string) $commandField['required']),
                            'description' => (string) $commandField->description
                        ];
                    }
                }

                $parsedNode[$namespaceName][(string) $command['name']] = [
                    'class' => $class,
                    'disabled' => $disabled,
                    'description' => $description,
                    'options' => $options
                ];
            }
        }

        return $parsedNode;
    }
}
