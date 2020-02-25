<?php

namespace Awesome\Framework\XmlParser;

use Awesome\Framework\App\Http;
use Awesome\Cache\Model\Cache;
use Awesome\Framework\Block\Template\Container;

class PageXmlParser extends \Awesome\Framework\Model\XmlParser\AbstractXmlParser
{
    private const DEFAULT_PAGE_XML_PATH_PATTERN = '/*/*/view/%v/layout/default.xml';
    private const PAGE_XML_PATH_PATTERN = '/*/*/view/%v/layout/%h.xml';
    private const PAGE_HANDLES_CACHE_TAG = 'page-handles';

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
        if (!$pageStructure = $this->cache->get(Cache::LAYOUT_CACHE_KEY, $handle)) {
            $defaultPattern = APP_DIR . str_replace('%v', $this->view, self::DEFAULT_PAGE_XML_PATH_PATTERN);

            foreach (glob($defaultPattern) as $defaultXmlFile) {
                $pageData = simplexml_load_file($defaultXmlFile);

                $parsedData = $this->parse($pageData);
                $pageStructure = array_replace_recursive($pageStructure, $parsedData);
            }

            $pattern = APP_DIR . str_replace('%v', $this->view, self::PAGE_XML_PATH_PATTERN);
            $pattern = str_replace('%h', $handle, $pattern);

            foreach (glob($pattern) as $pageXmlFile) {
                $pageData = simplexml_load_file($pageXmlFile);

                $parsedData = $this->parse($pageData);
                $pageStructure = array_replace_recursive($pageStructure, $parsedData);
            }

            $this->filterRemovedAssets();
            //@TODO: Add check for minify/merge enabled and replace links
            $pageStructure['head'] = array_merge($pageStructure['head'], $this->collectedAssets);

            $this->applyReferences($pageStructure['body']);
            $this->applySortOrder($pageStructure['body']);
            //@TODO: add validation for duplicating elements

            $this->cache->save(Cache::LAYOUT_CACHE_KEY, $handle, $pageStructure);
        }

        return $pageStructure;
    }

    /**
     * Get all available page handles for a specified view.
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
        if (!$handles = $this->cache->get(Cache::LAYOUT_CACHE_KEY, self::PAGE_HANDLES_CACHE_TAG)) {
            foreach ([Http::FRONTEND_VIEW, Http::BACKEND_VIEW] as $view) {
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
     * Set current page view.
     * @param string $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Parse head part of XML page node.
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
     * Parse body part of XML page node.
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
     * Apply reference updates to a parsed page.
     * @param array $bodyStructure
     * @return array
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
     * Apply sort order rules to a parsed page.
     * @param array $blockStructure
     * @return array
     */
    private function applySortOrder(&$blockStructure)
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
