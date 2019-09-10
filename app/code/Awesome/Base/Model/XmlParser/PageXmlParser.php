<?php

namespace Awesome\Base\Model\XmlParser;

use Awesome\Base\Model\App;

class PageXmlParser extends \Awesome\Base\Model\AbstractXmlParser
{
    private const DEFAULT_PAGE_XML_PATH_PATTERN = '/*/*/view/%v/layout/default.xml';
    private const PAGE_XML_PATH_PATTERN = '/*/*/view/%v/layout/%h.xml';
    private const PAGE_CACHE_KEY = 'pages';
    private const PAGE_CACHE_TAG = 'page-handles';

    /**
     * @var array $assetMap
     */
    private $assetMap = [
        'script' => 'scripts',
        'css' => 'styles'
    ];

    /**
     * @var array $collectedAssets
     */
    private $collectedAssets = [
        'scripts' => [],
        'styles' => []
    ];

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

                $parsedData = $this->parsePageNode($pageData);
                $pageStructure = array_merge_recursive($pageStructure, $parsedData);
            }

            if (!empty($pageStructure)) {
                $pattern = APP_DIR . str_replace('%v', $view, self::DEFAULT_PAGE_XML_PATH_PATTERN);

                foreach (glob($pattern) as $defaultXmlFile) {
                    $defaultData = simplexml_load_file($defaultXmlFile);

                    $parsedData = $this->parsePageNode($defaultData);
                    $pageStructure = array_merge_recursive($pageStructure, $parsedData);
                }

                $pageStructure['head']['scripts'] = $this->collectedAssets['scripts'];
                $pageStructure['head']['styles'] = $this->collectedAssets['styles'];
                //@TODO: if merge or minify (get this value from StaticContent Class) change links

                $pageStructure['body'] = $this->applySortOrder($pageStructure['body']);

                $this->cache->save(self::PAGE_CACHE_KEY, $handle, $pageStructure);
            }
        }

        return $pageStructure;
    }

    /**
     * Check if requested handle exist in collected handles.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    public function handleExist($handle, $view)
    {
        return in_array($handle, $this->collectHandles()[$view]);
    }

    /**
     * Retrieve all available page handles and save them to cache.
     * @return array
     */
    private function collectHandles()
    {
        if (!$handles = $this->cache->get(self::PAGE_CACHE_KEY, self::PAGE_CACHE_TAG)) {
            foreach ([App::FRONTEND_VIEW, App::BACKEND_VIEW, App::BASE_VIEW] as $view) {
                $pattern = APP_DIR . str_replace('%v', $view, self::PAGE_XML_PATH_PATTERN);
                $pattern = str_replace('%h', '*', $pattern);
                $collectedHandles = [];

                if ($foundHandles = glob($pattern)) {
                    foreach ($foundHandles as $collectedHandle) {
                        $collectedHandle = explode('/', $collectedHandle);
                        $collectedHandle = str_replace('.xml', '', end($collectedHandle));

                        $collectedHandles[] = $collectedHandle;
                    }
                }

                $handles[$view] = array_flip(array_flip($collectedHandles));
            }

            $this->cache->save(self::PAGE_CACHE_KEY, self::PAGE_CACHE_TAG, $handles);
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
        $children = $headNode->children();

        foreach ($children as $child) {
            $childName = $child->getName();

            if (isset($this->assetMap[$childName])) {
                $this->collectedAssets[$this->assetMap[$childName]][] = reset($child['src']);
            } else {
                $parsedHeadNode[$childName] = trim((string)$child);
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
                    return ($a['sortOrder'] <=> $b['sortOrder']);
                }
            );

            foreach ($children as $childName => $child) {
                $blockStructure['children'][$childName] = $this->applySortOrder($child);
            }
        }

        return $blockStructure;
    }
}
