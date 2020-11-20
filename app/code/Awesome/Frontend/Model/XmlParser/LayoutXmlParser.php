<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\XmlParser;

use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Model\FileManager\XmlFileManager;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Helper\XmlParsingHelper;
use Awesome\Frontend\Block\Html\Head;
use Awesome\Frontend\Block\Root;
use Awesome\Frontend\Block\Template\Container;

class LayoutXmlParser
{
    private const DEFAULT_HANDLE_NAME = 'default';
    private const LAYOUT_XML_PATH_PATTERN = '/*/*/view/%s/layout/%s.xml';

    /**
     * @var XmlFileManager $xmlFileManager
     */
    private $xmlFileManager;

    /**
     * @var array $processedElements
     */
    private $processedElements = [];

    /**
     * @var array $collectedAssets
     */
    private $collectedAssets = [
        'script' => [],
        'css' => [],
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
     * @param XmlFileManager $xmlFileManager
     */
    public function __construct(XmlFileManager $xmlFileManager)
    {
        $this->xmlFileManager = $xmlFileManager;
    }

    /**
     * Get layout structure for requested handle for a specified view.
     * @param string $handle
     * @param string $view
     * @param array $handles
     * @return array
     * @throws \Exception
     */
    public function getLayoutStructure(string $handle, string $view, array $handles = []): array
    {
        $handles = $handles ?: [$handle];
        $pattern = sprintf(
            self::LAYOUT_XML_PATH_PATTERN,
            '{' . Http::BASE_VIEW . ',' . $view . '}',
            '{' . self::DEFAULT_HANDLE_NAME . ',' . implode(',', $handles) . '}'
        );
        $head = [];
        $body = [];

        foreach (glob(APP_DIR . $pattern, GLOB_BRACE) as $layoutXmlFile) {
            $layoutData = $this->xmlFileManager->parseXmlFile($layoutXmlFile);

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
        XmlParsingHelper::applySortOrder($this->collectedAssets);
        $head = [
            'name' => 'head',
            'class' => Head::class,
            'template' => null,
            'children' => [],
            'data' => array_merge($head, $this->collectedAssets),
        ];

        $body = [
            'name' => 'body',
            'class' => Container::class,
            'template' => null,
            'children' => $body,
        ];
        $this->applyReferences($body);
        XmlParsingHelper::applySortOrder($body);

        return [
            'root' => [
                'name' => 'root',
                'class' => Root::class,
                'template' => null,
                'children' => [
                    'head' => $head,
                    'body' => $body
                ],
            ]
        ];
    }

    /**
     * Parse head part of XML layout node.
     * @param \SimpleXMLElement $headNode
     * @return array
     */
    private function parseHeadNode(\SimpleXMLElement $headNode): array
    {
        $data = [];

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
                case 'script':
                    $parsedAsset = [];

                    if ($sortOrder = XmlParsingHelper::getNodeAttribute($child, 'sortOrder')) {
                        $parsedAsset['sortOrder'] = $sortOrder;
                    }
                    if (XmlParsingHelper::isAttributeBooleanTrue($child, 'async')) {
                        $parsedAsset['async'] = true;
                    }
                    if (XmlParsingHelper::isAttributeBooleanTrue($child, 'defer')) {
                        $parsedAsset['defer'] = true;
                    }
                    $this->collectedAssets[$childName][XmlParsingHelper::getNodeAttribute($child, 'src')] = $parsedAsset;
                    break;
                case 'css':
                    $parsedAsset = [];

                    if ($sortOrder = XmlParsingHelper::getNodeAttribute($child, 'sortOrder')) {
                        $parsedAsset['sortOrder'] = $sortOrder;
                    }
                    $this->collectedAssets[$childName][XmlParsingHelper::getNodeAttribute($child, 'src')] = $parsedAsset;
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
    private function parseBodyNode(\SimpleXMLElement $bodyNode): array
    {
        $body = [];

        foreach ($bodyNode->children() as $bodyItem) {
            if ($parsedItem = $this->parseBodyItem($bodyItem)) {
                $body[XmlParsingHelper::getNodeAttributeName($bodyItem)] = $parsedItem;
            }
        }

        return $body;
    }

    /**
     * Parse block, container or reference items.
     * @param \SimpleXMLElement $itemNode
     * @return array
     * @throws XmlValidationException
     */
    private function parseBodyItem(\SimpleXMLElement $itemNode): array
    {
        $parsedItemNode = [];
        $itemName = XmlParsingHelper::getNodeAttributeName($itemNode);

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
                    $parsedItemNode['children'][XmlParsingHelper::getNodeAttributeName($child)] = $this->parseBodyItem($child);
                }

                if (in_array($itemName, $this->processedElements, true)) {
                    throw new XmlValidationException(sprintf('"%s" block is declared twice', $itemName));
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
                    $parsedItemNode['children'][XmlParsingHelper::getNodeAttributeName($child)] = $this->parseBodyItem($child);
                }

                if (in_array($itemName, $this->processedElements, true)) {
                    throw new XmlValidationException(sprintf('"%s" container is declared twice', $itemName));
                }
                $this->processedElements[] = $itemName;
                break;
            case 'referenceBlock':
            case 'referenceContainer':
                if (XmlParsingHelper::isAttributeBooleanTrue($itemNode, 'remove')) {
                    $this->referencesToRemove[] = $itemName;
                } else {
                    $reference = [
                        'children' => []
                    ];

                    if ($sortOrder = XmlParsingHelper::getNodeAttribute($itemNode, 'sortOrder')) {
                        $reference['sortOrder'] = $sortOrder;
                    }

                    foreach ($itemNode->children() as $child) {
                        $reference['children'][XmlParsingHelper::getNodeAttributeName($child)] = $this->parseBodyItem($child);
                    }
                    $this->references[] = [
                        'name' => $itemName,
                        'data' => $reference,
                    ];
                }
                break;
        }

        return $parsedItemNode;
    }

    /**
     * Filter collected assets according to remove references.
     * @return void
     */
    private function filterRemovedAssets(): void
    {
        foreach ($this->assetsToRemove as $assetToRemove) {
            foreach ($this->collectedAssets as $assetType => $unused) {
                unset($this->collectedAssets[$assetType][$assetToRemove]);
            }
        }
    }

    /**
     * Apply reference updates to a parsed layout.
     * @param array $bodyStructure
     * @return void
     */
    private function applyReferences(array &$bodyStructure): void
    {
        foreach ($this->references as $reference) {
            DataHelper::arrayReplaceByKeyRecursive($bodyStructure, $reference['name'], $reference['data']);
        }

        foreach ($this->referencesToRemove as $referenceToRemove) {
            DataHelper::arrayRemoveByKeyRecursive($bodyStructure, $referenceToRemove);
        }
    }
}
