<?php

namespace Awesome\Frontend\Model\XmlParser;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Http;
use Awesome\Frontend\Block\Template\Container;

class Layout extends \Awesome\Framework\Model\XmlParser\AbstractXmlParser
{
    private const DEFAULT_LAYOUT_XML_PATH_PATTERN = '/*/*/view/%s/layout/default.xml';
    private const LAYOUT_XML_PATH_PATTERN = '/*/*/view/%s/layout/%s.xml';
    private const LAYOUT_HANDLES_CACHE_TAG = 'layout-handles';

    /**
     * @var string $view
     */
    private $view;

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
     * @inheritDoc
     */
    public function get($handle)
    {
        if (!$layoutStructure = $this->cache->get(Cache::LAYOUT_CACHE_KEY, $handle)) {
            $layoutStructure = [];
            $defaultPattern = sprintf(self::DEFAULT_LAYOUT_XML_PATH_PATTERN, '{' . Http::BASE_VIEW . ',' . $this->view . '}');

            foreach (glob(APP_DIR . $defaultPattern, GLOB_BRACE) as $defaultXmlFile) {
                $layoutData = simplexml_load_file($defaultXmlFile);

                $parsedData = $this->parse($layoutData);
                $layoutStructure = array_replace_recursive($layoutStructure, $parsedData);
            }
            $pattern = sprintf(self::LAYOUT_XML_PATH_PATTERN, '{' . Http::BASE_VIEW . ',' . $this->view . '}', $handle);

            foreach (glob(APP_DIR . $pattern, GLOB_BRACE) as $layoutXmlFile) {
                $layoutData = simplexml_load_file($layoutXmlFile);

                $parsedData = $this->parse($layoutData);
                $layoutStructure = array_replace_recursive($layoutStructure, $parsedData);
            }

            // @TODO: add check if no layout data is found
            $this->filterRemovedAssets();
            //@TODO: Add check for minify/merge enabled and replace links
            $layoutStructure['head'] = array_merge($layoutStructure['head'], $this->collectedAssets);

            $this->applyReferences($layoutStructure['body']);
            $this->applySortOrder($layoutStructure['body']);
            //@TODO: add validation for duplicating elements

            $this->cache->save(Cache::LAYOUT_CACHE_KEY, $handle, $layoutStructure);
        }

        return $layoutStructure;
    }

    /**
     * Get all available page layout handles for a specified view.
     * @param string $view
     * @return array
     */
    public function getHandlesForView($view)
    {
        $handles = $this->getHandles();

        return $handles[$view] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getHandles()
    {
        if (!$handles = $this->cache->get(Cache::LAYOUT_CACHE_KEY, self::LAYOUT_HANDLES_CACHE_TAG)) {
            foreach ([Http::FRONTEND_VIEW, Http::BACKEND_VIEW] as $view) {
                $pattern = sprintf(self::LAYOUT_XML_PATH_PATTERN, $view, '*_*_*');
                $collectedHandles = [];

                foreach (glob(APP_DIR . $pattern) as $collectedHandle) {
                    $collectedHandles[] = basename($collectedHandle, '.xml');
                }

                $handles[$view] = array_unique($collectedHandles);
            }

            $this->cache->save(Cache::LAYOUT_CACHE_KEY, self::LAYOUT_HANDLES_CACHE_TAG, $handles);
        }

        return $handles;
    }

    /**
     * @inheritDoc
     */
    protected function parse($node)
    {
        $parsedNode = [];

        foreach ($node->children() as $rootNode) {
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
     * Set current page layout view.
     * @param string $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
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
                    $parsedHeadNode[$childName] = $this->getNodeAttribute($child, 'src');
                    break;
                case 'lib':
                case 'script':
                case 'css':
                    $this->collectedAssets[$childName][] = $this->getNodeAttribute($child, 'src');
                    break;
                case 'remove':
                    $this->assetsToRemove[] = $this->getNodeAttribute($child, 'src');
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
                $parsedBodyNode['children'][$this->getNodeAttribute($bodyItem)] = $parsedItem;
            }
        }

        return $parsedBodyNode;
    }

    /**
     * Resolve block, container or reference items.
     * @param \SimpleXMLElement $itemNode
     * @return array
     */
    private function parseBodyItem($itemNode)
    {
        $parsedItemNode = [];

        switch ($itemNode->getName()) {
            case 'block':
                $parsedItemNode = [
                    'name' => $this->getNodeAttribute($itemNode),
                    'class' => $this->getNodeAttribute($itemNode, 'class'),
                    'template' => $this->getNodeAttribute($itemNode, 'template'),
                    'children' => []
                ];

                if ($sortOrder = $this->getNodeAttribute($itemNode, 'sortOrder')) {
                    $parsedItemNode['sortOrder'] = $sortOrder;
                }

                foreach ($itemNode->children() as $child) {
                    $parsedItemNode['children'][$this->getNodeAttribute($child)] = $this->parseBodyItem($child);
                }
                break;
            case 'container':
                $parsedItemNode = [
                    'name' => $this->getNodeAttribute($itemNode),
                    'class' => ($this->getNodeAttribute($itemNode, 'class')) ?: Container::class,
                    'template' => $this->getNodeAttribute($itemNode, 'template'),
                    'children' => [],
                    'containerData' => []
                ];

                if ($htmlTag = $this->getNodeAttribute($itemNode, 'htmlTag')) {
                    $parsedItemNode['containerData'] = [
                        'htmlTag' => $htmlTag,
                        'htmlClass' => $this->getNodeAttribute($itemNode, 'htmlClass'),
                        'htmlId' => $this->getNodeAttribute($itemNode, 'htmlId')
                    ];
                }

                if ($sortOrder = $this->getNodeAttribute($itemNode, 'sortOrder')) {
                    $parsedItemNode['sortOrder'] = $sortOrder;
                }

                foreach ($itemNode->children() as $child) {
                    $parsedItemNode['children'][$this->getNodeAttribute($child)] = $this->parseBodyItem($child);
                }
                break;
            case 'referenceBlock':
            case 'referenceContainer':
                if ($this->stringBooleanCheck($this->getNodeAttribute($itemNode, 'remove'))) {
                    $this->referencesToRemove[] = $this->getNodeAttribute($itemNode);
                } else {
                    $reference = [
                        'children' => []
                    ];

                    if ($sortOrder = $this->getNodeAttribute($itemNode, 'sortOrder')) {
                        $reference['sortOrder'] = $sortOrder;
                    }

                    foreach ($itemNode->children() as $child) {
                        $reference['children'][$this->getNodeAttribute($child)] = $this->parseBodyItem($child);
                    }
                    $this->references[] = [
                        'name' => $this->getNodeAttribute($itemNode),
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

            array_update_by_key_recursive($bodyStructure, $referenceName, $referenceData);
        }

        foreach ($this->referencesToRemove as $referenceToRemove) {
            array_remove_by_key_recursive($bodyStructure, $referenceToRemove);
        }

        return $bodyStructure;
    }

    /**
     * Apply sort order rules to a parsed layout.
     * @param array $blockStructure
     */
    private function applySortOrder(&$blockStructure)
    {
        if ($children = $blockStructure['children'] ?? []) {
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
                $this->applySortOrder($blockStructure['children'][$childName]);
            }
        }
    }
}
