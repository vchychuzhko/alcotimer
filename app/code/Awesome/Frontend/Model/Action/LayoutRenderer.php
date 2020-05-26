<?php

namespace Awesome\Frontend\Model\Action;

use Awesome\Cache\Model\Cache;
use Awesome\Frontend\Block\Root;
use Awesome\Frontend\Model\Http\HtmlResponse;
use Awesome\Frontend\Model\TemplateRenderer;
use Awesome\Frontend\Model\XmlParser\LayoutXmlParser;

class LayoutRenderer implements \Awesome\Framework\Model\ActionInterface
{
    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var LayoutXmlParser $layoutXmlParser
     */
    private $layoutXmlParser;

    /**
     * @var string $handle
     */
    private $handle;

    /**
     * @var string $view
     */
    private $view;

    /**
     * LayoutHandler constructor.
     * @param string $handle
     * @param string $view
     */
    function __construct($handle, $view)
    {
        $this->handle = $handle;
        $this->view = $view;
        $this->cache = new Cache();
        $this->layoutXmlParser = new LayoutXmlParser();
    }

    /**
     * Render html page according to request path and initialized view.
     * @inheritDoc
     */
    public function execute($request)
    {
        if (!$pageContent = $this->cache->get(Cache::FULL_PAGE_CACHE_KEY, $this->handle)) {
            $structure = $this->layoutXmlParser->getLayoutStructure($this->handle, $this->view);

            $templateRenderer = new TemplateRenderer($this->handle, $this->view, $structure['body']['children']);
            $html = new Root($templateRenderer, 'root', null, $structure);

            $pageContent = $html->toHtml();

            $this->cache->save(Cache::FULL_PAGE_CACHE_KEY, $this->handle, $pageContent);
        }

        return new HtmlResponse($pageContent);
    }
}
