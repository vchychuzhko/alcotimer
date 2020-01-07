<?php

namespace Awesome\Framework\Model\XmlParser;

use Awesome\Framework\Model\App;
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
     * @var array $references
     */
    private $references = [];

    /**
     * @var array $referencesToRemove
     */
    private $referencesToRemove = [];

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

            $this->applyReferences($pageStructure['body']);
            $this->applySortOrder($pageStructure['body']);

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
     * @param \SimpleXMLElement $pageNode
     * @return array
     */
    private function parsePageNode($pageNode)
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
                $parsedBodyNode['children'][(string)$bodyItem['name']] = $parsedItem;
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
                    'name' => (string) $itemNode['name'],
                    'class' => (string) $itemNode['class'],
                    'template' => (string) $itemNode['template'],
                    'children' => []
                ];

                if ($sortOrder = (string) $itemNode['sortOrder']) {
                    $parsedItemNode['sortOrder'] = $sortOrder;
                }

                foreach ($itemNode->children() as $child) {
                    $parsedItemNode['children'][(string) $child['name']] = $this->parseBodyItem($child);
                }
                break;
            case 'container':
                $parsedItemNode = [
                    'name' => (string) $itemNode['name'],
                    'class' => ((string) $itemNode['class']) ?: \Awesome\Framework\Block\Template\Container::class,
                    'template' => (string) $itemNode['template'],
                    'children' => []
                ];

                if ($htmlTag = (string) $itemNode['htmlTag']) {
                    $parsedItemNode['containerData'] = [
                        'htmlTag' => $htmlTag,
                        'htmlClass' => (string) $itemNode['htmlClass'],
                        'htmlId' => (string) $itemNode['htmlId']
                    ];
                }

                if ($sortOrder = (string) $itemNode['sortOrder']) {
                    $parsedItemNode['sortOrder'] = $sortOrder;
                }

                foreach ($itemNode->children() as $child) {
                    $parsedItemNode['children'][(string) $child['name']] = $this->parseBodyItem($child);
                }
                break;
            case 'referenceBlock':
            case 'referenceContainer':
                if ($this->stringBooleanCheck((string) $itemNode['remove'])) {
                    $this->referencesToRemove[] = (string) $itemNode['name'];
                } else {
                    $reference = [
                        'children' => []
                    ];

                    if ($sortOrder = (string) $itemNode['sortOrder']) {
                        $reference['sortOrder'] = $sortOrder;
                    }

                    foreach ($itemNode->children() as $child) {
                        $reference['children'][(string) $child['name']] = $this->parseBodyItem($child);
                    }
                    $this->references[] = [
                        'name' => (string) $itemNode['name'],
                        'data' => $reference
                    ];
                }
                break;
        }

        return $parsedItemNode;
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
