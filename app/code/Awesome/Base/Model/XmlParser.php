<?php

namespace Awesome\Base\Model;

class XmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const ETC_CACHE_KEY = 'etc';
    private const CLI_CACHE_TAG = 'cli';

    private const DEFAULT_PAGE_XML_PATH_PATTERN = '/*/*/view/*/layout/default.xml';
    private const PAGE_XML_PATH_PATTERN = '/*/*/view/*/layout/%n.xml';
    private const PAGE_CACHE_KEY = 'pages';

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
        if (!$commandList = $this->cache->get(self::ETC_CACHE_KEY, self::CLI_CACHE_TAG)) {
            foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
                $cliData = simplexml_load_file($cliXmlFile);

                $parsedData = $this->parseXmlNode($cliData);
                $commandList = array_merge_recursive($commandList, $parsedData['config']);
            }

            $this->cache->save(self::ETC_CACHE_KEY, self::CLI_CACHE_TAG, $commandList);
        }

        return $commandList;
    }

    /**
     * Collect page structure according to the requested handle.
     * @param $handle
     * @return array
     */
    public function retrievePageStructure($handle)
    {
        if (!$pageStructure = $this->cache->get(self::PAGE_CACHE_KEY, $handle)) {
            $pattern = APP_DIR . str_replace('%n', $handle, self::PAGE_XML_PATH_PATTERN);

            foreach (glob($pattern) as $pageXmlFile) {
                $pageData = simplexml_load_file($pageXmlFile);

                $parsedData = $this->parseXmlNode($pageData);
                $pageStructure = array_merge_recursive($pageStructure, $parsedData['page']);
            }

            if (!empty($pageStructure)) {
                $this->cache->save(self::PAGE_CACHE_KEY, $handle, $pageStructure);
            }
        }

        return $pageStructure;
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
        } elseif ($text = trim((string)$xmlNode)) {
            $parsedNode[$nodeName]['text'] = $text;
        }

        return $parsedNode;
    }

    /**
     * Check if string is a boolean and convert it.
     * @param string $value
     * @return string|bool
     */
    private function stringBooleanCheck($value)
    {
        if ($value === 'true' || $value === 'false') {
            $value = $value === 'true';
        }

        return $value;
    }
}
