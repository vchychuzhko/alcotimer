<?php

namespace Awesome\Frontend\Model\XmlParser;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Helper\XmlParsingHelper;
use Awesome\Frontend\Block\Html\Head;
use Awesome\Frontend\Block\Root;
use Awesome\Frontend\Block\Template\Container;

class LayoutXmlParser
{
    private const DEFAULT_HANDLE_NAME = 'default';
    private const LAYOUT_XML_PATH_PATTERN = '/*/*/view/%s/layout/%s.xml';
    private const PAGE_HANDLES_CACHE_TAG_PREFIX = 'page-handles_';

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var array $processedElements
     */
    private $processedElements = [];

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
            $pattern = sprintf(
                self::LAYOUT_XML_PATH_PATTERN,
                '{' . Http::BASE_VIEW . ',' . $view . '}',
                '{' . self::DEFAULT_HANDLE_NAME . ',' . $handle . '}'
            );
            $head = [];
            $body = [];

            foreach (glob(APP_DIR . $pattern, GLOB_BRACE) as $layoutXmlFile) {
                $layoutData = simplexml_load_file($layoutXmlFile);

                foreach ($layoutData->children() as $rootNode) {
                    if ($rootNode->getName() === 'head') {
                        $head = array_replace_recursive($head, $this->parseHeadNode($rootNode));
                    }

                    if ($rootNode->getName() === 'body') {
                        $body = array_replace_recursive($body, $this->parseBodyNode($rootNode));
                    }
                }
            }

            // @TODO: Add check for minify/merge enabled and replace links
            $this->filterRemovedAssets();
            $head = [
                'name' => 'head',
                'class' => Head::class,
                'template' => null,
                'children' => [],
                'data' => array_merge($head, $this->collectedAssets)
            ];

            $this->applyReferences($body);
            XmlParsingHelper::applySortOrder($body);

            $layoutStructure = [
                'root' => [
                    'name' => 'root',
                    'class' => Root::class,
                    'template' => null,
                    'children' => array_merge(['head' => $head], $body)
                ]
            ];

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
     * Parse head part of XML layout node.
     * @param \SimpleXMLElement $headNode
     * @return array
     */
    private function parseHeadNode($headNode)
    {
        $data = [];

        //@TODO: Implement 'async' loading option
        foreach ($headNode->children() as $child) {
            switch ($childName = $child->getName()) {
                case 'title':
                case 'description':
                case 'keywords':
                    $data[$childName] = (string) $child;
                    break;
                    //@TODO: Move above attributes to page-related config, they should not be defined in XML
                case 'favicon':
                    $data[$childName] = XmlParsingHelper::getNodeAttribute($child, 'src');
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

        return $data;
    }

    /**
     * Parse body part of XML layout node.
     * @param \SimpleXMLElement $bodyNode
     * @return array
     */
    private function parseBodyNode($bodyNode)
    {
        $body = [];

        foreach ($bodyNode->children() as $bodyItem) {
            if ($parsedItem = $this->parseBodyItem($bodyItem)) {
                $body[XmlParsingHelper::getNodeAttribute($bodyItem)] = $parsedItem;
            }
        }

        return $body;
    }

    /**
     * Parse block, container or reference items.
     * @param \SimpleXMLElement $itemNode
     * @return array
     * @throws \LogicException
     */
    private function parseBodyItem($itemNode)
    {
        $parsedItemNode = [];
        $itemName = XmlParsingHelper::getNodeAttribute($itemNode);

        switch ($itemNode->getName()) {
            case 'block':
                $parsedItemNode = [
                    'name' => $itemName,
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

                if (in_array($itemName, $this->processedElements)) {
                    throw new \LogicException(sprintf('"%s" element is declared twice', $itemName));
                }
                $this->processedElements[] = $itemName;
                break;
            case 'container':
                $parsedItemNode = [
                    'name' => $itemName,
                    'class' => Container::class,
                    'template' => null,
                    'children' => []
                ];

                if ($htmlTag = XmlParsingHelper::getNodeAttribute($itemNode, 'htmlTag')) {
                    $parsedItemNode['data'] = [
                        'html_tag' => $htmlTag,
                        'html_class' => XmlParsingHelper::getNodeAttribute($itemNode, 'htmlClass'),
                        'html_id' => XmlParsingHelper::getNodeAttribute($itemNode, 'htmlId')
                    ];
                }

                if ($sortOrder = XmlParsingHelper::getNodeAttribute($itemNode, 'sortOrder')) {
                    $parsedItemNode['sortOrder'] = $sortOrder;
                }

                foreach ($itemNode->children() as $child) {
                    $parsedItemNode['children'][XmlParsingHelper::getNodeAttribute($child)] = $this->parseBodyItem($child);
                }

                if (in_array($itemName, $this->processedElements)) {
                    throw new \LogicException(sprintf('"%s" element is declared twice', $itemName));
                }
                $this->processedElements[] = $itemName;
                break;
            case 'referenceBlock':
            case 'referenceContainer':
                if (XmlParsingHelper::stringBooleanCheck(XmlParsingHelper::getNodeAttribute($itemNode, 'remove'))) {
                    $this->referencesToRemove[] = $itemName;
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
                        'name' => $itemName,
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
            DataHelper::arrayReplaceByKeyRecursive($bodyStructure, $reference['name'], $reference['data']);
        }

        foreach ($this->referencesToRemove as $referenceToRemove) {
            DataHelper::arrayRemoveByKeyRecursive($bodyStructure, $referenceToRemove);
        }
    }
}
