<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\AppState;
use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Event\EventManager;
use Awesome\Framework\Model\Http\Request;
use Awesome\Framework\Model\Http\Response;
use Awesome\Framework\Model\Http\Response\HtmlResponse;
use Awesome\Framework\Model\Http\Response\JsonResponse;
use Awesome\Framework\Model\Http\Router;
use Awesome\Framework\Model\Logger;
use Awesome\Framework\Model\Maintenance;

class Http
{
    public const VERSION = '0.4.2';

    public const FRONTEND_VIEW = 'frontend';
    public const BACKEND_VIEW = 'adminhtml';
    public const BASE_VIEW = 'base';

    public const BACKEND_FRONT_NAME_CONFIG = 'backend/front_name';

    public const ROOT_ACTION_NAME = 'index_index_index';

    /**
     * @var AppState $appState
     */
    private $appState;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var EventManager $eventManager
     */
    private $eventManager;

    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * @var Maintenance $maintenance
     */
    private $maintenance;

    /**
     * @var Router $router
     */
    private $router;

    /**
     * @var Request $request
     */
    private $request;

    /**
     * Http app constructor.
     * @param AppState $appState
     * @param Config $config
     * @param EventManager $eventManager
     * @param Logger $logger
     * @param Maintenance $maintenance
     * @param Router $router
     */
    public function __construct(
        AppState $appState,
        Config $config,
        EventManager $eventManager,
        Logger $logger,
        Maintenance $maintenance,
        Router $router
    ) {
        $this->appState = $appState;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->maintenance = $maintenance;
        $this->router = $router;
    }

    /**
     * Run the web application.
     * @return void
     */
    public function run(): void
    {
        try {
            $request = $this->getRequest();
            $this->logger->logVisitor($request);

            if (!$this->isMaintenance()) {
                $this->eventManager->dispatch(
                    'http_frontend_action',
                    ['request' => $request, 'router' => $this->router],
                    $request->getView()
                );

                if ($action = $this->router->getAction()) {
                    $response = $action->execute($request);
                } elseif ($request->getRedirectStatusCode() === Request::FORBIDDEN_REDIRECT_CODE
                    && $this->appState->showForbidden()
                ) {
                    $response = new Response('', Response::FORBIDDEN_STATUS_CODE);
                } else {
                    $response = new Response('', Response::NOTFOUND_STATUS_CODE);
                }
            } else {
                // @TODO: get request 'accept' header and return error page according to needed type
                $response = new HtmlResponse(
                    $this->maintenance->getMaintenancePage(),
                    Response::SERVICE_UNAVAILABLE_STATUS_CODE
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e);

            if (isset($request) && $request->getAcceptType() === Request::JSON_ACCEPT_HEADER) {
                $response = new JsonResponse(
                    [
                        'status' => 'ERROR',
                        'message' => $this->appState->isDeveloperMode()
                            ? get_class_name($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString()
                            : 'Error details are hidden due to security reasons. Additional information can be found in the server logs.'
                    ],
                    Response::INTERNAL_ERROR_STATUS_CODE
                );
            } else {
                $response = new HtmlResponse(
                    $this->appState->isDeveloperMode()
                        ? '<pre>' . get_class_name($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>'
                        : $this->maintenance->getInternalErrorPage(),
                    Response::INTERNAL_ERROR_STATUS_CODE
                );
            }
        }

        $response->proceed();
    }

    /**
     * Check if maintenance mode is active for user IP address.
     * @return bool
     */
    private function isMaintenance(): bool
    {
        $ip = $this->getRequest()->getUserIp();

        return $this->maintenance->isMaintenance($ip);
    }

    /**
     * Parse and return http request.
     * @return Request
     */
    private function getRequest(): Request
    {
        if (!$this->request) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === 443
                ? Request::SCHEME_HTTPS
                : Request::SCHEME_HTTP;
            $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];
            $parameters = array_merge($_GET, $_POST);
            $cookies = $_COOKIE;
            $redirectStatus = isset($_SERVER['REDIRECT_STATUS']) ? (int) $_SERVER['REDIRECT_STATUS'] : null;
            list($acceptType) = isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] !== '*/*'
                ? explode(',', $_SERVER['HTTP_ACCEPT'])
                : [null];
            $fullActionName = $this->parseFullActionName($url);
            $userIp = $_SERVER['REMOTE_ADDR'];
            $view = $this->parseView($url);

            $this->request = new Request($url, $method, $parameters, $cookies, $redirectStatus, [
                'accept_type' => $acceptType,
                'full_action_name' => $fullActionName,
                'user_ip' => $userIp,
                'view' => $view,
            ]);
        }

        return $this->request;
    }

    /**
     * Parse requested URL into a valid action name.
     * Name should consists of three parts, missing ones will be added as 'index'.
     * @param string $url
     * @return string
     */
    private function parseFullActionName(string $url): string
    {
        $parts = explode('/', str_replace('_', '-', trim(parse_url($url, PHP_URL_PATH), '/')));

        return $parts[0]
            ? ($parts[0] . '_' . ($parts[1] ?? 'index') . '_' . ($parts[2] ?? 'index'))
            : self::ROOT_ACTION_NAME;
    }

    /**
     * Resolve page view by requested URL.
     * @param string $url
     * @return string
     */
    private function parseView(string $url): string
    {
        $view = self::FRONTEND_VIEW;

        if ($this->config->get('backend/enabled')) {
            $url = '/' . trim(parse_url($url, PHP_URL_PATH), '/') . '/';
            $backendFrontName = $this->config->get(self::BACKEND_FRONT_NAME_CONFIG);

            $view = preg_match('/^\/' . $backendFrontName . '\//', $url) ? self::BACKEND_VIEW : $view;
        }

        return $view;
    }
}
