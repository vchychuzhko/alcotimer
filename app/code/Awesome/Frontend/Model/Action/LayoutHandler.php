<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Action;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Http;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Http\Router;
use Awesome\Frontend\Model\Result\ResultPageFactory;

class LayoutHandler extends \Awesome\Framework\Model\AbstractAction
{
    private const PAGE_HANDLES_CACHE_TAG_PREFIX = 'page-handles_';

    private const LAYOUT_XML_PATH_PATTERN = '/*/*/view/%s/layout/*_*_*.xml';

    public const HOMEPAGE_HANDLE_CONFIG = 'web/homepage';

    public const FORBIDDEN_PAGE_HANDLE = 'forbidden_index_index';
    public const NOTFOUND_PAGE_HANDLE = 'notfound_index_index';

    /**
     * @var AppState $appState
     */
    private $appState;

    /**
     * @var Cache $cache
     */
    private $cache;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var ResultPageFactory $resultPageFactory
     */
    private $resultPageFactory;

    /**
     * @var Router $router
     */
    private $router;

    /**
     * LayoutHandler constructor.
     * @param AppState $appState
     * @param Cache $cache
     * @param Config $config
     * @param ResultPageFactory $resultPageFactory
     * @param Router $router
     * @param array $data
     */
    public function __construct(
        AppState $appState,
        Cache $cache,
        Config $config,
        ResultPageFactory $resultPageFactory,
        Router $router,
        array $data = []
    ) {
        parent::__construct($data);
        $this->appState = $appState;
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
    public function execute(Request $request): Response
    {
        $handle = $request->getFullActionName();
        $handles = [$handle];
        $view = $request->getView();
        $status = Response::SUCCESS_STATUS_CODE;

        if ($this->isHomepage($request)) {
            $handle = $this->getHomepageHandle();
            $handles[] = $handle;
        }

        if (!$this->handleExist($handle, $view)) {
            if ($request->getRedirectStatusCode() === Request::FORBIDDEN_REDIRECT_CODE
                && $this->appState->showForbidden()
            ) {
                $handle = self::FORBIDDEN_PAGE_HANDLE;
                $status = Response::FORBIDDEN_STATUS_CODE;
            } else {
                $handle = self::NOTFOUND_PAGE_HANDLE;
                $status = Response::NOTFOUND_STATUS_CODE;
            }
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
        return $request->getFullActionName() === Http::ROOT_ACTION_NAME;
    }

    /**
     * Get homepage handle.
     * @return string
     */
    private function getHomepageHandle(): string
    {
        return $this->config->get(self::HOMEPAGE_HANDLE_CONFIG);
    }

    /**
     * Check if requested page handle is registered and exists in specified view.
     * @param string $handle
     * @param string $view
     * @return bool
     */
    private function handleExist(string $handle, string $view): bool
    {
        list($route) = explode('_', $handle);

        return $this->router->getStandardRoute($route, $view) && in_array($handle, $this->getPageHandles($view), true);
    }

    /**
     * Get available page layout handles for requested view.
     * @param string $view
     * @return array
     */
    private function getPageHandles(string $view): array
    {
        return $this->cache->get(Cache::LAYOUT_CACHE_KEY, self::PAGE_HANDLES_CACHE_TAG_PREFIX . $view, function () use ($view) {
            $handles = [];
            $pattern = sprintf(self::LAYOUT_XML_PATH_PATTERN, $view);

            foreach (glob(APP_DIR . $pattern) as $collectedHandle) {
                $handles[] = basename($collectedHandle, '.xml');
            }

            return array_unique($handles);
        });
    }
}
