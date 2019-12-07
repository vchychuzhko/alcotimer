<?php

namespace Awesome\Framework\Model;

use \Awesome\Maintenance\Model\Maintenance;

class App
{
    public const FRONTEND_VIEW = 'frontend';
    public const BACKEND_VIEW = 'adminhtml';
    public const BASE_VIEW = 'base';

    /**
     * @var \Awesome\Logger\Model\LogWriter
     */
    private $logWriter;

    /**
     * @var Maintenance $maintenance
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
        $this->maintenance = new Maintenance();
        $this->pageRenderer = new \Awesome\Framework\Model\App\PageRenderer();
        $this->config = new \Awesome\Framework\Model\Config();
    }

    /**
     * Run web app.
     */
    public function run()
    {
        $this->logWriter->logVisitor();

        $handle = $this->resolveUrl();
        $response = $this->pageRenderer->render($handle, self::FRONTEND_VIEW);

        if ($this->isMaintenance() || !$response) {
            $response = file_get_contents(BP . Maintenance::MAINTENANCE_PAGE_PATH);
        }

        echo $response;
    }

    /**
     * Resolve page handle by requested URL.
     * @return string
     */
    public function resolveUrl()
    {
        $redirectStatus = (string) ($_SERVER['REDIRECT_STATUS'] ?? '');

        if ($redirectStatus === '403') {
            $handle = 'forbidden';
            http_response_code(403);
        } else {
            $uri = (string) strtok(trim($_SERVER['REQUEST_URI'], '/'), '?');

            if ($uri === '') {
                $handle = $this->config->getConfig('web/homepage');
            } else {
                $handle = str_replace('/', '_', $uri);
            }

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
}
