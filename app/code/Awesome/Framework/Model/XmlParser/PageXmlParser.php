<?php

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Framework\Model\App;
use Awesome\Framework\Block\Template\Container;
use Awesome\Cache\Model\Cache;

class PageXmlParser extends \Awesome\Framework\Model\AbstractXmlParser
{
    private const DEFAULT_PAGE_XML_PATH_PATTERN = '/*/*/view/%v/layout/default.xml';
    private const PAGE_XML_PATH_PATTERN = '/*/*/view/%v/layout/%h.xml';
    private const PAGE_HANDLES_CACHE_TAG = 'page-handles';

    /**
     * @var array $collectedAssets
     */
    private $collectedAssets = [
        'lib' => [],
        'script' => [],
        'css' => []
    ];

    /**
     * Collect page structure according to the requested handle.
     * @param string $handle
     * @param string $view
     * @return array
     */
    public function retrievePageStructure($handle, $view)
    {
        if (!$pageStructure = $this->cache->get(Cache::LAYOUT_CACHE_KEY, $handle)) {
            $defaultPattern = APP_DIR . str_replace('%v', $view, self::DEFAULT_PAGE_XML_PATH_PATTERN);

            foreach (glob($defaultPattern) as $defaultXmlFile) {
                $pageData = simplexml_load_file($defaultXmlFile);

                $parsedData = $this->parsePageNode($pageData);
                $pageStructure = array_replace_recursive($pageStructure, $parsedData);
            }

            $pattern = APP_DIR . str_replace('%v', $view, self::PAGE_XML_PATH_PATTERN);
            $pattern = str_replace('%h', $handle, $pattern);

            foreach (glob($pattern) as $pageXmlFile) {
                $pageData = simplexml_load_file($pageXmlFile);

                $parsedData = $this->parsePageNode($pageData);
                $pageStructure = array_replace_recursive($pageStructure, $parsedData);
            }

            //@TODO: Add check for minify/merge enabled and replace links
            $pageStructure['head'] = array_merge($pageStructure['head'], $this->collectedAssets);

            $pageStructure['body'] = $this->applySortOrder($pageStructure['body']);

            $this->cache->save(Cache::LAYOUT_CACHE_KEY, $handle, $pageStructure);
        }

        return $pageStructure;
    }

    /**
     * Check if requested page handle exist.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    public function handleExist($handle, $view)
    {
        return in_array($handle, $this->collectHandles($view));
    }

    /**
     * Retrieve all available page handles for a specific view.
     * Return all of handles if view is not specified.
     * @param string $requestedView
     * @return array
     */
    private function collectHandles($requestedView = '')
    {
        if (!$handles = $this->cache->get(Cache::LAYOUT_CACHE_KEY, self::PAGE_HANDLES_CACHE_TAG)) {
            foreach ([App::FRONTEND_VIEW, App::BACKEND_VIEW, App::BASE_VIEW] as $view) {
                $pattern = APP_DIR . str_replace('%v', $view, self::PAGE_XML_PATH_PATTERN);
                $pattern = str_replace('%h', '*', $pattern);
                $collectedHandles = [];

                foreach (glob($pattern) as $collectedHandle) {
                    $collectedHandle = explode('/', $collectedHandle);
                    $collectedHandle = str_replace('.xml', '', end($collectedHandle));

                    $collectedHandles[] = $collectedHandle;
                }

                $handles[$view] = array_unique($collectedHandles);
            }

            $this->cache->save(Cache::LAYOUT_CACHE_KEY, self::PAGE_HANDLES_CACHE_TAG, $handles);
        }

        if ($requestedView) {
            $handles = $handles[$requestedView] ?? [];
        }

        return $handles;
    }

    /**
     * Convert page XML node into array.
     * @param \SimpleXMLElement $xmlNode
     * @return array
     */
    private function parsePageNode($xmlNode) {
        $parsedNode = [];

        foreach ($xmlNode->children() as $mainNode) {
            if ($mainNode->getName() === 'head') {
                $parsedNode['head'] = $this->parseHeadNode($mainNode);
            }

            if ($mainNode->getName() === 'body') {
                $parsedNode['body'] = $this->parseBodyNode($mainNode)['body'];
            }
        }

        return $parsedNode;
    }

    /**
     * Parse head part of XML page node.
     * @param \SimpleXMLElement $headNode
     * @return array
     */
    private function parseHeadNode($headNode)
    {
        $parsedHeadNode = [];

        foreach ($headNode->children() as $child) {
            switch ($childName = $child->getName()) {
                case 'title':
                case 'description':
                case 'keywords':
                    $parsedHeadNode[$childName] = (string) $child;
                    break;
                case 'favicon':
                    $parsedHeadNode[$childName] = (string) $child['src'];
                    break;
                case 'lib':
                case 'script':
                case 'css':
                    $this->collectedAssets[$childName][] = (string) $child['src'];
                    break;
            }
        }

        return $parsedHeadNode;
    }

    /**
     * Convert Page XML node into array.
     * @param \SimpleXMLElement $xmlNode
     * @return array
     */
    public function parseBodyNode($xmlNode) {
        $parsedNode = [];
        $nodeName = $xmlNode->getName();
        $attributes = [];

        if ($nodeName === Container::CONTAINER_XML_TAG) {
            $attributes['class'] = Container::class;
        }

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
                $child = $this->parseBodyNode($child);
                $childName = array_key_first($child);

                if ($nodeName === 'data' || $childName === 'data') {
                    $parsedNode[$nodeName][$childName] = $child[$childName];
                } else {
                    $parsedNode[$nodeName]['children'][$childName] = $child[$childName];
                }
            }
        } elseif ($text = trim((string) $xmlNode)) {
            $parsedNode[$nodeName] = $text;
        }

        return $parsedNode;
    }

    /**
     * Apply sort order rules for block children.
     * @param array $blockStructure
     * @return array
     */
    public function applySortOrder($blockStructure)
    {
        $children = $blockStructure['children'] ?? [];

        if ($children) {
            uasort(
                $blockStructure['children'],
                function ($a, $b)
                {
                    $a = $a['sortOrder'] ?? -1;
                    $b = $b['sortOrder'] ?? -1;
                    $compare = $a <=> $b;

                    if ($a < 0 || $b < 0) {
                        $compare = 0;
                    }

                    return $compare;
                }
            );

            foreach ($children as $childName => $child) {
                $blockStructure['children'][$childName] = $this->applySortOrder($child);
            }
        }

        return $blockStructure;
    }
}
