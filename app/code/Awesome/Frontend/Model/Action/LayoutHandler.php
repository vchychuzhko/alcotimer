<?php

namespace Awesome\Frontend\Model\Action;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Http\Response\HtmlResponse;
use Awesome\Frontend\Model\TemplateRenderer;
use Awesome\Frontend\Model\XmlParser\LayoutXmlParser;

/**
 * Class LayoutHandler
 * @method getHandle()
 * @method getHandles()
 * @method getStatus()
 */
class LayoutHandler extends \Awesome\Framework\Model\AbstractAction
{
    public const HOMEPAGE_HANDLE_CONFIG = 'web/homepage';

    public const FORBIDDEN_PAGE_HANDLE = 'forbidden_index_index';
    public const NOTFOUND_PAGE_HANDLE = 'notfound_index_index';

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var LayoutXmlParser $layoutXmlParser
     */
    private $layoutXmlParser;

    /**
     * LayoutHandler constructor.
     * @param Cache $cache
     * @param LayoutXmlParser $layoutXmlParser
     * @param array $data
     */
    public function __construct(
        Cache $cache,
        LayoutXmlParser $layoutXmlParser,
        $data = []
    ) {
        parent::__construct($data);
        $this->cache = $cache;
        $this->layoutXmlParser = $layoutXmlParser;
    }

    /**
     * Render html page according to request path and view.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute($request)
    {
        $handle = $this->getHandle();
        $handles = $this->getHandles();
        $status = $this->getStatus();
        $view = $request->getView();

        if (!$pageContent = $this->cache->get(Cache::FULL_PAGE_CACHE_KEY, $handle . '_' . $view)) {
            $pageContent = $this->renderPage($handle, $view, $handles);

            $this->cache->save(Cache::FULL_PAGE_CACHE_KEY, $handle . '_' . $view, $pageContent);
        }

        return new HtmlResponse($pageContent, $status);
    }

    /**
     * Render page by specified handle and view.
     * @param string $handle
     * @param string $view
     * @param array $handles
     * @return string
     * @throws \Exception
     */
    private function renderPage($handle, $view, $handles = [])
    {
        // @TODO: Create Page model along with PageFactory and move rendering there
        $handles = $handles ?: [$handle];

        if (!$structure = $this->cache->get(Cache::LAYOUT_CACHE_KEY, $handle)) {
            $structure = $this->layoutXmlParser->getLayoutStructure($handle, $view, $handles);

            $this->cache->save(Cache::LAYOUT_CACHE_KEY, $handle, $structure);
        }
        $templateRenderer = new TemplateRenderer($handle, $view, $structure, $handles);

        return $templateRenderer->render('root');
    }
}
