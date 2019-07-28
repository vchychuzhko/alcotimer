<?php

namespace Awesome\Frontend\Model;

use \Awesome\Maintenance\Model\Maintenance;

class App
{
    public const CONFIG_FILE = 'app/config.php';
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
     * App constructor.
     */
    public function __construct()
    {
        $this->logWriter = new \Awesome\Logger\Model\LogWriter();
        $this->maintenance = new Maintenance();
        $this->staticContent = new \Awesome\Cache\Model\StaticContent();
        $this->config = $this->loadConfig();
    }

    /**
     * Run web app.
     */
    public function run()
    {
        ob_start();
        $template = Maintenance::MAINTENANCE_PAGE_PATH;

        if (!$this->isMaintenance() && $this->config) {
            $routes = $this->config['routes'];
            $systemRoutes = $this->config['system_routes'];
            $redirectStatus = (string) ($_SERVER['REDIRECT_STATUS'] ?? '');

            if ($redirectStatus === '403') {
                $templateName = $systemRoutes[$redirectStatus];
            } else {
                $uri = (string) strtok(trim($_SERVER['REQUEST_URI'], '/'), '?');

                if (isset($routes[$uri])) {
                    $templateName = $routes[$uri];
                } else {
                    http_response_code(404);
                    $templateName = $systemRoutes['404'];
                }
            }

            $templateFile = self::TEMPLATES_DIR . '/' . $templateName;

            if (file_exists($templateFile)) {
                $template = $templateFile;
            } else {
                $this->logWriter->write('No such file: ' . $templateFile);
            }
        }

        include $template;

        $response = ob_get_clean();
        echo $response;
    }

    /**
     * Include configuration file.
     * @return array
     */
    public function loadConfig()
    {
        $config = [];

        if (file_exists(BP . '/' . self::CONFIG_FILE)) {
            require_once(BP . '/' . self::CONFIG_FILE);
        } else {
            $this->logWriter->write('Config file is missing.');
        }

        return $config;
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

    /**
     *
     * @return string
     */
    public function getStaticPath()
    {
        if (!$deployedVersion = $this->staticContent->getDeployedVersion()) {
            $deployedVersion = $this->staticContent->deploy()->getDeployedVersion();
            //@TODO: Resolve situation when frontend folder is missing, but deployed version is present
        }

        return PUB_DIR . '/static/version' . $deployedVersion . '/frontend';
    }

    /**
     *
     * @return string
     */
    public function getMediaPath()
    {
        return '/' . PUB_DIR . 'media';
    }
}
