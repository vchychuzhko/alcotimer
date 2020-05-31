<?php

namespace Awesome\Frontend\Block;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Http;
use Awesome\Frontend\Model\StaticContent;
use Awesome\Frontend\Model\TemplateRenderer;

class Template extends\Awesome\Framework\Model\DataObject
{
    /**
     * @var TemplateRenderer $renderer
     */
    protected $renderer;

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
    protected $children;

    /**
     * @var string $mediaUrl
     */
    protected $mediaUrl;

    /**
     * @var string $staticUrl
     */
    protected $staticUrl;

    /**
     * @var StaticContent $staticContent
     */
    protected $staticContent;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * Template constructor.
     * @param TemplateRenderer $renderer
     * @param string $name
     * @param string|null $template
     * @param array $children
     * @param array $data
     */
    public function __construct($renderer, $name, $template = null, $children = [], $data = [])
    {
        parent::__construct($data, true);
        $this->renderer = $renderer;
        $this->name = $name;
        $this->template = $template ?: $this->template;
        $this->children = $children;
        $this->staticContent = new StaticContent();
        $this->config = new Config();
    }

    /**
     * Render template.
     * @return string
     */
    public function toHtml()
    {
        return $this->renderer->renderElement($this);
    }

    /**
     * Get child element.
     * Return all children if no name is specified.
     * @param string $childName
     * @return string
     */
    public function getChildHtml($childName = '')
    {
        $childHtml = '';

        if ($childName) {
            if (in_array($childName, $this->children)) {
                $childHtml = $this->renderer->render($childName);
            }
        } else {
            foreach ($this->children as $childName) {
                $childHtml .= $this->renderer->render($childName);
            }
        }

        return $childHtml;
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
    private function getPubUrl($file = '')
    {
        return ($this->config->get(Http::WEB_ROOT_CONFIG) ? '' : '/pub') . $file;
    }

    /**
     * Get element name.
     * @return string
     */
    public function getNameInLayout()
    {
        return $this->name;
    }

    /**
     * Get element template.
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
