<?php

namespace Awesome\Frontend\Model\Action;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Http\Response\HtmlResponse;
use Awesome\Frontend\Model\TemplateRenderer;
use Awesome\Frontend\Model\XmlParser\LayoutXmlParser;

class LayoutHandler implements \Awesome\Framework\Model\ActionInterface
{
    public const HOMEPAGE_HANDLE_CONFIG = 'web/homepage';

    /**
     * @var Cache $cache
     */
    protected $cache;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var LayoutXmlParser $layoutXmlParser
     */
    protected $layoutXmlParser;

    /**
     * LayoutHandler constructor.
     * @param Cache $cache
     * @param Config $config
     * @param LayoutXmlParser $layoutXmlParser
     */
    public function __construct(
        Cache $cache,
        Config $config,
        LayoutXmlParser $layoutXmlParser
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->layoutXmlParser = $layoutXmlParser;
    }

    /**
     * Render html page according to request path and view.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute($request)
    {
        //@todo: add XmlValidationException and AutoloadException (thematic exceptions, in other words)
        $handle = $request->getFullActionName();
        $view = $request->getView();
        $handles = [$handle];

        if ($this->isHomepage($request)) {
            $handle = $this->getHomepageHandle();
            $handles[] = $handle;
        }

        if (!$pageContent = $this->cache->get(Cache::FULL_PAGE_CACHE_KEY, $handle . '_' . $view)) {
            $pageContent = $this->renderPage($handle, $view, $handles);

            $this->cache->save(Cache::FULL_PAGE_CACHE_KEY, $handle . '_' . $view, $pageContent);
        }

        return new HtmlResponse($pageContent, Response::SUCCESS_STATUS_CODE);
    }

    /**
     * Render page by specified handle and view.
     * @param string $handle
     * @param string $view
     * @param array $handles
     * @return string
     * @throws \Exception
     */
    protected function renderPage($handle, $view, $handles = [])
    {
        $handles = $handles ?: [$handle];

        if (!$structure = $this->cache->get(Cache::LAYOUT_CACHE_KEY, $handle)) {
            $structure = $this->layoutXmlParser->getLayoutStructure($handle, $view, $handles);

            $this->cache->save(Cache::LAYOUT_CACHE_KEY, $handle, $structure);
        }
        $templateRenderer = new TemplateRenderer($handle, $view, $structure, $handles);

        return $templateRenderer->render('root');
    }

    /**
     * Check if homepage is requested.
     * @param Request $request
     * @return bool
     */
    private function isHomepage($request)
    {
        return $request->getFullActionName() === Http::ROOT_ACTION_NAME;
    }

    /**
     * Get homepage handle.
     * @return string
     */
    private function getHomepageHandle()
    {
        return $this->config->get(self::HOMEPAGE_HANDLE_CONFIG);
    }
}
