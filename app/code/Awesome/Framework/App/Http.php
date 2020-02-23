<?php

namespace Awesome\Framework\App;

use Awesome\Logger\Model\LogWriter;
use Awesome\Maintenance\Model\Maintenance;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Handler\LayoutHandler;

class Http implements \Awesome\Framework\Model\AppInterface
{
    public const FRONTEND_VIEW = 'frontend';
    public const BACKEND_VIEW = 'adminhtml';
    public const BASE_VIEW = 'base';

    public const SHOW_FORBIDDEN_CONFIG = 'web/show_forbidden';
    public const HOMEPAGE_HANDLE_CONFIG = 'web/homepage';
    public const WEB_ROOT_CONFIG = 'web/web_root_is_pub';

    /**
     * @var LogWriter
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
     * App constructor.
     */
    public function __construct()
    {
        $this->logWriter = new LogWriter();
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
        $this->logWriter->logVisitor();

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
        return self::FRONTEND_VIEW;
    }

    /**
     * Resolve page handle by requested URL.
     * @return string
     */
    private function resolveHandle()
    {
        $redirectStatus = (string) ($_SERVER['REDIRECT_STATUS'] ?? '');

        if ($redirectStatus === '403' && $this->showForbiddenPage()) {
            $handle = 'forbidden';
            http_response_code(403);
        } else {
            $uri = $this->getRequestUri();

            if ($uri === '') {
                $uri = $this->getHomepageHandle();
            }
            $handle = $this->layoutHandler->parse($uri);

            if (!$this->layoutHandler->exist($handle)) {
                $handle = 'notfound';
                http_response_code(404);
            }
        }

        return $handle;
    }

    /**
     * Return requested URI path.
     * @return string
     */
    private function getRequestUri()
    {
        return (string) strtok(trim($_SERVER['REQUEST_URI'], '/'), '?');
    }

    /**
     * Check if maintenance mode is active for current IP.
     * @return bool
     */
    private function isMaintenance()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

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
}
