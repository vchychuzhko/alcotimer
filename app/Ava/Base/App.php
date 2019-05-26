<?php

namespace Ava\Base;

class App
{
    private const SUPPORT_EMAIL_ADDRESS = 'vlad.chichuzhko@gmail.com';
    private const TEMPLATES_DIR = 'pub/templates';

    /**
     * @var array $routes
     */
    private $routes = [
        '' => 'homepage.php',
        '403' => '403.php',
        '404' => '404.php',
        '405' => '405.php',
        'maintenance' => 'maintenance.php'
    ];

    /**
     * @var \Ava\Logger\LogWriter
     */
    private $logWriter;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->logWriter = new \Ava\Logger\LogWriter();
    }

    /**
     * Run web app.
     */
    public function run()
    {
        ob_start();
        $redirectStatus = (string) ($_SERVER['REDIRECT_STATUS'] ?? '');

        if (!$this->isMaintenance()) {
            if ($redirectStatus === '403' || $redirectStatus === '405') {
                $uri = $redirectStatus;
            } else {
                $uri = strtok(trim($_SERVER['REQUEST_URI'], '/'), '?');
            }

            $template = $this->routes[(string) $uri] ?? $this->routes['404'];
        } else {
            $template = $this->routes['maintenance'];
        }

        $template = BP . DS . self::TEMPLATES_DIR . DS . $template;

        try {
            if (@file_get_contents($template) === false) {
                $this->logWriter->write('No such file: ' . $template);
                throw new \Exception('No such file');
            }

            include $template;
        } catch (\Exception $e) {
            include BP . DS . self::TEMPLATES_DIR . DS . $this->routes['404'];
        }

        $response = ob_get_clean();
        echo $response;
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
        return self::SUPPORT_EMAIL_ADDRESS;
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
