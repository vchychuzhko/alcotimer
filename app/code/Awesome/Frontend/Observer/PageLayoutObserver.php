<?php

namespace Awesome\Frontend\Observer;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Router;
use Awesome\Frontend\Model\Action\ErrorLayoutHandler;
use Awesome\Frontend\Model\Action\LayoutHandler;
use Awesome\Frontend\Model\XmlParser\LayoutXmlParser;

class PageLayoutObserver implements \Awesome\Framework\Model\Event\ObserverInterface
{
    private const PAGE_HANDLES_CACHE_TAG_PREFIX = 'page-handles_';

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
     * PageLayoutObserver constructor.
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
     * Add layout renderer as a Http action.
     * @inheritDoc
     */
    public function execute($event)
    {
        /** @var Router $router */
        $router = $event->getRouter();
        /** @var Request $request */
        $request = $event->getRequest();

        $handle = $request->getFullActionName();
        $view = $request->getView();

        if ($this->isHomepage($request)) {
            $handle = $this->getHomepageHandle();
        }
        if ($this->handleExist($handle, $view, $router)) {
            $router->addAction(LayoutHandler::class);
        } else {
            $router->addAction(ErrorLayoutHandler::class);
        }
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
        return $this->config->get(LayoutHandler::HOMEPAGE_HANDLE_CONFIG);
    }

    /**
     * Check if requested page handle is registered and exists in specified view.
     * @param string $handle
     * @param string $view
     * @param Router $router
     * @return bool
     */
    private function handleExist($handle, $view, $router)
    {
        $routes = $router->getStandardRoutes($view);
        list($route) = explode('_', $handle);

        if (!$handles = $this->cache->get(Cache::LAYOUT_CACHE_KEY, self::PAGE_HANDLES_CACHE_TAG_PREFIX . $view)) {
            $handles = $this->layoutXmlParser->getPageHandles($view);

            $this->cache->save(Cache::LAYOUT_CACHE_KEY, self::PAGE_HANDLES_CACHE_TAG_PREFIX . $view, $handles);
        }

        return isset($routes[$route]) && in_array($handle, $handles, true);
    }
}
