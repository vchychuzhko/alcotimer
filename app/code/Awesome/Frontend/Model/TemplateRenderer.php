<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Model\FileManager\PhpFileManager;
use Awesome\Framework\Model\Http;
use Awesome\Frontend\Block\Template;

class TemplateRenderer
{
    /**
     * @var string $handle
     */
    private $handle;

    /**
     * @var string $handle
     */
    private $handles;

    /**
     * @var string $view
     */
    private $view;

    /**
     * @var array $structure
     */
    private $structure;

    /**
     * @var PhpFileManager $phpFileManager
     */
    private $phpFileManager;

    /**
     * TemplateRenderer constructor.
     * @param string $handle
     * @param string $view
     * @param array $structure
     * @param array $handles
     */
    public function __construct(string $handle, string $view, array $structure, array $handles = [])
    {
        $this->handle = $handle;
        $this->view = $view;
        $this->structure = $structure;
        $this->handles = $handles ?: [$handle];
        $this->phpFileManager = new PhpFileManager();
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
            $className = $element['class'];

            /** @var Template $templateClass */
            $templateClass = new $className(
                $this,
                $nameInLayout,
                $element['template'],
                array_keys($element['children']),
                $element['data'] ?? []
            );

            $html = $templateClass->toHtml();
        }

        return $html;
    }

    /**
     * Render element template.
     * @param Template $element
     * @return string
     * @throws \Exception
     */
    public function renderElement(Template $element): string
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
     * Get main page handle.
     * @return string
     */
    public function getHandle(): string
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
     * @return string
     */
    public function getView(): string
    {
        return $this->view;
    }
}
