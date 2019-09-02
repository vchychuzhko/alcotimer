<?php

namespace Awesome\Base\Model;

class PageRenderer
{
    private const BASE_TEMPLATE_PATH = '/Awesome/Base/view/base/templates/base.phtml';
    private const FRONTEND_VIEW = 'frontend';
    private const ADMINHTML_VIEW = 'adminhtml';

    /**
     * @var \Awesome\Base\Model\XmlParser\PageXmlParser $pageXmlParser
     */
    private $pageXmlParser;

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
     * @var \Awesome\Base\Block\Html\Head $headTemplate
     */
    private $headRenderer;

    /**
     * @var \Awesome\Base\Block\Html\Body $bodyTemplate
     */
    private $bodyRenderer;

    /**
     * PageRenderer constructor.
     */
    function __construct()
    {
        $this->headRenderer = new \Awesome\Base\Block\Html\Head();
        $this->bodyRenderer = new \Awesome\Base\Block\Html\Body();
        $this->pageXmlParser = new \Awesome\Base\Model\XmlParser\PageXmlParser();
    }

    /**
     * Render the page according to XML handle.
     * @param string $handle
     * @param string $view
     * @return string
     */
    public function render($handle, $view = self::FRONTEND_VIEW)
    {
        $page = '';

        if ($this->handleExist($handle, $view)) {
            $this->view = $view;
            ob_start(); //prevent includes from output everything to the page
            include(APP_DIR . self::BASE_TEMPLATE_PATH);
            $page = ob_get_clean();
        }

        return $page;
    }

    /**
     * Check if requested page handle exists.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    public function handleExist($handle, $view = self::FRONTEND_VIEW)
    {
        $handle = $this->parseHandle($handle);
        $this->structure = $this->structure ?? $this->pageXmlParser->retrievePageStructure($handle, $view);

        return !empty($this->structure);
    }

    /**
     * Parse requested handle into valid page handle.
     * Handle should consists of three parts, missing ones will be added automatically as 'index'.
     * @param string $handle
     * @return string
     */
    private function parseHandle($handle)
    {
        if ($this->handle !== $handle) {
            $handle = str_replace('-', '_', $handle);
            $parts = explode('_', $handle);
            $handle = $parts[0] . '_' //module
                . ($parts[1] ?? 'index') . '_' //page
                . ($parts[2] ?? 'index'); //action

            $this->handle = $handle;
            $this->structure = null;
        }

        return $this->handle;
    }

    /**
     *
     * @return string
     */
    public function getHead()
    {
        $head = '';

        if ($headStructure = $this->structure['head']) {
            $this->headRenderer->setData($headStructure);
            $this->headRenderer->setData('view', $this->view);
            $head = $this->headRenderer->toHtml();
        }

        return $head;
    }

    /**
     *
     * @return string
     */
    public function getBodyClass()
    {
        return str_replace('_', '-', $this->handle);
    }

    /**
     *
     * @return string
     */
    public function getBody()
    {
        $body = '';

        if ($bodyStructure = $this->structure['head']) {
            $this->bodyRenderer->setData($bodyStructure);
            $body = $this->bodyRenderer->toHtml();
        }

        return $body;
    }
}
