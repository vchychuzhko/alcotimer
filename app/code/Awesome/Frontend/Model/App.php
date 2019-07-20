<?php

namespace Awesome\Frontend\Model;

class App
{
    public const CONFIG_FILE = 'app' . DS . 'config.php';
    private const TEMPLATES_DIR = BP . DS . 'app' . DS . 'templates';
    private const MAINTENANCE_PAGE_PATH = BP . DS . 'pub' . DS . 'pages' . DS . 'maintenance.html';

    /**
     * @var \Awesome\Logger\LogWriter
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
        $this->logWriter = new \Awesome\Logger\LogWriter();
        $this->config = $this->loadConfig();
    }

    /**
     * Run web app.
     */
    public function run()
    {
        ob_start();
        $template = self::MAINTENANCE_PAGE_PATH;

        if (!$this->isMaintenance() && $this->config) {
            $routes = $this->config['routes'];
            $systemRoutes = $this->config['system_routes'];
            $redirectStatus = (string) ($_SERVER['REDIRECT_STATUS'] ?? '');

            if ($redirectStatus === '403') {
                $templateName = $systemRoutes[$redirectStatus];
            } else {
                $uri = (string) strtok(trim($_SERVER['REQUEST_URI'], '/'), '?');
                $templateName = $routes[$uri] ?? $systemRoutes['404'];
            }

            $templateFile = self::TEMPLATES_DIR . DS . $templateName;

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

        if (($allowedIPs = @file_get_contents(BP . DS . \Awesome\Console\Command\Maintenance::MAINTENANCE_FILE)) !== false) {
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
     * Get current static deployed version.
     * @return string
     */
    public function getDeployedVersion()
    {
        $version = @file_get_contents(BP . DS . \Awesome\Console\Command\Cache::DEPLOYED_VERSION_FILE);

        return (string) $version;
    }

    /**
     *
     * @return string
     */
    public function getStaticPath()
    {
        return PUB_DIR . '/' . 'static/version' . $this->getDeployedVersion() . '/' . 'frontend';
    }

    /**
     *
     * @return string
     */
    public function getMediaPath()
    {
        return PUB_DIR . '/' . 'media';
    }
}
