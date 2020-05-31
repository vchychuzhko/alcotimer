<?php

namespace Awesome\Frontend\Model;

use Awesome\Framework\Helper\DataHelper;
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
     * TemplateRenderer constructor.
     * @param string $handle
     * @param string $view
     * @param array $structure
     * @param array $handles
     */
    public function __construct($handle, $view, $structure, $handles = [])
    {
        $this->handle = $handle;
        $this->handles = $handles ?: [$handle];
        $this->view = $view;
        $this->structure = $structure;
    }

    /**
     * Parse and render an element by name.
     * Return empty string in case element is not found.
     * @param string $nameInLayout
     * @return string
     */
    public function render($nameInLayout)
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
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function renderElement($element)
    {
        $template = $element->getTemplate();
        $fileName = $this->getTemplateFileName($template);

        if (!file_exists($fileName)) {
            throw new \LogicException(
                sprintf('Template "%s" is not found for "%s" element', $template, $element->getNameInLayout())
            );
        }
        ob_start();

        try {
            extract(['block' => $element]);
            include $fileName;
        } catch (\LogicException | \RuntimeException $e) {
            ob_end_clean();

            throw $e;
        }

        return ob_get_clean();
    }

    /**
     * Parse template XML path to a valid filesystem path.
     * @param string $template
     * @return string
     */
    private function getTemplateFileName($template)
    {
        @list($module, $file) = explode('::', $template);
        $path = $module;

        if (isset($file)) {
            $module = str_replace('_', '/', $module);
            $path = '/' . $module . '/view/' . $this->view . '/templates/' . $file;

            if (!file_exists(APP_DIR . $path)) {
                $path = str_replace('/view/' . $this->view, '/view/' . Http::BASE_VIEW, $path);
            }
        }

        return APP_DIR . $path;
    }

    /**
     * Get main page handle.
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Get all handles assigned for current page.
     * @return array
     */
    public function getHandles()
    {
        return $this->handles;
    }

    /**
     * Get current page view.
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }
}
