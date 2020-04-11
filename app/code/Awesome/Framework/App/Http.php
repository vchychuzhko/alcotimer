<?php

namespace Awesome\Framework\App;

use Awesome\Framework\Handler\LayoutHandler;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Logger;
use Awesome\Maintenance\Model\Maintenance;

class Http implements \Awesome\Framework\Model\AppInterface
{
    public const FRONTEND_VIEW = 'frontend';
    public const BACKEND_VIEW = 'adminhtml';
    public const BASE_VIEW = 'base';

    public const SHOW_FORBIDDEN_CONFIG = 'web/show_forbidden';
    public const HOMEPAGE_HANDLE_CONFIG = 'web/homepage';
    public const WEB_ROOT_CONFIG = 'web/web_root_is_pub';

    /**
     * @var Logger
     */
    private $logWriter;

    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * @var LayoutHandler $layoutHandler
     */
    private $layoutHandler;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var Request $request
     */
    private $request;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->logWriter = new Logger();
        $this->maintenance = new Maintenance();
        $this->layoutHandler = new LayoutHandler();
        $this->config = new Config();
    }

    /**
     * Run the web application.
     * @inheritDoc
     */
    public function run()
    {
        $request = $this->getRequest();
        $this->logWriter->logVisitor($request);

        if (!$this->isMaintenance()) {
            $pageView = $this->resolveView();
            $this->layoutHandler->setView($pageView);

            $pageHandle = $this->resolveHandle();
            $response = $this->layoutHandler->process($pageHandle);
        } else {
            $response = $this->maintenance->getMaintenancePage();
        }

        echo $response;
    }

    /**
     * Resolve page view by requested URL.
     * @return string
     */
    private function resolveView()
    {
        // @TODO: temporary, should be updated to resolve adminhtml view
        return self::FRONTEND_VIEW;
    }

    /**
     * Resolve page handle by requested URL.
     * @return string
     */
    private function resolveHandle()
    {
        $redirectStatus = $this->getRequest()->getRedirectStatusCode();

        if ($redirectStatus === 403 && $this->showForbiddenPage()) {
            $handle = 'forbidden';
            http_response_code(403);
        } else {
            $path = $this->getRequest()->getPath();

            if ($path === '/') {
                $path = $this->getHomepageHandle();
            }
            $handle = $this->layoutHandler->parse($path);

            if (!$this->layoutHandler->exist($handle)) {
                $handle = 'notfound';
                http_response_code(404);
            }
        }

        return $handle;
    }

    /**
     * Check if maintenance mode is active for user IP address.
     * @return bool
     */
    private function isMaintenance()
    {
        $ip = $this->getRequest()->getUserIPAddress();

        return $this->maintenance->isMaintenance($ip);
    }

    /**
     * Check if it is allowed to show 403 Forbidden page.
     * @return bool
     */
    private function showForbiddenPage()
    {
        return (bool) $this->config->get(self::SHOW_FORBIDDEN_CONFIG);
    }

    /**
     * Return current homepage handle.
     * @return string
     */
    private function getHomepageHandle()
    {
        return (string) $this->config->get(self::HOMEPAGE_HANDLE_CONFIG);
    }

    /**
     * Parse and return http request.
     * @return Request
     */
    private function getRequest()
    {
        if (!$this->request) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443
                ? Request::SCHEME_HTTPS
                : Request::SCHEME_HTTP;
            $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];
            $parameters = [];
            $redirectStatus = $_SERVER['REDIRECT_STATUS'] ?? null;
            $userIPAddress = $_SERVER['REMOTE_ADDR'];

            if ($_GET) {
                $parameters = array_merge($parameters, $_GET);
            }

            if ($_POST) {
                $parameters = array_merge($parameters, $_POST);
            }
            // @TODO: add cookies parsing

            $this->request = new Request($url, $method, $parameters, $redirectStatus, $userIPAddress);
        }

        return $this->request;
    }
}
