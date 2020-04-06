<?php

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Cache\Model\Cache;

abstract class AbstractXmlParser
{
    /**
     * @var Cache $cache
     */
    protected $cache;

    /**
     * XmlParser constructor.
     */
    function __construct()
    {
        $this->cache = new Cache();
    }

    /**
     * Get structure data according to the requested handle.
     * @param string $handle
     * @return array
     */
    abstract public function get($handle);

    /**
     * Get all available handles.
     * @return array
     */
    abstract public function getHandles();

    /**
     * Convert XML node into array.
     * @param \SimpleXMLElement $node
     * @return array
     */
    abstract protected function parse($node);

    /**
     * Get attribute value from the provided XML node.
     * @param \SimpleXMLElement $node
     * @param string $attribute
     * @return string
     */
    protected function getNodeAttribute($node, $attribute = 'name')
    {
        return (string) $node[$attribute];
    }

    /**
     * Check if string is a boolean "true", otherwise return false.
     * Not case sensitive.
     * @param string $string
     * @return bool
     */
    protected function stringBooleanCheck($string)
    {
        return strtolower($string) === 'true';
    }
}
