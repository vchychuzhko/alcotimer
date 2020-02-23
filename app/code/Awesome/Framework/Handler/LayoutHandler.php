<?php

namespace Awesome\Framework\Handler;

use Awesome\Framework\Block\Html;
use Awesome\Framework\XmlParser\PageXmlParser;
use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Http\TemplateRenderer;

class LayoutHandler extends \Awesome\Framework\Model\Handler\AbstractHandler
{
    /**
     * @var PageXmlParser $pageXmlParser
     */
    private $pageXmlParser;

    /**
     * @var string $view
     */
    private $view;

    /**
     * LayoutHandler constructor.
     */
    function __construct()
    {
        $this->pageXmlParser = new PageXmlParser();
        parent::__construct();
    }

    /**
     * Render the page according to XML handle.
     * @param string $handle
     * @return string
     */
    public function process($handle)
    {
        $handle = $this->parse($handle);

        if (!$pageContent = $this->cache->get(Cache::FULL_PAGE_CACHE_KEY, $handle)) {
            $structure = $this->pageXmlParser->getPageStructure($handle, $this->view);

            $templateRenderer = new TemplateRenderer($handle, $this->view, $structure['body']['children']);
            $html = new Html($templateRenderer, 'root', null, $structure);

            $pageContent = $html->toHtml();

            $this->cache->save(Cache::FULL_PAGE_CACHE_KEY, $handle, $pageContent);
        }

        return $pageContent ?: '';
    }

    /**
     * @inheritDoc
     */
    public function exist($handle)
    {
        $handle = $this->parse($handle);

        return in_array($handle, $this->pageXmlParser->getHandles($this->view));
    }

    /**
     * Handle should consists of three parts, missing ones will be added automatically as 'index'.
     * @inheritDoc
     * @return string
     */
    public function parse($handle)
    {
        $handle = str_replace('/', '_', $handle);
        $parts = explode('_', $handle);
        $handle = $parts[0] . '_'           //module
            . ($parts[1] ?? 'index') . '_'  //page
            . ($parts[2] ?? 'index');       //action

        return $handle;
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
}
