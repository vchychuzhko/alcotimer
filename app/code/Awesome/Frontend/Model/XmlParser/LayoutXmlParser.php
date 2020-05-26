<?php

namespace Awesome\Frontend\Model\XmlParser;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Helper\XmlParsingHelper;
use Awesome\Frontend\Block\Template\Container;

class LayoutXmlParser
{
    private const DEFAULT_LAYOUT_XML_PATH_PATTERN = '/*/*/view/%s/layout/default.xml';
    private const LAYOUT_XML_PATH_PATTERN = '/*/*/view/%s/layout/%s.xml';
    private const PAGE_HANDLES_CACHE_TAG_PREFIX = 'page-handles_';

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var array $collectedAssets
     */
    private $collectedAssets = [
        'lib' => [],
        'script' => [],
        'css' => []
    ];

    /**
     * @var array $assetsToRemove
     */
    private $assetsToRemove = [];

    /**
     * @var array $references
     */
    private $references = [];

    /**
     * @var array $referencesToRemove
     */
    private $referencesToRemove = [];

    /**
     * LayoutXmlParser constructor.
     */
    function __construct()
    {
        $this->cache = new Cache();
    }

    /**
     * Get layout structure for requested handle for a specified view.
     * @param string $handle
     * @param string $view
     * @return array
     */
    public function getLayoutStructure($handle, $view)
    {
        if (!$layoutStructure = $this->cache->get(Cache::LAYOUT_CACHE_KEY, $handle)) {
            $layoutStructure = [];
            $defaultPattern = sprintf(self::DEFAULT_LAYOUT_XML_PATH_PATTERN, '{' . Http::BASE_VIEW . ',' . $view . '}');

            foreach (glob(APP_DIR . $defaultPattern, GLOB_BRACE) as $defaultXmlFile) {
                $layoutData = simplexml_load_file($defaultXmlFile);

                $parsedData = $this->parse($layoutData);
                $layoutStructure = array_replace_recursive($layoutStructure, $parsedData);
            }
            $pattern = sprintf(self::LAYOUT_XML_PATH_PATTERN, '{' . Http::BASE_VIEW . ',' . $view . '}', $handle);

            foreach (glob(APP_DIR . $pattern, GLOB_BRACE) as $layoutXmlFile) {
                $layoutData = simplexml_load_file($layoutXmlFile);

                $parsedData = $this->parse($layoutData);
                $layoutStructure = array_replace_recursive($layoutStructure, $parsedData);
            }

            // @TODO: add check if no layout data is found
            $this->filterRemovedAssets();
            // @TODO: Add check for minify/merge enabled and replace links
            $layoutStructure['head'] = array_merge($layoutStructure['head'], $this->collectedAssets);

            $this->applyReferences($layoutStructure['body']);
            XmlParsingHelper::applySortOrder($layoutStructure['body']);
            // @TODO: add validation for duplicating elements

            $this->cache->save(Cache::LAYOUT_CACHE_KEY, $handle, $layoutStructure);
        }

        return $layoutStructure;
    }

    /**
     * Get available page layout handles for specified view.
     * @param string $view
     * @return array
     */
    public function getPageHandles($view)
    {
        if (!$handles = $this->cache->get(Cache::LAYOUT_CACHE_KEY, self::PAGE_HANDLES_CACHE_TAG_PREFIX . $view)) {
            $pattern = sprintf(self::LAYOUT_XML_PATH_PATTERN, $view, '*_*_*');
            $handles = [];

            foreach (glob(APP_DIR . $pattern) as $collectedHandle) {
                $handles[] = basename($collectedHandle, '.xml');
            }
            $handles = array_unique($handles);

            $this->cache->save(Cache::LAYOUT_CACHE_KEY, self::PAGE_HANDLES_CACHE_TAG_PREFIX . $view, $handles);
        }

        return $handles;
    }

    /**
     * Parse page layout node.
     * @param \SimpleXMLElement $pageNode
     * @return array
     */
    private function parse($pageNode)
    {
        $parsedNode = [];

        foreach ($pageNode->children() as $rootNode) {
            if ($rootNode->getName() === 'head') {
                $parsedNode['head'] = $this->parseHeadNode($rootNode);
            }

            if ($rootNode->getName() === 'body') {
                $parsedNode['body'] = $this->parseBodyNode($rootNode);
            }
        }

        return $parsedNode;
    }

    /**
     * Parse head part of XML layout node.
     * @param \SimpleXMLElement $headNode
     * @return array
     */
    private function parseHeadNode($headNode)
    {
        $parsedHeadNode = [];

        //@TODO: Implement 'async' loading option
        foreach ($headNode->children() as $child) {
            switch ($childName = $child->getName()) {
                case 'title':
                case 'description':
                case 'keywords':
                    $parsedHeadNode[$childName] = (string) $child;
                    break;
                    //@TODO: move above attributes to Head block, they should not be defined in XML
                case 'favicon':
                    $parsedHeadNode[$childName] = XmlParsingHelper::getNodeAttribute($child, 'src');
                    break;
                case 'lib':
                case 'script':
                case 'css':
                    $this->collectedAssets[$childName][] = XmlParsingHelper::getNodeAttribute($child, 'src');
                    break;
                case 'remove':
                    $this->assetsToRemove[] = XmlParsingHelper::getNodeAttribute($child, 'src');
                    break;
            }
        }

        return $parsedHeadNode;
    }

    /**
     * Parse body part of XML layout node.
     * @param \SimpleXMLElement $bodyNode
     * @return array
     */
    private function parseBodyNode($bodyNode)
    {
        $parsedBodyNode = [
            'children' => []
        ];

        foreach ($bodyNode->children() as $bodyItem) {
            if ($parsedItem = $this->parseBodyItem($bodyItem)) {
                $parsedBodyNode['children'][XmlParsingHelper::getNodeAttribute($bodyItem)] = $parsedItem;
            }
        }

        return $parsedBodyNode;
    }

    /**
     * Parse block, container or reference items.
     * @param \SimpleXMLElement $itemNode
     * @return array
     */
    private function parseBodyItem($itemNode)
    {
        $parsedItemNode = [];

        switch ($itemNode->getName()) {
            case 'block':
                $parsedItemNode = [
                    'name' => XmlParsingHelper::getNodeAttribute($itemNode),
                    'class' => XmlParsingHelper::getNodeAttribute($itemNode, 'class'),
                    'template' => XmlParsingHelper::getNodeAttribute($itemNode, 'template') ?: null,
                    'children' => []
                ];

                if ($sortOrder = XmlParsingHelper::getNodeAttribute($itemNode, 'sortOrder')) {
                    $parsedItemNode['sortOrder'] = $sortOrder;
                }

                foreach ($itemNode->children() as $child) {
                    $parsedItemNode['children'][XmlParsingHelper::getNodeAttribute($child)] = $this->parseBodyItem($child);
                }
                break;
            case 'container':
                $parsedItemNode = [
                    'name' => XmlParsingHelper::getNodeAttribute($itemNode),
                    'class' => (XmlParsingHelper::getNodeAttribute($itemNode, 'class')) ?: Container::class,
                    'template' => XmlParsingHelper::getNodeAttribute($itemNode, 'template') ?: null,
                    'children' => [],
                    'containerData' => []
                ];

                if ($htmlTag = XmlParsingHelper::getNodeAttribute($itemNode, 'htmlTag')) {
                    $parsedItemNode['containerData'] = [
                        'htmlTag' => $htmlTag,
                        'htmlClass' => XmlParsingHelper::getNodeAttribute($itemNode, 'htmlClass'),
                        'htmlId' => XmlParsingHelper::getNodeAttribute($itemNode, 'htmlId')
                    ];
                }

                if ($sortOrder = XmlParsingHelper::getNodeAttribute($itemNode, 'sortOrder')) {
                    $parsedItemNode['sortOrder'] = $sortOrder;
                }

                foreach ($itemNode->children() as $child) {
                    $parsedItemNode['children'][XmlParsingHelper::getNodeAttribute($child)] = $this->parseBodyItem($child);
                }
                break;
            case 'referenceBlock':
            case 'referenceContainer':
                if (XmlParsingHelper::stringBooleanCheck(XmlParsingHelper::getNodeAttribute($itemNode, 'remove'))) {
                    $this->referencesToRemove[] = XmlParsingHelper::getNodeAttribute($itemNode);
                } else {
                    $reference = [
                        'children' => []
                    ];

                    if ($sortOrder = XmlParsingHelper::getNodeAttribute($itemNode, 'sortOrder')) {
                        $reference['sortOrder'] = $sortOrder;
                    }

                    foreach ($itemNode->children() as $child) {
                        $reference['children'][XmlParsingHelper::getNodeAttribute($child)] = $this->parseBodyItem($child);
                    }
                    $this->references[] = [
                        'name' => XmlParsingHelper::getNodeAttribute($itemNode),
                        'data' => $reference
                    ];
                }
                break;
        }

        return $parsedItemNode;
    }

    /**
     * Filter collected assets according to remove references.
     */
    private function filterRemovedAssets()
    {
        foreach ($this->assetsToRemove as $assetToRemove) {
            foreach ($this->collectedAssets as $assetsType => $assets) {
                if (($index = array_search($assetToRemove, $assets)) !== false) {
                    unset($this->collectedAssets[$assetsType][$index]);
                }
            }
        }
    }

    /**
     * Apply reference updates to a parsed layout.
     * @param array $bodyStructure
     */
    private function applyReferences(&$bodyStructure)
    {
        foreach ($this->references as $reference) {
            $referenceName = $reference['name'];
            $referenceData = $reference['data'];

            DataHelper::arrayReplaceByKeyRecursive($bodyStructure, $referenceName, $referenceData);
        }

        foreach ($this->referencesToRemove as $referenceToRemove) {
            DataHelper::arrayRemoveByKeyRecursive($bodyStructure, $referenceToRemove);
        }
    }
}
