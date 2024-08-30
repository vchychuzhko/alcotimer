<?php
declare(strict_types=1);

namespace Vch\Framework\Helper;

/**
 * @deprecated
 */
class XmlParsingHelper
{
    /**
     * Get attribute value from the provided XML node.
     * @param \SimpleXMLElement $node
     * @param string $attribute
     * @return string|null
     */
    public static function getNodeAttribute(\SimpleXMLElement $node, string $attribute): ?string
    {
        return $node[$attribute] ? (string) $node[$attribute] : null;
    }

    /**
     * Get node attribute name if any.
     * @param \SimpleXMLElement $node
     * @return string
     */
    public static function getNodeAttributeName(\SimpleXMLElement $node): string
    {
        return self::getNodeAttribute($node, 'name');
    }

    /**
     * Get child node by name.
     * Return the first one if several nodes found.
     * @param \SimpleXMLElement $node
     * @param string $childNodeName
     * @return \SimpleXMLElement|null
     */
    public static function getChildNode(\SimpleXMLElement $node, string $childNodeName): ?\SimpleXMLElement
    {
        $childNode = null;

        foreach ($node->$childNodeName as $child) {
            $childNode = $child;
            break;
        }

        return $childNode;
    }

    /**
     * Get node inner content.
     * @param \SimpleXMLElement $node
     * @return string
     */
    public static function getNodeContent(\SimpleXMLElement $node): string
    {
        return (string) $node;
    }

    /**
     * Check if node attribute is a boolean "true".
     * Return default value as false if attribute is not set.
     * @param \SimpleXMLElement $node
     * @param string $attribute
     * @param bool $default
     * @return bool
     */
    public static function isAttributeBooleanTrue(\SimpleXMLElement $node, string $attribute, bool $default = false): bool
    {
        return self::getNodeAttribute($node, $attribute)
            ? DataHelper::isStringBooleanTrue(self::getNodeAttribute($node, $attribute))
            : $default;
    }

    /**
     * Check if node element is disabled.
     * If attribute is absent, false will be returned.
     * @param \SimpleXMLElement $node
     * @return bool
     */
    public static function isDisabled(\SimpleXMLElement $node): bool
    {
        return self::isAttributeBooleanTrue($node, 'disabled');
    }

    /**
     * Apply sort order rules to a parsed node element.
     * Recursively by default.
     * @param array $nodeElement
     * @param bool $recursive
     */
    public static function applySortOrder(array &$nodeElement, bool $recursive = true)
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
