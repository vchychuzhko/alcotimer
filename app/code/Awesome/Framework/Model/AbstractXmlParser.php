<?php

namespace Awesome\Framework\Model;

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

    /**
     * Apply sort order rules to a parsed node element.
     * Recursively by default.
     * @param array $nodeElement
     * @param bool $recursive
     */
    protected function applySortOrder(&$nodeElement, $recursive = true)
    {
        if (is_array($nodeElement)) {
            uasort($nodeElement, function ($a, $b) {
                $a = $a['sortOrder'] ?? null;
                $b = $b['sortOrder'] ?? null;

                if ($a === null || $b === null) {
                    $compare = 0;
                } else {
                    $compare = $a <=> $b;
                }

                return $compare;
            });

            if ($recursive) {
                foreach ($nodeElement as $index => $unused) {
                    $this->applySortOrder($nodeElement[$index]);
                }
            }
        }
    }
}
