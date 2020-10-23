<?php
declare(strict_types=1);

namespace Awesome\Framework\Helper;

class XmlParsingHelper
{
    /**
     * Get attribute value from the provided XML node.
     * Process "name" attribute by default.
     * @param \SimpleXMLElement $node
     * @param string $attribute
     * @return string
     */
    public static function getNodeAttribute(\SimpleXMLElement $node, string $attribute = 'name'): string
    {
        return (string) $node[$attribute];
    }

    /**
     * Check if node attribute is a boolean "true".
     * Process "disabled" attribute by default.
     * @param \SimpleXMLElement $node
     * @param string $attribute
     * @return bool
     */
    public static function isAttributeBooleanTrue(\SimpleXMLElement $node, string $attribute = 'disabled'): bool
    {
        return DataHelper::isStringBooleanTrue(self::getNodeAttribute($node, $attribute));
    }

    /**
     * Apply sort order rules to a parsed node element.
     * Recursively by default.
     * @param array $nodeElement
     * @param bool $recursive
     * @return void
     */
    public static function applySortOrder(array &$nodeElement, bool $recursive = true): void
    {
        uasort($nodeElement, static function ($a, $b) {
            $compare = 0;

            if (isset($a['sortOrder'], $b['sortOrder'])) {
                $compare = $a['sortOrder'] <=> $b['sortOrder'];
            }

            return $compare;
        });

        if ($recursive) {
            foreach ($nodeElement as &$childElement) {
                if (is_array($childElement)) {
                    self::applySortOrder($childElement);
                }
            }
        }
    }
}
