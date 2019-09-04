<?php

namespace Awesome\Base\Model;

class XmlParser
{
    public const FRONTEND_VIEW = 'frontend';
    public const ADMINHTML_VIEW = 'adminhtml';
    public const BASE_VIEW = 'base';

    /**
     * @var \Awesome\Cache\Model\Cache $cache
     */
    protected $cache;

    /**
     * XmlParser constructor.
     */
    function __construct()
    {
        $this->cache = new \Awesome\Cache\Model\Cache();
    }

    /**
     * Convert XML node into array.
     * @param \SimpleXMLElement $xmlNode
     * @return array
     */
    public function parseXmlNode($xmlNode) {
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

                $parsedNode[$nodeName]['children'][$childName] = $child[$childName];
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
    protected function stringBooleanCheck($value)
    {
        if ($value === 'true' || $value === 'false') {
            $value = $value === 'true';
        }

        return $value;
    }
}
