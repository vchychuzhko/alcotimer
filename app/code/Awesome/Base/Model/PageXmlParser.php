<?php

namespace Awesome\Base\Model;

class PageXmlParser extends \Awesome\Base\Model\XmlParser
{
    private const DEFAULT_PAGE_XML_PATH_PATTERN = '/*/*/view/%v/layout/default.xml';
    private const PAGE_XML_PATH_PATTERN = '/*/*/view/%v/layout/%h.xml';
    private const PAGE_CACHE_KEY = 'pages';

    /**
     * @var array $assetTags
     */
    private $assetTags = [
        'script',
        'css'
    ];

    /**
     * @var array $collectedAssets
     */
    private $collectedAssets = [];

    /**
     * Collect page structure according to the requested handle.
     * @param string $handle
     * @param string $view
     * @return array
     */
    public function retrievePageStructure($handle, $view)
    {
        if (!$pageStructure = $this->cache->get(self::PAGE_CACHE_KEY, $handle)) {
            $pattern = APP_DIR . str_replace('%v', $view, self::PAGE_XML_PATH_PATTERN);
            $pattern = str_replace('%h', $handle, $pattern);

            foreach (glob($pattern) as $pageXmlFile) {
                $pageData = simplexml_load_file($pageXmlFile);

                $parsedData = $this->parseXmlNode($pageData);
                $pageStructure = array_merge_recursive($pageStructure, $parsedData['page']);
            }

            if (!empty($pageStructure)) {
                $pattern = APP_DIR . str_replace('%v', $view, self::DEFAULT_PAGE_XML_PATH_PATTERN);

                foreach (glob($pattern) as $defaultXmlFile) {
                    $defaultData = simplexml_load_file($defaultXmlFile);

                    $parsedData = $this->parseXmlNode($defaultData);
                    $pageStructure = array_merge_recursive($pageStructure, $parsedData['page']);
                }

                $pageStructure['head']['links'] = $this->collectedAssets;

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
    protected function parseXmlNode($xmlNode) {
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

                if (in_array($childName, $this->assetTags)) {
                    $this->collectedAssets[$childName . 's'][] = $child[$childName];
                } else {
                    $parsedNode[$nodeName][$childName] = $child[$childName];
                }
            }
        } elseif ($text = trim((string)$xmlNode)) {
            $parsedNode[$nodeName]['text'] = $text;
        }

        return $parsedNode;
    }
}
