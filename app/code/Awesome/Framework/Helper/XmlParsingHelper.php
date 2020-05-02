<?php

namespace Awesome\Framework\Helper;

class XmlParsingHelper
{
    /**
     * Get attribute value from the provided XML node.
     * @param \SimpleXMLElement $node
     * @param string $attribute
     * @return string
     */
    public static function getNodeAttribute($node, $attribute = 'name')
    {
        return (string) $node[$attribute];
    }

    /**
     * Check if string is a boolean "true", otherwise return false.
     * Not case sensitive.
     * @param string $string
     * @return bool
     */
    public static function stringBooleanCheck($string)
    {
        return strtolower($string) === 'true';
    }

    /**
     * Apply sort order rules to a parsed node element.
     * Recursively by default.
     * @param array $nodeElement
     * @param bool $recursive
     */
    public static function applySortOrder(&$nodeElement, $recursive = true)
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
                    self::applySortOrder($nodeElement[$index]);
                }
            }
        }
    }
}
