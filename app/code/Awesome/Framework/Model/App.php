<?php

namespace Awesome\Framework\Model;

class App
{
    public const VERSION = '0.2.3';

    public const FRONTEND_VIEW = 'frontend';
    public const BACKEND_VIEW = 'adminhtml';
    public const BASE_VIEW = 'base';

    public const SHOW_FORBIDDEN_CONFIG = 'web/show_forbidden';
    public const HOMEPAGE_HANDLE_CONFIG = 'web/homepage';
    public const WEB_ROOT_CONFIG = 'web/web_root_is_pub';

    /**
     * @var \Awesome\Logger\Model\LogWriter
     */
    private $logWriter;

    /**
     * @var \Awesome\Maintenance\Model\Maintenance $maintenance
     */
    private $maintenance;

    /**
     * @var \Awesome\Framework\Model\Config $config
     */
    private $config;

    /**
     * @var \Awesome\Framework\Model\App\PageRenderer
     */
    private $pageRenderer;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->logWriter = new \Awesome\Logger\Model\LogWriter();
        $this->maintenance = new \Awesome\Maintenance\Model\Maintenance();
        $this->pageRenderer = new \Awesome\Framework\Model\App\PageRenderer();
        $this->config = new \Awesome\Framework\Model\Config();
    }

    /**
     * Run web app.
     */
    public function run()
    {
        $this->logWriter->logVisitor();

        if (!$this->isMaintenance()) {
            $pageHandle = $this->resolveUrl();
            $response = $this->pageRenderer->render($pageHandle, self::FRONTEND_VIEW);
        } else {
            $response = $this->pageRenderer->renderMaintenancePage();
        }

        echo $response;
    }

    /**
     * Resolve page handle by requested URL.
     * @return string
     */
    private function resolveUrl()
    {
        $redirectStatus = (string) ($_SERVER['REDIRECT_STATUS'] ?? '');

        if ($redirectStatus === '403' && $this->showForbiddenPage()) {
            $handle = 'forbidden';
            http_response_code(403);
        } else {
            $uri = (string) strtok(trim($_SERVER['REQUEST_URI'], '/'), '?');

            if ($uri === '') {
                $uri = $this->getHomepageHandle();
            }
            $handle = $this->pageRenderer->parseHandle($uri);

            if (!$this->pageRenderer->handleExist($handle, self::FRONTEND_VIEW)) {
                $handle = 'notfound';
                http_response_code(404);
            }
        }

        return $handle;
    }

    /**
     * Check if maintenance mode is active for current IP.
     * @return bool
     */
    private function isMaintenance()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        return $this->maintenance->isMaintenanceForIp($ip);
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
     * Return currently set homepage handle.
     * @return string
     */
    private function getHomepageHandle()
    {
        return (string) $this->config->get(self::HOMEPAGE_HANDLE_CONFIG);
    }
}
