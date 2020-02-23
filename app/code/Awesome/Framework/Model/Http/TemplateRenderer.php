<?php

namespace Awesome\Framework\Model\Http;

use Awesome\Framework\App\Http;
use Awesome\Framework\Block\Template;
use Awesome\Framework\Block\Template\Container;

class TemplateRenderer
{
    /**
     * @var string $handle
     */
    private $handle;

    /**
     * @var string $view
     */
    private $view;

    /**
     * @var array $structure
     */
    private $structure;

    /**
     * PageRenderer constructor.
     * @param string $handle
     * @param string $view
     * @param array $structure
     */
    public function __construct($handle, $view, $structure)
    {
        $this->handle = $handle;
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

        if ($element = array_get_by_key_recursive($this->structure, $nameInLayout)) {
            $className = $element['class'];

            /** @var Template $templateClass */
            $templateClass = new $className(
                $this,
                $nameInLayout,
                $element['template'],
                array_keys($element['children'])
            );

            if ($this->isContainer($element) && $element['containerData']) {
                /** @var Container $templateClass */
                $templateClass->setContainerTagData($element['containerData']);
            }

            $html = $templateClass->toHtml();
        }

        return $html;
    }

    /**
     * Parse template XML path to a valid filesystem path.
     * @param string $template
     * @return string
     */
    public function resolveTemplatePath($template)
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
     * Get current page handle.
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Get current page view.
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Check if requested element has container type.
     * @param $element
     * @return bool
     */
    private function isContainer($element)
    {
        return is_a($element['class'], Container::class, true);
    }
}
