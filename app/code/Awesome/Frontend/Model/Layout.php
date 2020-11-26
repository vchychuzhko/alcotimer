<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Model\FileManager\PhpFileManager;
use Awesome\Framework\Model\Http;
use Awesome\Frontend\Model\BlockInterface;
use Awesome\Frontend\Model\XmlParser\LayoutXmlParser;

class Layout
{
    /**
     * @var LayoutXmlParser $layoutXmlParser
     */
    private $layoutXmlParser;

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var PhpFileManager $phpFileManager
     */
    private $phpFileManager;

    /**
     * @var BlockFactory $templateFactory
     */
    private $templateFactory;

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
     * @param LayoutXmlParser $layoutXmlParser
     * @param Cache $cache
     * @param PhpFileManager $phpFileManager
     * @param BlockFactory $templateFactory
     */
    public function __construct(
        LayoutXmlParser $layoutXmlParser,
        Cache $cache,
        PhpFileManager $phpFileManager,
        BlockFactory $templateFactory
    ) {
        $this->layoutXmlParser = $layoutXmlParser;
        $this->cache = $cache;
        $this->phpFileManager = $phpFileManager;
        $this->templateFactory = $templateFactory;
    }

    /**
     * Initialize page layout.
     * @param string $handle
     * @param string $view
     * @param array $handles
     * @return $this
     * @throws \Exception
     */
    public function init(string $handle, string $view, array $handles = []): self
    {
        $this->handle = $handle;
        $this->view = $view;
        $this->handles = $handles ?: [$handle];
        $this->structure = $this->cache->get(Cache::LAYOUT_CACHE_KEY, $handle, function () use ($handle, $view, $handles) {
            return $this->layoutXmlParser->getLayoutStructure($handle, $view, $handles);
        });

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

        if ($elementData = DataHelper::arrayGetByKeyRecursive($this->structure, $nameInLayout)) {
            $elementId = $elementData['class'];

            /** @var BlockInterface $templateClass */
            $element = $this->templateFactory->create($elementId, [
                'data' => $elementData['data'] ?? []
            ]);
            $element->init($this, $nameInLayout, $elementData['template']);

            $html = $element->toHtml();
        }

        return $html;
    }

    /**
     * Get element's child names.
     * @param string $nameInLayout
     * @return array
     */
    public function getChildNames(string $nameInLayout): array
    {
        $childNames = [];

        if ($elementData = DataHelper::arrayGetByKeyRecursive($this->structure, $nameInLayout)) {
            $childNames = array_keys($elementData['children']);
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
        ob_start();

        try {
            $this->phpFileManager->includeFile($templateFile, false, ['block' => $element]);
        } catch (\Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Convert template XML path to a valid filesystem path.
     * @param string $template
     * @return string
     * @throws \LogicException
     */
    private function getTemplateFile(string $template): string
    {
        @list($module, $file) = explode('::', $template);
        $path = $module;

        if (isset($file)) {
            $path = '/' . str_replace('_', '/', $module) . '/view/' . $this->view . '/templates/' . $file;

            if (!file_exists(APP_DIR . $path)) {
                $path = preg_replace('/(\/view\/)(\w+)(\/)/', '$1' . Http::BASE_VIEW . '$3', $path);
            }
        }
        $path = APP_DIR . $path;

        if (!file_exists($path)) {
            throw new \LogicException(
                sprintf('Template file "%s" was not found', $template)
            );
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
     * Get all handles assigned for current page.
     * @return array
     */
    public function getHandles(): array
    {
        return $this->handles;
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
