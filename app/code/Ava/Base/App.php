<?php

namespace Ava\Base;

class App
{
    public const CONFIG_FILE = 'app' . DS . 'config.php';
    private const TEMPLATES_DIR = 'app' . DS . 'templates';
    private const MAINTENANCE_DEFAULT_ROUTE = 'maintenance.php';
    private const MAINTENANCE_DEFAULT_EMAIL = 'vlad.chichuzhko@gmail.com';

    /**
     * @var \Ava\Logger\LogWriter
     */
    private $logWriter;

    /**
     * @var array $config
     */
    private $config;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->logWriter = new \Ava\Logger\LogWriter();
        $this->config = $this->loadConfig();
    }

    /**
     * Run web app.
     */
    public function run()
    {
        ob_start();

        if (!$this->isMaintenance() && $this->config) {
            $routes = $this->config['routes'];
            $systemRoutes = $this->config['system_routes'];
            $redirectStatus = (string) ($_SERVER['REDIRECT_STATUS'] ?? '');

            if ($redirectStatus === '403' || $redirectStatus === '405') {
                $template = $systemRoutes[$redirectStatus];
            } else {
                $uri = (string) strtok(trim($_SERVER['REQUEST_URI'], '/'), '?');
                $template = $routes[$uri] ?? $systemRoutes['404'];
            }

            $template = BP . DS . self::TEMPLATES_DIR . DS . $template;

            if (!file_exists($template)) {
                $this->logWriter->write('No such file: ' . $template);
                $template = BP . DS . self::TEMPLATES_DIR . DS . self::MAINTENANCE_DEFAULT_ROUTE;
            }
        } else {
            $template = BP . DS . self::TEMPLATES_DIR . DS . self::MAINTENANCE_DEFAULT_ROUTE;
        }

        include $template;

        $response = ob_get_clean();
        echo $response;
    }

    /**
     * @return array
     */
    public function loadConfig()
    {
        $config = [];

        if (file_exists(BP . DS . self::CONFIG_FILE)) {
            require_once(BP . DS . self::CONFIG_FILE);
        } else {
            $this->logWriter->write('Config file is missing.');
        }

        return $config;
    }

    /**
     * Check if maintenance mode is enabled for this IP.
     * @return bool
     */
    private function isMaintenance() {
        $enabled = false;

        if (($allowedIPs = @file_get_contents(BP . DS . \Ava\Console\Command\Maintenance::MAINTENANCE_FILE)) !== false) {
            $allowedIPs = explode(',', $allowedIPs);
            $ip = $_SERVER['REMOTE_ADDR'];

            $enabled = !in_array($ip, $allowedIPs);
        };

        return $enabled;
    }

    /**
     * Get site support email address.
     * @return string
     */
    public function getSupportEmailAddress()
    {
        return $this->config['support_email_address'] ?? self::MAINTENANCE_DEFAULT_EMAIL;
    }

    /**
     * Get current static deployed version.
     * @return string
     */
    public function getDeployedVersion()
    {
        return '123';
    }
}
