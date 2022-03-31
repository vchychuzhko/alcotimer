<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\XmlParser;

use Awesome\Framework\Exception\XmlValidationException;
use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Helper\XmlParsingHelper;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\FileManager\XmlFileManager;
use Awesome\Frontend\Block\Container;
use Awesome\Frontend\Block\Html\Body;
use Awesome\Frontend\Block\Html\Head;
use Awesome\Frontend\Block\Root;

class LayoutXmlParser extends \Awesome\Framework\Model\AbstractXmlParser
{
    private const DEFAULT_HANDLE_NAME = 'default';
    private const LAYOUT_XML_PATH_PATTERN = '/*/*/view/%s/layout/%s.xml';
    private const LAYOUT_XSD_SCHEMA_PATH = '/Awesome/Frontend/Schema/page_layout.xsd';

    private Config $config;

    private array $assetsToRemove = [];

    private array $processedElements = [];

    private array $references = [];

    private array $referencesToRemove = [];

    /**
     * LayoutXmlParser constructor.
     * @param Config $config
     * @param XmlFileManager $xmlFileManager
     */
    public function __construct(
        Config $config,
        XmlFileManager $xmlFileManager
    ) {
        parent::__construct($xmlFileManager);
        $this->config = $config;
    }

    /**
     * Get layout structure for requested handle for a specified view.
     * @param string $handle
     * @param string $view
     * @return array
     * @throws \Exception
     */
    public function getLayoutStructure(string $handle, string $view): array
    {
        $pattern = APP_DIR . sprintf(
            self::LAYOUT_XML_PATH_PATTERN,
            $view,
            '{' . self::DEFAULT_HANDLE_NAME . ',' . $handle . '}'
        );
        $head = [];
        $body = [];

        foreach (glob($pattern, GLOB_BRACE) as $layoutXmlFile) {
            $layoutData = $this->xmlFileManager->parseXmlFile($layoutXmlFile, APP_DIR . self::LAYOUT_XSD_SCHEMA_PATH);

            if ($headNode = XmlParsingHelper::getChildNode($layoutData, 'head')) {
                $head = $this->parseHeadNode($headNode, $head);
            }
            if ($bodyNode = XmlParsingHelper::getChildNode($layoutData, 'body')) {
                $body = $this->parseBodyNode($bodyNode, $body);
            }
        }

        $this->filterRemovedAssets($head);
        XmlParsingHelper::applySortOrder($head);

        $this->applyReferences($body);
        XmlParsingHelper::applySortOrder($body);

        return [
            'root' => [
                'name'     => 'root',
                'class'    => Root::class,
                'children' => [
                    'head' => [
                        'name'  => 'head',
                        'class' => Head::class,
                        'data'  => $head,
                    ],
                    'body' => [
                        'name'     => 'body',
                        'class'    => Body::class,
                        'children' => $body,
                    ],
                ],
            ]
        ];
    }

    /**
     * Parse head part of XML layout node.
     * @param \SimpleXMLElement $headNode
     * @param array $headData
     * @return array
     * @throws XmlValidationException
     */
    private function parseHeadNode(\SimpleXMLElement $headNode, array $headData = []): array
    {
        foreach ($headNode->children() as $child) {
            switch ($child->getName()) {
                case 'favicon':
                    $headData['favicon'] = [];

                    foreach ($child->children() as $icon) {
                        $headData['favicon'][] = [
                            'rel'   => XmlParsingHelper::getNodeAttribute($icon, 'rel') ?: 'icon',
                            'href'  => XmlParsingHelper::getNodeAttribute($icon, 'href'),
                            'type'  => XmlParsingHelper::getNodeAttribute($icon, 'type'),
                            'sizes' => XmlParsingHelper::getNodeAttribute($icon, 'sizes'),
                        ];
                    }
                    break;
                case 'manifest':
                    $headData['manifest'] = [
                        'href'       => XmlParsingHelper::getNodeAttribute($child, 'href'),
                        'themeColor' => XmlParsingHelper::getNodeAttribute($child, 'themeColor'),
                    ];
                    break;
                case 'script':
                    $headData['scripts'] = $headData['scripts'] ?? [];
                    $src = XmlParsingHelper::getNodeAttribute($child, 'src');

                    if (isset($headData['scripts'][$src])) {
                        throw new XmlValidationException(__('Script file with "%1" src is already defined', $src));
                    }

                    $headData['scripts'][$src] = [
                        'async'     => XmlParsingHelper::isAttributeBooleanTrue($child, 'async'),
                        'defer'     => XmlParsingHelper::isAttributeBooleanTrue($child, 'defer'),
                        'sortOrder' => XmlParsingHelper::getNodeAttribute($child, 'sortOrder'),
                    ];
                    break;
                case 'css':
                    $headData['styles'] = $headData['styles'] ?? [];
                    $src = XmlParsingHelper::getNodeAttribute($child, 'src');

                    if (isset($headData['styles'][$src])) {
                        throw new XmlValidationException(__('Style file with "%1" src is already defined', $src));
                    }

                    $headData['styles'][$src] = [
                        'media'     => XmlParsingHelper::getNodeAttribute($child, 'media'),
                        'sortOrder' => XmlParsingHelper::getNodeAttribute($child, 'sortOrder'),
                    ];
                    break;
                case 'preload':
                    $headData['preloads'] = $headData['preloads'] ?? [];
                    $src = XmlParsingHelper::getNodeAttribute($child, 'src');

                    if (isset($headData['preloads'][$src])) {
                        throw new XmlValidationException(__('Preload directive with "%1" src is already defined', $src));
                    }

                    $headData['preloads'][$src] = [
                        'as'        => XmlParsingHelper::getNodeAttribute($child, 'as'),
                        'type'      => XmlParsingHelper::getNodeAttribute($child, 'type'),
                        'sortOrder' => XmlParsingHelper::getNodeAttribute($child, 'sortOrder'),
                    ];
                    break;
                case 'remove':
                    $this->assetsToRemove[] = XmlParsingHelper::getNodeAttribute($child, 'src');
                    break;
            }
        }

        return $headData;
    }

    /**
     * Parse body part of XML layout node.
     * @param \SimpleXMLElement $bodyNode
     * @param array $body
     * @return array
     * @throws XmlValidationException
     */
    private function parseBodyNode(\SimpleXMLElement $bodyNode, array $body = []): array
    {
        foreach ($bodyNode->children() as $element) {
            $name = XmlParsingHelper::getNodeAttributeName($element);

            if ($parsedElement = $this->parseElement($element)) {
                $body[$name] = array_replace_recursive($body[$name] ?? [], $parsedElement);
            }
        }

        return $body;
    }

    /**
     * Parse block, container or reference items.
     * @param \SimpleXMLElement $element
     * @return array|null
     * @throws XmlValidationException
     */
    private function parseElement(\SimpleXMLElement $element): ?array
    {
        $configPath = XmlParsingHelper::getNodeAttribute($element, 'ifConfig');

        if ($configPath && !$this->config->get($configPath)) {
            return null;
        }
        $parsedElement = [];
        $name = XmlParsingHelper::getNodeAttributeName($element);

        switch ($element->getName()) {
            case 'block':
                if (in_array($name, $this->processedElements, true)) {
                    throw new XmlValidationException(__('Block "%1" is already declared', $name));
                }

                $parsedElement = [
                    'name'      => $name,
                    'class'     => XmlParsingHelper::getNodeAttribute($element, 'class'),
                    'template'  => XmlParsingHelper::getNodeAttribute($element, 'template'),
                    'children'  => [],
                    'sortOrder' => XmlParsingHelper::getNodeAttribute($element, 'sortOrder'),
                ];

                if (XmlParsingHelper::isDisabled($element)) {
                    $parsedElement['disabled'] = true;
                }

                foreach ($element->children() as $child) {
                    if ($parsedChild = $this->parseElement($child)) {
                        $parsedElement['children'][XmlParsingHelper::getNodeAttributeName($child)] = $parsedChild;
                    }
                }

                $this->processedElements[] = $name;
                break;
            case 'container':
                if (in_array($name, $this->processedElements, true)) {
                    throw new XmlValidationException(__('Container "%1" is already declared', $name));
                }

                $parsedElement = [
                    'name'      => $name,
                    'class'     => Container::class,
                    'children'  => [],
                    'sortOrder' => XmlParsingHelper::getNodeAttribute($element, 'sortOrder'),
                ];

                if (XmlParsingHelper::isDisabled($element)) {
                    $parsedElement['disabled'] = true;
                }

                if ($htmlTag = XmlParsingHelper::getNodeAttribute($element, 'htmlTag')) {
                    $parsedElement['data'] = [
                        'html_tag'   => $htmlTag,
                        'html_class' => XmlParsingHelper::getNodeAttribute($element, 'htmlClass'),
                        'html_id'    => XmlParsingHelper::getNodeAttribute($element, 'htmlId'),
                    ];
                }

                foreach ($element->children() as $child) {
                    if ($parsedChild = $this->parseElement($child)) {
                        $parsedElement['children'][XmlParsingHelper::getNodeAttributeName($child)] = $parsedChild;
                    }
                }

                $this->processedElements[] = $name;
                break;
            case 'referenceBlock':
            case 'referenceContainer':
                if (XmlParsingHelper::isAttributeBooleanTrue($element, 'remove')) {
                    $this->referencesToRemove[] = $name;
                } else {
                    $reference = [
                        'children' => [],
                    ];

                    if ($sortOrder = XmlParsingHelper::getNodeAttribute($element, 'sortOrder')) {
                        $reference['sortOrder'] = $sortOrder;
                    }

                    foreach ($element->children() as $child) {
                        if ($parsedChild = $this->parseElement($child)) {
                            $reference['children'][XmlParsingHelper::getNodeAttributeName($child)] = $parsedChild;
                        }
                    }
                    $this->references[] = [
                        'name' => $name,
                        'data' => $reference,
                    ];
                }
                break;
        }

        return $parsedElement;
    }

    /**
     * Filter collected assets according to remove references.
     * @param array $headStructure
     */
    private function filterRemovedAssets(array &$headStructure)
    {
        foreach ($this->assetsToRemove as $assetToRemove) {
            foreach ($headStructure as $headField => $unused) {
                if (in_array($headField, ['scripts', 'styles', 'preloads'], true)) {
                    unset($headStructure[$headField][$assetToRemove]);
                }
            }
        }
    }

    /**
     * Apply reference updates to a parsed layout.
     * @param array $bodyStructure
     */
    private function applyReferences(array &$bodyStructure)
    {
        foreach ($this->references as $reference) {
            $bodyStructure = DataHelper::arrayReplaceByKeyRecursive($bodyStructure, $reference['name'], $reference['data']);
        }

        foreach ($this->referencesToRemove as $referenceToRemove) {
            $bodyStructure = DataHelper::arrayRemoveByKeyRecursive($bodyStructure, $referenceToRemove);
        }
    }
}
