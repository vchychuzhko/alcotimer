<?php

namespace Awesome\Framework\Block;

use Awesome\Framework\Model\App;

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
     * @var string $template
     */
    protected $template;

    /**
     * @var string $view
     */
    protected $view;

    /**
     * @var array $structure
     */
    protected $structure;

    /**
     * @var string $mediaUrl
     */
    private $mediaUrl;

    /**
     * @var string $staticUrl
     */
    private $staticUrl;

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
    public function toHtml() {
        ob_start();
        include($this->resolveTemplatePath());

        return ob_get_clean();
    }

    /**
     * Get and render child block.
     * Return all children if no block is specified.
     * @param string $blockName
     * @return string
     */
    public function getChildHtml($blockName = '')
    {
        $childHtml = '';

        if ($blockName) {
            if ($block = $this->structure[$blockName] ?? []) {
                $childHtml = $this->renderBlock($block);
            }
        } else {
            foreach ($this->structure as $child) {
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
        $className = $block['class'];
        /** @var \Awesome\Framework\Block\Template $templateClass */
        $templateClass = new $className();
        $template = $block['template'] ?? $templateClass->getTemplate();
        $children = $block['children'] ?? [];
        $data = $block['data'] ?? [];

        $templateClass->setView($this->view)
            ->setTemplate($template)
            ->setStructure($children)
            ->setData($data);

        return $templateClass->toHtml();
    }

    /**
     * Set block's template.
     * @param string $template
     * @return $this
     */
    public function setTemplate($template) {
        $this->template = $template;

        return $this;
    }

    /**
     * Retrieve block's template.
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
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
     * Set block's structure.
     * @param array $structure
     * @return $this
     */
    public function setStructure($structure) {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get block's structure attribute by key.
     * Return all data if no key is set.
     * @param string $key
     * @return mixed
     */
    public function getStructureData($key = '') {
        if ($key === '') {
            $data = $this->structure;
        } else {
            $data = $this->structure[$key] ?? null;
        }

        return $data;
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
        return ($this->config->getConfig(App::WEB_ROOT_CONFIG) ? '' : '/pub') . $file;
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
                $path = str_replace('/view/' . $this->view, '/view/' . App::BASE_VIEW, $path);
            }
        }

        return APP_DIR . $path;
    }
}
