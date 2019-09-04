<?php

namespace Awesome\Base\Model;

use \Awesome\Maintenance\Model\Maintenance;

class App
{
    public const CONFIG_FILE_PATH = 'app/config.php';
    private const TEMPLATES_DIR = BP . '/app/templates';

    /**
     * @var \Awesome\Logger\Model\LogWriter
     */
    private $logWriter;

    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * @var array $config
     */
    private $config;

    /**
     * @var \Awesome\Cache\Model\StaticContent $staticContent
     */
    private $staticContent;

    /**
     * @var \Awesome\Base\Model\App\PageRenderer
     */
    private $pageRenderer;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->logWriter = new \Awesome\Logger\Model\LogWriter();
        $this->maintenance = new Maintenance();
        $this->pageRenderer = new \Awesome\Base\Model\App\PageRenderer();
        $this->config = $this->loadConfig();
    }

    /**
     * Run web app.
     */
    public function run()
    {
        $this->logWriter->logVisitor();

        $handle = $this->resolveUrl();
        $response = $this->pageRenderer->render($handle);

        if ($this->isMaintenance() || !$this->config || !$response) {
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
                $handle = $this->config['routes']['homepage'];
            } else {
                $handle = str_replace('/', '_', $uri);
            }

            if (!$this->pageRenderer->handleExist($handle)) {
                $handle = 'notfound';
                http_response_code(404);
            }
        }

        return $handle;
    }

    /**
     * Include configuration file.
     * @return array
     */
    public function loadConfig()
    {
        $config = @include(BP . '/' . self::CONFIG_FILE_PATH);

        return $config ?: [];
    }

    /**
     * Check if maintenance mode is active.
     * @return bool
     */
    private function isMaintenance()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        return $this->maintenance->isMaintenanceForIp($ip);
    }

    /**
     * Get site support email address.
     * @return string
     */
    public function getSupportEmailAddress()
    {
        return $this->config['support_email_address'] ?? '';
    }

    /**
     * Get timer configurations.
     * @return array
     */
    public function getTimerConfigurations()
    {
        return $this->config['timer_config'];
    }
}
