<?php
declare(strict_types=1);

namespace Vch\Frontend\Model;

use Vch\Cache\Model\Cache;
use Vch\Framework\Exception\FileSystemException;
use Vch\Framework\Helper\DataHelper;
use Vch\Framework\Model\Http;
use Vch\Frontend\Model\AbstractBlock;
use Vch\Frontend\Model\BlockInterface;
use Vch\Frontend\Model\TemplateEngine\Php as TemplateEngine;
use Vch\Frontend\Model\XmlParser\LayoutXmlParser;

class Layout
{
    /**
     * @var BlockFactory $blockFactory
     */
    private $blockFactory;

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var LayoutXmlParser $layoutXmlParser
     */
    private $layoutXmlParser;

    /**
     * @var TemplateEngine $templateEngine
     */
    private $templateEngine;

    /**
     * @var string $handle
     */
    private $handle;

    /**
     * @var string $view
     */
    private $view;

    /**
     * @var array $handles
     */
    private $handles = [];

    /**
     * @var array $structure
     */
    private $structure;

    /**
     * Layout constructor.
     * @param BlockFactory $blockFactory
     * @param Cache $cache
     * @param LayoutXmlParser $layoutXmlParser
     * @param TemplateEngine $templateEngine
     */
    public function __construct(
        BlockFactory $blockFactory,
        Cache $cache,
        LayoutXmlParser $layoutXmlParser,
        TemplateEngine $templateEngine
    ) {
        $this->blockFactory = $blockFactory;
        $this->cache = $cache;
        $this->layoutXmlParser = $layoutXmlParser;
        $this->templateEngine = $templateEngine;
    }

    /**
     * Initialize page layout.
     * @param string $handle
     * @param string $view
     * @return $this
     * @throws \Exception
     */
    public function init(string $handle, string $view): self
    {
        $this->handle = $handle;
        $this->view = $view;
        $this->structure = $this->cache->get(
            Cache::LAYOUT_CACHE_KEY,
            $handle . '_' . $view,
            function () use ($handle, $view) {
                return $this->layoutXmlParser->getLayoutStructure($handle, $view);
            }
        );

        return $this;
    }

    /**
     * Parse and render an element by name.
     * Return empty string in case element is not found.
     * @param string $nameInLayout
     * @return string
     * @throws \Exception
     */
    public function render(string $nameInLayout): string
    {
        $html = '';

        if ($element = DataHelper::arrayGetByKeyRecursive($this->structure, $nameInLayout)) {
            $elementId = $element['class'];
            $params = [
                'layout'       => $this,
                'nameInLayout' => $nameInLayout,
                'template'     => $element['template'] ?? null,
            ];

            if (isset($element['data'])) {
                $params['data'] = $element['data'];
            }

            $block = $this->blockFactory->create($elementId, $params);

            $html = $block->toHtml();
        }

        return $html;
    }

    /**
     * Get element's child names.
     * @param string $nameInLayout
     * @param bool $includeDisabled
     * @return array
     */
    public function getChildNames(string $nameInLayout, bool $includeDisabled = false): array
    {
        $childNames = [];

        if ($element = DataHelper::arrayGetByKeyRecursive($this->structure, $nameInLayout)) {
            $children = $element['children'] ?? [];

            if (!$includeDisabled) {
                $children = array_filter($children, static function ($child) {
                    return ($child['disabled'] ?? false) !== true;
                });
            }

            $childNames = array_keys($children);
        }

        return $childNames;
    }

    /**
     * Render element template.
     * @param BlockInterface $element
     * @return string
     * @throws \Exception
     */
    public function renderElement(BlockInterface $element): string
    {
        $templateFile = $this->getTemplateFile($element->getTemplate());

        return $this->templateEngine->render($element, $templateFile);
    }

    /**
     * Convert template XML path to a valid filesystem path.
     * @param string $template
     * @return string
     * @throws FileSystemException
     */
    private function getTemplateFile(string $template): string
    {
        @list($module, $file) = explode('::', $template);
        $path = $module;

        if (isset($file)) {
            $path = APP_DIR . '/' . str_replace('_', '/', $module) . '/view/' . $this->view . '/templates/' . $file;

            if (!file_exists($path)) {
                $path = preg_replace('/(\/view\/)(\w+)(\/)/', '$1' . Http::BASE_VIEW . '$3', $path);
            }
        } else {
            $path = APP_DIR . $path;
        }

        if (!is_file($path)) {
            throw new FileSystemException(__('Template file "%1" was not found', $template));
        }

        return $path;
    }

    /**
     * Get current page leading handle.
     * @return string|null
     */
    public function getHandle(): ?string
    {
        return $this->handle;
    }

    /**
     * Get current page view.
     * @return string|null
     */
    public function getView(): ?string
    {
        return $this->view;
    }
}
