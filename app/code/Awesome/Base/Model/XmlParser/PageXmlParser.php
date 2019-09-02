<?php

namespace Awesome\Base\Model\XmlParser;

class PageXmlParser extends \Awesome\Base\Model\XmlParser //@TODO: add abstract class for parsing, XmlParser cannot be their parent
{
    private const DEFAULT_PAGE_XML_PATH_PATTERN = '/*/*/view/%v/layout/default.xml';
    private const PAGE_XML_PATH_PATTERN = '/*/*/view/%v/layout/%h.xml';
    private const PAGE_CACHE_KEY = 'pages';

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

                $this->cache->save(self::PAGE_CACHE_KEY, $handle, $pageStructure);
            }
        }

        return $pageStructure;
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
                $parsedNode['body'] = $this->parseXmlNode($mainNode)['body']['children'];
            }
        }

        return $parsedNode;
    }

    /**
     *
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
                $this->collectedAssets[$this->assetMap[$childName]][] = $this->parseXmlNode($child)[$childName];
            } else {
                $parsedHeadNode[$childName] = trim((string)$child);
            }
        }
        return $parsedHeadNode;
    }
}
