<?php

namespace Awesome\Base\Model;

class XmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const CLI_CACHE_KEY = 'cli';

    /**
     * @var \Awesome\Cache\Model\Cache $cache
     */
    private $cache;

    /**
     * XmlParser constructor.
     */
    function __construct()
    {
        $this->cache = new \Awesome\Cache\Model\Cache();
    }

    /**
     * Collect data about all available console commands.
     * @return array
     */
    public function retrieveConsoleCommands()
    {
        if (!$commandList = $this->cache->get(self::CLI_CACHE_KEY)) {
            foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
                $cliData = simplexml_load_file($cliXmlFile);

                $parsedData = $this->parseXmlNode($cliData);
                $commandList = array_merge_recursive($commandList, $parsedData['config']);
            }

            $this->cache->save($commandList, self::CLI_CACHE_KEY);
        }

        return $commandList;
    }

    /**
     * Convert XML node into array
     * @param \SimpleXMLElement $xmlNode
     * @return array
     */
    private function parseXmlNode($xmlNode) {
        $parsedNode = [];
        $nodeName = $xmlNode->getName();
        $attributes = [];

        foreach ($xmlNode->attributes() as $attributeName => $attributeValue) {
            $attributeValue = (string)$attributeValue;

            if ($attributeName === 'name') {
                $nodeName = $attributeValue;
            } else {
                $attributes[$attributeName] = $this->stringBooleanCheck($attributeValue);
            }
        }
        $parsedNode[$nodeName] = $attributes;
        $children = $xmlNode->children();

        if ($childrenCount = count($children)) {
            foreach ($children as $parentName => $child) {
                $child = $this->parseXmlNode($child);
                $childName = array_key_first($child);

                $parsedNode[$nodeName][$childName] = $child[$childName];
            }
        } else {
            $parsedNode[$nodeName] = trim((string)$xmlNode);
        }

        return $parsedNode;
    }

    /**
     * Check if string is a boolean and convert it.
     * @param string $value
     * @return string|boolean
     */
    private function stringBooleanCheck($value)
    {
        if ($value === 'true' || $value === 'false') {
            $value = $value === 'true';
        }

        return $value;
    }
}
