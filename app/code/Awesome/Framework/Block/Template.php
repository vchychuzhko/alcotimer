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
     * @var array $data
     */
    protected $data = [];

    /**
     * @var array $structure
     */
    protected $structure = [];

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
                $childHtml .= $this->renderBlock($block);
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
        $template = $block['template'];
        $children = $block['children'] ?? [];
        $data = $block['data'] ?? [];

        /** @var \Awesome\Framework\Block\Template $templateClass */
        $templateClass = new $className();
        $templateClass->setView($this->view)
            ->setTemplate($template)
            ->setStructure($children)
            ->setData($data);

        return $templateClass->toHtml();
    }

    /**
     * Set template.
     * @param string $template
     * @return $this
     */
    public function setTemplate($template) {
        $this->template = $template;

        return $this;
    }

    /**
     * Set current page view.
     * @param $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Set children blocks structure.
     * @param array $structure
     * @return $this
     */
    public function setStructure($structure) {
        $this->structure = $structure['children'] ?? $structure;

        return $this;
    }

    /**
     * Return URL path to media folder, according to the root folder.
     * @return string
     */
    public function getMediaUrl()
    {
        return '/' . PUB_DIR . 'media/';
    }

    /**
     * Return URL path to static folder, according to the root folder.
     * @return string
     */
    public function getStaticUrl()
    {
        if (!$deployedVersion = $this->staticContent->getDeployedVersion()) {
            $deployedVersion = $this->staticContent->deploy()
                ->getDeployedVersion();
            //@TODO: Resolve situation when frontend folder is missing, but deployed version is present
        }

        return '/' . PUB_DIR . 'static/version' . $deployedVersion . '/';
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

    /**
     * Template data getter.
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     *
     * @param string $key
     * @return mixed
     */
    public function getData($key = '')
    {
        if ($key === '') {
            $data = $this->data;
        } else {
            $data = $this->data[$key] ?? null;
        }

        return $data;
    }

    /**
     * Template data setter.
     *
     * The $key parameter can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if ($key === (array) $key) {
            $this->data = $key;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Set/Get attribute wrapper.
     * vendor/magento/framework/DataObject.php - L381
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->underscore(substr($method, 3));

                return $this->getData($key);
            case 'set':
                $key = $this->underscore(substr($method, 3));
                $value = isset($args[0]) ? $args[0] : null;

                return $this->setData($key, $value);
        }

        throw new \Exception(
            'Invalid method ' . get_class($this) . '::' . $method
        );
    }

    /**
     * Converts camelCase to snake_case for setters and getters.
     * $this->getMyField() === $this->getData('my_field')
     *
     * @param string $string
     * @return string
     */
    protected function underscore($string)
    {
        return strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $string), '_'));
    }

    /**
     * Converts snake_case to camelCase for js widget configurations.
     *
     * @param $string
     * @param string $separator
     * @return string
     */
    protected function camelCase($string, $separator = '_')
    {
        return str_replace($separator, '', lcfirst(ucwords($string, $separator)));
    }
}
