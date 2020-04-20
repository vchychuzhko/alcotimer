<?php

namespace Awesome\Frontend\Model;

use Awesome\Framework\Helper\DataHelper;
use Awesome\Framework\Model\Http;
use Awesome\Frontend\Block\Template;
use Awesome\Frontend\Block\Template\Container;

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

        if ($element = DataHelper::arrayGetByKeyRecursive($this->structure, $nameInLayout)) {
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
     * Render element template.
     * @param Template $element
     * @return string
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
        } catch (\Exception $e) {
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
     * @param array $element
     * @return bool
     */
    private function isContainer($element)
    {
        return is_a($element['class'], Container::class, true);
    }
}
