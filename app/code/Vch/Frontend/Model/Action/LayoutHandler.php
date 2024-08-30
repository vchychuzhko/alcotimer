<?php
declare(strict_types=1);

namespace Vch\Frontend\Model\Action;

use Vch\Cache\Model\Cache;
use Vch\Framework\Model\Config;
use Vch\Framework\Model\Http\Request;
use Vch\Framework\Model\Http\Router;
use Vch\Framework\Model\Http\ResponseFactory;
use Vch\Framework\Model\ResponseInterface;
use Vch\Frontend\Model\Http\PageResponseFactory;

/**
 * @deprecated
 */
class LayoutHandler extends \Vch\Framework\Model\AbstractAction
{
    private const PAGE_HANDLES_CACHE_TAG_PREFIX = 'page-handles_';

    private const LAYOUT_XML_PATH_PATTERN = '/*/*/view/%s/layout/*_*_*.xml';

    public const HOMEPAGE_HANDLE_CONFIG = 'web/homepage';

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
     * @var PageResponseFactory $resultPageFactory
     */
    private $resultPageFactory;

    /**
     * @var Router $router
     */
    private $router;

    /**
     * @var string $homepageHandle
     */
    private $homepageHandle;

    /**
     * @var string $homepageRoute
     */
    private $homepageRoute;

    /**
     * @var array $pageHandles
     */
    private $pageHandles;

    /**
     * LayoutHandler constructor.
     * @param Cache $cache
     * @param Config $config
     * @param ResponseFactory $responseFactory
     * @param PageResponseFactory $resultPageFactory
     * @param Router $router
     */
    public function __construct(
        Cache $cache,
        Config $config,
        ResponseFactory $responseFactory,
        PageResponseFactory $resultPageFactory,
        Router $router
    ) {
        parent::__construct($responseFactory);
        $this->cache = $cache;
        $this->config = $config;
        $this->resultPageFactory = $resultPageFactory;
        $this->router = $router;
    }

    /**
     * Render html page according to request path and view.
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(Request $request): ResponseInterface
    {
        $handle = $request->getFullActionName();
        $handles = [$handle];
        $route = $request->getRoute();
        $view = $request->getView();
        $status = ResponseInterface::SUCCESS_STATUS_CODE;

        if ($this->isHomepage($request)) {
            $handle = $this->getHomepageHandle();
            $route = $this->getHomepageRoute();
            $handles[] = $handle;
        }

        if (!$this->router->getStandardRoute($route, $view) || !$this->handleExist($handle, $view)) {
            $handle = self::NOTFOUND_PAGE_HANDLE;
            $status = ResponseInterface::NOTFOUND_STATUS_CODE;
            $handles = [$handle];
        }

        return $this->resultPageFactory->create($handle, $view, $handles)
            ->setStatusCode($status);
    }

    /**
     * Check if homepage is requested.
     * @param Request $request
     * @return bool
     */
    private function isHomepage(Request $request): bool
    {
        return $request->getFullActionName() === Request::ROOT_ACTION_NAME && $request->getRoute() !== Request::DEFAULT_ROUTE;
    }

    /**
     * Get homepage handle.
     * @return string
     */
    private function getHomepageHandle(): string
    {
        if ($this->homepageHandle === null) {
            $this->homepageHandle = str_replace('/', '_', $this->config->get(self::HOMEPAGE_HANDLE_CONFIG));
        }

        return $this->homepageHandle;
    }

    /**
     * Get homepage route.
     * @return string
     */
    private function getHomepageRoute(): string
    {
        if ($this->homepageRoute === null) {
            list($this->homepageRoute) = explode('/', $this->config->get(self::HOMEPAGE_HANDLE_CONFIG), 2);
        }

        return $this->homepageRoute;
    }

    /**
     * Check if requested page handle exists in specified view.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    private function handleExist(string $handle, string $view): bool
    {
        return in_array($handle, $this->getPageHandles($view), true);
    }

    /**
     * Get available page layout handles for requested view.
     * @param string $view
     * @return array
     */
    private function getPageHandles(string $view): array
    {
        if ($this->pageHandles === null) {
            $this->pageHandles = $this->cache->get(
                Cache::LAYOUT_CACHE_KEY,
                self::PAGE_HANDLES_CACHE_TAG_PREFIX . $view,
                function () use ($view) {
                    $handles = [];
                    $pattern = APP_DIR . sprintf(self::LAYOUT_XML_PATH_PATTERN, $view);

                    foreach (glob($pattern) as $collectedHandle) {
                        $handles[] = basename($collectedHandle, '.xml');
                    }

                    return array_unique($handles);
                }
            );
        }

        return $this->pageHandles;
    }
}
