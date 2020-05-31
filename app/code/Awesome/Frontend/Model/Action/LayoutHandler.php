<?php

namespace Awesome\Frontend\Model\Action;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response;
use Awesome\Frontend\Model\Http\HtmlResponse;
use Awesome\Frontend\Model\TemplateRenderer;
use Awesome\Frontend\Model\XmlParser\LayoutXmlParser;

class LayoutHandler implements \Awesome\Framework\Model\ActionInterface
{
    public const HOMEPAGE_HANDLE_CONFIG = 'web/homepage';

    public const FORBIDDEN_PAGE_HANDLE = 'forbidden_index_index';
    public const NOTFOUND_PAGE_HANDLE = 'notfound_index_index';

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var LayoutXmlParser $layoutXmlParser
     */
    private $layoutXmlParser;

    /**
     * LayoutHandler constructor.
     */
    function __construct()
    {
        $this->cache = new Cache();
        $this->config = new Config();
        $this->layoutXmlParser = new LayoutXmlParser();
    }

    /**
     * Render html page according to request path and view.
     * @inheritDoc
     */
    public function execute($request)
    {
        $handle = $request->getFullActionName();
        $view = $request->getView();
        $status = Response::SUCCESS_STATUS_CODE;
        $handles = [];

        if ($this->isHomepage($request)) {
            $handles[] = $handle;
            $handle = $this->getHomepageHandle();
        }

        if (!$this->handleExist($handle, $view)) {
            $redirectStatus = $request->getRedirectStatusCode();

            if ($redirectStatus === Request::FORBIDDEN_REDIRECT_CODE && $this->showForbiddenPage()) {
                $handle = self::FORBIDDEN_PAGE_HANDLE;
                $status = Response::FORBIDDEN_STATUS_CODE;
            } else {
                $handle = self::NOTFOUND_PAGE_HANDLE;
                $status = Response::NOTFOUND_STATUS_CODE;
            }
        }
        $handles[] = $handle;

        if (!$pageContent = $this->cache->get(Cache::FULL_PAGE_CACHE_KEY, $handle . '_' . $view)) {
            $structure = $this->layoutXmlParser->getLayoutStructure($handle, $view, $handles);
            $templateRenderer = new TemplateRenderer($handle, $view, $structure, $handles);

            $pageContent = $templateRenderer->render('root');

            $this->cache->save(Cache::FULL_PAGE_CACHE_KEY, $handle . '_' . $view, $pageContent);
        }

        return new HtmlResponse($pageContent, $status);
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

    /**
     * Check if requested page handle exists in specified view.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    private function handleExist($handle, $view)
    {
        return in_array($handle, $this->layoutXmlParser->getPageHandles($view))
            && !in_array($handle, $this->getSystemHandles());
    }

    /**
     * Get hidden system handles.
     * @return array
     */
    private function getSystemHandles()
    {
        // @TODO: Move them to a separate folder or mark as system in XML
        return [self::FORBIDDEN_PAGE_HANDLE, self::NOTFOUND_PAGE_HANDLE];
    }

    /**
     * Check if it is allowed to show 403 Forbidden page.
     * @return bool
     */
    private function showForbiddenPage()
    {
        // @TODO: Save this value to registry in Http and remove this method as duplicating
        return (bool) $this->config->get(Http::SHOW_FORBIDDEN_CONFIG);
    }
}
