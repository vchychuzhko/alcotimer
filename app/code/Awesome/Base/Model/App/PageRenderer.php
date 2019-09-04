<?php

namespace Awesome\Base\Model\App;

use \Awesome\Base\Model\XmlParser\PageXmlParser;

class PageRenderer
{
    private const BASE_TEMPLATE_PATH = '/Awesome/Base/view/base/templates/base.phtml';
    private const FRONTEND_VIEW = 'frontend';
    private const ADMINHTML_VIEW = 'adminhtml';
    private const BASE_VIEW = 'base';

    /**
     * @var PageXmlParser $pageXmlParser
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
        $this->pageXmlParser = new PageXmlParser();
    }

    /**
     * Render the page according to XML handle.
     * @param string $handle
     * @param string $view
     * @return string
     */
    public function render($handle, $view = PageXmlParser::FRONTEND_VIEW)
    {
        $page = '';
        $handle = $this->parseHandle($handle);

        if ($this->handleExist($handle, $view)) {
            $this->handle = $handle;
            $this->view = $view;
            $this->structure = $this->pageXmlParser->retrievePageStructure($handle, $view);

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
    public function handleExist($handle, $view = PageXmlParser::FRONTEND_VIEW)
    {
        $handle = $this->parseHandle($handle);

        return $this->pageXmlParser->handleExist($handle, $view);
    }

    /**
     * Parse requested handle into valid page handle.
     * Handle should consists of three parts, missing ones will be added automatically as 'index'.
     * @param string $handle
     * @return string
     */
    private function parseHandle($handle)
    {
        $parts = explode('_', $handle);
        $handle = $parts[0] . '_' //module
            . ($parts[1] ?? 'index') . '_' //page
            . ($parts[2] ?? 'index'); //action

        return $handle;
    }

    /**
     * Create and render head part of the page.
     * @return string
     */
    public function getHead()
    {
        $head = '';

        if ($headStructure = $this->structure['head']) {
            $this->headRenderer->setData($headStructure);
            $this->headRenderer->setView(self::BASE_VIEW);
            $head = $this->headRenderer->toHtml();
        }

        return $head;
    }

    /**
     * Get body class by page handle.
     * @return string
     */
    public function getBodyClass()
    {
        $class = str_replace('-', '', $this->handle);

        return str_replace('_', '-', $class);
    }

    /**
     * Create and render body part of the page.
     * @return string
     */
    public function getBody()
    {
        $body = '';

        if ($bodyStructure = $this->structure['head']) {
            $this->bodyRenderer->setData($bodyStructure);
            $this->bodyRenderer->setView(self::BASE_VIEW);
            $body = $this->bodyRenderer->toHtml();
        };

        return $body;
    }
}
