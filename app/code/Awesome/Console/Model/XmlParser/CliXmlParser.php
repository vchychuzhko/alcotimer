<?php

namespace Awesome\Console\Model\XmlParser;

class CliXmlParser extends \Awesome\Base\Model\AbstractXmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const ETC_CACHE_KEY = 'etc';
    private const CLI_CACHE_TAG = 'cli';

    /**
     * Collect data about all available console commands.
     * @return array
     */
    public function retrieveConsoleCommands()
    {
        if (!$commandList = $this->cache->get(self::ETC_CACHE_KEY, self::CLI_CACHE_TAG)) {
            foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
                $cliData = simplexml_load_file($cliXmlFile);

                $parsedData = $this->parseCliNode($cliData);
                $commandList = array_merge_recursive($commandList, $parsedData['config']);
            }

            $this->cache->save(self::ETC_CACHE_KEY, self::CLI_CACHE_TAG, $commandList);
        }

        return $commandList;
    }

    /**
     * Convert Cli XML node into array.
     * @param \SimpleXMLElement $xmlNode
     * @return array
     */
    private function parseCliNode($xmlNode) {
        $parsedNode = [];
        $nodeName = $xmlNode->getName();
        $attributes = [];

        foreach ($xmlNode->attributes() as $attributeName => $attributeValue) {
            $attributeValue = (string) $attributeValue;

            if ($attributeName === 'name') {
                $nodeName = $attributeValue;
            } else {
                $attributes[$attributeName] = $this->stringBooleanCheck($attributeValue);
            }
        }
        $parsedNode[$nodeName] = $attributes;
        $children = $xmlNode->children();

        if (count($children)) {
            foreach ($children as $child) {
                $child = $this->parseCliNode($child);
                $childName = array_key_first($child);

                $parsedNode[$nodeName][$childName] = $child[$childName];
            }
        } elseif ($text = trim((string) $xmlNode)) {
            $parsedNode[$nodeName] = $text;
        }

        return $parsedNode;
    }
}
