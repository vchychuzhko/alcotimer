<?php

namespace Awesome\Framework\Block;

use Awesome\Framework\App\Http;

class Template
{
    /**
     * @var \Awesome\Cache\Model\StaticContent $staticContent
     */
    protected $staticContent;

    /**
     * @var \Awesome\Framework\Model\Config $config
     */
    protected $config;

    /**
     * @var string $view
     */
    protected $view;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $template
     */
    protected $template;

    /**
     * @var array $children
     */
    protected $children = [];

    /**
     * @var string $mediaUrl
     */
    protected $mediaUrl;

    /**
     * @var string $staticUrl
     */
    protected $staticUrl;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->staticContent = new \Awesome\Cache\Model\StaticContent();
        $this->config = new \Awesome\Framework\Model\Config();
    }

    /**
     * Render block's template.
     * @return string
     */
    public function toHtml()
    {
        ob_start();
        include($this->resolveTemplatePath());

        return ob_get_clean();
    }

    /**
     * Render a child block.
     * Return all children if no blockName is specified.
     * @param string $blockName
     * @return string
     */
    public function getChildHtml($blockName = '')
    {
        $childHtml = '';

        if ($blockName) {
            if ($block = $this->children[$blockName] ?? []) {
                $childHtml = $this->renderBlock($block);
            }
        } else {
            foreach ($this->children as $child) {
                $childHtml .= $this->renderBlock($child);
            }
        }

        return $childHtml;
    }

    /**
     * Parse and render a block.
     * @param array $block
     * @return string
     */
    private function renderBlock($block)
    {
        //@TODO: move this template preparation process to some kind of renderer
        $className = $block['class'];
        /** @var \Awesome\Framework\Block\Template $templateClass */
        $templateClass = new $className();
        $name = $block['name'];
        $template = $block['template'] ?: $templateClass->getTemplate();
        $children = $block['children'];


        if ($containerTagData = $block['containerData'] ?? []) {
            $templateClass->setContainerTagData($containerTagData);
        }

        $templateClass->setView($this->view)
            ->setNameInLayout($name)
            ->setTemplate($template)
            ->setChildren($children);

        return $templateClass->toHtml();
    }

    /**
     * Set block's template.
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get block's template.
     * @return string
     */
    public function getTemplate()
    {
        return $this->template ?: '';
    }

    /**
     * Set current page view.
     * @param string $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Return URI path to for file in the media folder.
     * If file is not specified, return media folder URI path.
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file = '')
    {
        if ($this->mediaUrl === null) {
            $this->mediaUrl = $this->getPubUrl('/media/');
        }

        return $this->mediaUrl . $file;
    }

    /**
     * Return URI path for file in the static folder.
     * If file is not specified, return static folder URI path.
     * @param string $file
     * @return string
     */
    public function getStaticUrl($file = '')
    {
        if ($this->staticUrl === null) {
            if (!$deployedVersion = $this->staticContent->getDeployedVersion()) {
                $deployedVersion = $this->staticContent->deploy()
                    ->getDeployedVersion();
            }

            $this->staticUrl = $this->getPubUrl('/static/version' . $deployedVersion . '/');
        }

        return $this->staticUrl . $file;
    }

    /**
     * Return URI path for file in the pub folder.
     * If file is not specified, return pub folder URI path.
     * @param string $file
     * @return string
     */
    protected function getPubUrl($file = '')
    {
        return ($this->config->get(Http::WEB_ROOT_CONFIG) ? '' : '/pub') . $file;
    }

    /**
     * Set block children elements.
     * @param array $children
     * @return $this
     */
    protected function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Set block name.
     * @param string $name
     * @return $this
     */
    protected function setNameInLayout($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get current block name.
     * @return string
     */
    public function getNameInLayout()
    {
        return $this->name ?: '';
    }

    /**
     * Parse template XML path to a valid filesystem path.
     * @return string
     */
    protected function resolveTemplatePath()
    {
        @list($module, $file) = explode('::', $this->template);
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
     * Converts snake_case string to camelCase.
     * @param string $string
     * @param string $separator
     * @return string
     */
    protected function camelCase($string, $separator = '_')
    {
        return str_replace($separator, '', lcfirst(ucwords($string, $separator)));
    }
}
