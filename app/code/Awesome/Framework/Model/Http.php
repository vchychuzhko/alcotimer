<?php

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Event\Manager as EventManager;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Http\Router;
use Awesome\Framework\Model\Logger;
use Awesome\Framework\Model\Maintenance;

class Http
{
    public const VERSION = '0.3.1';

    public const FRONTEND_VIEW = 'frontend';
    public const BACKEND_VIEW = 'adminhtml';
    public const BASE_VIEW = 'base';

    public const SHOW_FORBIDDEN_CONFIG = 'web/show_forbidden';
    public const WEB_ROOT_CONFIG = 'web/web_root_is_pub';

    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var EventManager $eventManager
     */
    private $eventManager;

    /**
     * @var Request $request
     */
    private $request;

    /**
     * Http app constructor.
     */
    public function __construct()
    {
        $this->logger = new Logger();
        $this->maintenance = new Maintenance();
        $this->config = new Config();
        $this->eventManager = new EventManager();
    }

    /**
     * Run the web application.
     */
    public function run()
    {
        $request = $this->getRequest();
        $this->logger->logVisitor($request);

        if (!$this->isMaintenance()) {
            $redirectStatus = $request->getRedirectStatusCode();

            if ($redirectStatus === Response::FORBIDDEN_STATUS_CODE && $this->showForbiddenPage()) {
                $forbiddenRenderer = new \Awesome\Frontend\Model\Action\LayoutRenderer('forbidden_index_index', self::FRONTEND_VIEW);
                $response = $forbiddenRenderer->execute($request);
                $response->setStatusCode(Response::FORBIDDEN_STATUS_CODE);
                // @TODO: add native http error page displaying
            } else {
                $view = $this->resolveView();
                $router = new Router($view);
                $this->eventManager->dispatch(
                    'http_frontend_action',
                    ['request' => $request, 'router' => $router]
                );

                if ($action = $router->getAction()) {
                    $response = $action->execute($request);
                } else {
                    $notFoundRenderer = new \Awesome\Frontend\Model\Action\LayoutRenderer('notfound_index_index', self::FRONTEND_VIEW);
                    $response = $notFoundRenderer->execute($request);
                    $response->setStatusCode(Response::NOTFOUND_STATUS_CODE);
                    // @TODO: add native http error page displaying
                }
            }
        } else {
            $response = new Response(
                $this->maintenance->getMaintenancePage(),
                Response::SERVICE_UNAVAILABLE_STATUS_CODE,
                ['Content-Type' => 'text/html']
            );
        }

        $response->proceed();
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
            $cookies = [];
            $redirectStatus = $_SERVER['REDIRECT_STATUS'] ?? null;
            $userIPAddress = $_SERVER['REMOTE_ADDR'];

            if ($_GET) {
                $parameters = array_merge($parameters, $_GET);
            }

            if ($_POST) {
                $parameters = array_merge($parameters, $_POST);
            }

            if ($_COOKIE) {
                $cookies = $_COOKIE;
            }

            $this->request = new Request($url, $method, $parameters, $cookies, $redirectStatus, $userIPAddress);
        }

        return $this->request;
    }
}
